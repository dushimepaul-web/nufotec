"""Connexion aux fournisseurs d'IA.

Deux protocoles suffisent :
- « compatible OpenAI » (/chat/completions, /models) : OpenAI, Groq, Mistral, Google Gemini (point d'accès
  OpenAI de Google), Ollama (local, gratuit) et tout service compatible ;
- Anthropic (/messages, /models).
Les clés sont déchiffrées uniquement au moment de l'appel et n'apparaissent jamais dans les erreurs."""

import json
from collections.abc import Iterator
from dataclasses import dataclass, field

import httpx

from app.core.config import get_settings
from app.core.crypto import decrypt
from app.models import AiProvider

DEFAULT_URLS = {
    "openai": "https://api.openai.com/v1",
    "groq": "https://api.groq.com/openai/v1",
    "anthropic": "https://api.anthropic.com/v1",
    "gemini": "https://generativelanguage.googleapis.com/v1beta/openai",
    "mistral": "https://api.mistral.ai/v1",
    "ollama": "http://localhost:11434/v1",
}
KEY_OPTIONAL = {"ollama", "openai_compatible"}
ANTHROPIC_VERSION = "2023-06-01"


class ProviderError(Exception):
    """Erreur d'un fournisseur (message sûr à afficher : aucune clé dedans)."""


@dataclass
class Usage:
    prompt_tokens: int = 0
    completion_tokens: int = 0


@dataclass
class ChatResult:
    text: str
    usage: Usage = field(default_factory=Usage)


def _client() -> httpx.Client:
    return httpx.Client(timeout=httpx.Timeout(get_settings().ai_timeout_seconds, connect=10))


def _headers(p: AiProvider) -> dict:
    key = decrypt(p.api_key_encrypted)
    if p.kind == "anthropic":
        headers = {"anthropic-version": ANTHROPIC_VERSION, "content-type": "application/json"}
        if key:
            headers["x-api-key"] = key
        return headers
    headers = {"content-type": "application/json"}
    if key:
        headers["authorization"] = f"Bearer {key}"
    return headers


def _raise_for(response: httpx.Response, provider: AiProvider) -> None:
    if response.status_code < 400:
        return
    try:
        body = response.json()
        detail = body.get("error", {}).get("message") if isinstance(body.get("error"), dict) else body.get("error")
        detail = detail or body.get("message") or ""
    except (ValueError, AttributeError):
        detail = response.text[:200]
    messages = {401: "clé d'API refusée", 403: "accès refusé", 404: "modèle ou adresse introuvable",
                429: "quota ou limite de débit atteint"}
    reason = messages.get(response.status_code, f"erreur {response.status_code}")
    raise ProviderError(f"{provider.name} : {reason}" + (f" ({str(detail)[:150]})" if detail else ""))


def _split_system(messages: list[dict]) -> tuple[str, list[dict]]:
    system = "\n\n".join(m["content"] for m in messages if m["role"] == "system")
    return system, [m for m in messages if m["role"] != "system"]


def list_models(p: AiProvider) -> list[str]:
    try:
        with _client() as http:
            response = http.get(f"{p.base_url.rstrip('/')}/models", headers=_headers(p))
    except httpx.HTTPError as exc:
        raise ProviderError(f"{p.name} : connexion impossible ({type(exc).__name__})") from None
    _raise_for(response, p)
    data = response.json()
    items = data.get("data", data.get("models", [])) if isinstance(data, dict) else data
    ids = [m.get("id") or m.get("name") for m in items if isinstance(m, dict)]
    return sorted({i.removeprefix("models/") for i in ids if i})


def chat(p: AiProvider, model: str, messages: list[dict], temperature: float, max_tokens: int) -> ChatResult:
    base = p.base_url.rstrip("/")
    try:
        with _client() as http:
            if p.kind == "anthropic":
                system, rest = _split_system(messages)
                response = http.post(f"{base}/messages", headers=_headers(p), json={
                    "model": model, "system": system, "messages": rest, "max_tokens": max_tokens,
                    "temperature": temperature})
                _raise_for(response, p)
                data = response.json()
                text = "".join(b.get("text", "") for b in data.get("content", []) if b.get("type") == "text")
                u = data.get("usage", {})
                return ChatResult(text, Usage(u.get("input_tokens", 0), u.get("output_tokens", 0)))
            response = http.post(f"{base}/chat/completions", headers=_headers(p), json={
                "model": model, "messages": messages, "temperature": temperature, "max_tokens": max_tokens})
            _raise_for(response, p)
            data = response.json()
            u = data.get("usage") or {}
            return ChatResult(data["choices"][0]["message"].get("content") or "",
                              Usage(u.get("prompt_tokens", 0), u.get("completion_tokens", 0)))
    except httpx.HTTPError as exc:
        raise ProviderError(f"{p.name} : connexion impossible ({type(exc).__name__})") from None
    except (KeyError, IndexError, ValueError):
        raise ProviderError(f"{p.name} : réponse inattendue") from None


def stream_chat(p: AiProvider, model: str, messages: list[dict], temperature: float, max_tokens: int,
                usage: Usage) -> Iterator[str]:
    """Renvoie le texte morceau par morceau ; `usage` est rempli à la fin (estimé si le service ne le donne pas)."""
    base = p.base_url.rstrip("/")
    produced: list[str] = []
    try:
        with _client() as http:
            if p.kind == "anthropic":
                system, rest = _split_system(messages)
                request = http.stream("POST", f"{base}/messages", headers=_headers(p), json={
                    "model": model, "system": system, "messages": rest, "max_tokens": max_tokens,
                    "temperature": temperature, "stream": True})
            else:
                request = http.stream("POST", f"{base}/chat/completions", headers=_headers(p), json={
                    "model": model, "messages": messages, "temperature": temperature, "max_tokens": max_tokens,
                    "stream": True})
            with request as response:
                if response.status_code >= 400:
                    response.read()
                    _raise_for(response, p)
                for line in response.iter_lines():
                    if not line.startswith("data:"):
                        continue
                    payload = line[5:].strip()
                    if payload == "[DONE]":
                        break
                    try:
                        event = json.loads(payload)
                    except ValueError:
                        continue
                    if p.kind == "anthropic":
                        kind = event.get("type")
                        if kind == "content_block_delta":
                            piece = event.get("delta", {}).get("text", "")
                        else:
                            if kind == "message_start":
                                usage.prompt_tokens = event.get("message", {}).get("usage", {}).get("input_tokens", 0)
                            elif kind == "message_delta":
                                usage.completion_tokens = event.get("usage", {}).get("output_tokens", 0)
                            continue
                    else:
                        choices = event.get("choices") or [{}]
                        piece = (choices[0].get("delta") or {}).get("content") or ""
                        if event.get("usage"):
                            usage.prompt_tokens = event["usage"].get("prompt_tokens", 0)
                            usage.completion_tokens = event["usage"].get("completion_tokens", 0)
                    if piece:
                        produced.append(piece)
                        yield piece
    except httpx.HTTPError as exc:
        raise ProviderError(f"{p.name} : connexion interrompue ({type(exc).__name__})") from None
    if not usage.completion_tokens:
        usage.completion_tokens = max(1, len("".join(produced)) // 4)
    if not usage.prompt_tokens:
        usage.prompt_tokens = sum(len(m["content"]) for m in messages) // 4
