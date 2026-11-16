"""Adresse IP réelle et user-agent du client."""

from dataclasses import dataclass

from fastapi import Request

from app.core.config import get_settings


@dataclass(frozen=True)
class ClientInfo:
    ip: str | None
    user_agent: str


def client_ip(request: Request) -> str | None:
    """X-Forwarded-For n'est cru que si la requête vient d'un proxy de confiance ;
    on remonte la chaîne depuis la droite jusqu'à la première IP qui n'est pas un proxy."""
    direct = request.client.host if request.client else None
    trusted = get_settings().trusted_proxy_list
    if direct not in trusted:
        return direct
    forwarded = request.headers.get("x-forwarded-for", "")
    hops = [h.strip() for h in forwarded.split(",") if h.strip()]
    for hop in reversed(hops):
        if hop not in trusted:
            return hop[:45]
    return direct


def get_client(request: Request) -> ClientInfo:
    return ClientInfo(ip=client_ip(request), user_agent=request.headers.get("user-agent", "")[:1000])
