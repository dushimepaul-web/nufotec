"""Tables du chatbot : existantes (chatbot_kb, conversations, messages, transferts) et RAG (sql/008)."""

from datetime import datetime

from sqlalchemy import BigInteger, Boolean, DateTime, Enum, ForeignKey, Integer, LargeBinary, SmallInteger, String, Text
from sqlalchemy.orm import Mapped, mapped_column

from app.core.database import Base

PROVIDER_KINDS = ("openai", "groq", "anthropic", "gemini", "mistral", "ollama", "openai_compatible")


class ChatbotKb(Base):
    __tablename__ = "chatbot_kb"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    title: Mapped[str] = mapped_column(String(255))
    content: Mapped[str] = mapped_column(Text)
    category: Mapped[str | None] = mapped_column(String(120))
    embedding: Mapped[str | None] = mapped_column(Text)  # colonne du PHP, non utilisée par l'API
    active: Mapped[bool] = mapped_column(Boolean, default=True)
    created_at: Mapped[datetime] = mapped_column(DateTime)
    updated_at: Mapped[datetime] = mapped_column(DateTime)


class ChatbotConversation(Base):
    __tablename__ = "chatbot_conversations"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    session_id: Mapped[str] = mapped_column(String(64))
    token_hash: Mapped[str | None] = mapped_column(String(64), unique=True)
    user_name: Mapped[str | None] = mapped_column(String(150))
    user_email: Mapped[str | None] = mapped_column(String(150))
    user_id: Mapped[int | None] = mapped_column(Integer)
    status: Mapped[str] = mapped_column(Enum("bot", "pending_human", "human", "closed", native_enum=False), default="bot")
    lang: Mapped[str | None] = mapped_column(String(10))
    agent_id: Mapped[int | None] = mapped_column(Integer)
    ip_address: Mapped[str | None] = mapped_column(String(45))
    created_at: Mapped[datetime] = mapped_column(DateTime)
    updated_at: Mapped[datetime] = mapped_column(DateTime)


class ChatbotMessage(Base):
    __tablename__ = "chatbot_messages"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    conversation_id: Mapped[int] = mapped_column(ForeignKey("chatbot_conversations.id"))
    sender: Mapped[str] = mapped_column(Enum("user", "bot", "agent", "system", native_enum=False))
    message: Mapped[str] = mapped_column(Text)
    meta: Mapped[str | None] = mapped_column(Text)
    created_at: Mapped[datetime] = mapped_column(DateTime)


class ChatbotHandoff(Base):
    __tablename__ = "chatbot_handoff_requests"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    conversation_id: Mapped[int] = mapped_column(ForeignKey("chatbot_conversations.id"))
    reason: Mapped[str | None] = mapped_column(String(255))
    status: Mapped[str] = mapped_column(Enum("open", "assigned", "closed", native_enum=False), default="open")
    agent_id: Mapped[int | None] = mapped_column(Integer)
    created_at: Mapped[datetime] = mapped_column(DateTime)
    updated_at: Mapped[datetime] = mapped_column(DateTime)


class AiProvider(Base):
    __tablename__ = "ai_providers"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    name: Mapped[str] = mapped_column(String(100), unique=True)
    kind: Mapped[str] = mapped_column(Enum(*PROVIDER_KINDS, native_enum=False))
    base_url: Mapped[str] = mapped_column(String(255))
    api_key_encrypted: Mapped[str | None] = mapped_column(Text)
    api_key_hint: Mapped[str | None] = mapped_column(String(20))
    is_active: Mapped[bool] = mapped_column(Boolean, default=True)
    last_test_at: Mapped[datetime | None] = mapped_column(DateTime)
    last_test_ok: Mapped[bool | None] = mapped_column(Boolean)
    last_test_message: Mapped[str | None] = mapped_column(String(255))
    created_at: Mapped[datetime] = mapped_column(DateTime)
    updated_at: Mapped[datetime] = mapped_column(DateTime)


class ChatbotSetting(Base):
    __tablename__ = "chatbot_settings"

    cle: Mapped[str] = mapped_column(String(60), primary_key=True)
    valeur: Mapped[str | None] = mapped_column(Text)
    updated_at: Mapped[datetime] = mapped_column(DateTime)


class RagSource(Base):
    __tablename__ = "rag_sources"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    kind: Mapped[str] = mapped_column(Enum("kb", "document", "table", native_enum=False))
    name: Mapped[str] = mapped_column(String(255))
    status: Mapped[str] = mapped_column(Enum("pending", "indexing", "ready", "error", native_enum=False),
                                        default="pending")
    error: Mapped[str | None] = mapped_column(String(500))
    chunks_count: Mapped[int] = mapped_column(Integer, default=0)
    is_active: Mapped[bool] = mapped_column(Boolean, default=True)
    last_indexed_at: Mapped[datetime | None] = mapped_column(DateTime)
    created_at: Mapped[datetime] = mapped_column(DateTime)


class RagTableRule(Base):
    __tablename__ = "rag_table_rules"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    source_id: Mapped[int] = mapped_column(ForeignKey("rag_sources.id"))
    table_name: Mapped[str] = mapped_column(String(64), unique=True)
    columns_json: Mapped[str] = mapped_column(Text)
    filters_json: Mapped[str | None] = mapped_column(Text)
    template: Mapped[str | None] = mapped_column(Text)
    title_column: Mapped[str | None] = mapped_column(String(64))
    schedule: Mapped[str] = mapped_column(Enum("manual", "nightly", native_enum=False), default="nightly")
    max_rows: Mapped[int] = mapped_column(Integer, default=5000)


class RagDocument(Base):
    __tablename__ = "rag_documents"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    source_id: Mapped[int] = mapped_column(ForeignKey("rag_sources.id"))
    stored_name: Mapped[str] = mapped_column(String(100))
    original_name: Mapped[str] = mapped_column(String(255))
    mime: Mapped[str] = mapped_column(String(120))
    size_bytes: Mapped[int] = mapped_column(Integer)
    text_chars: Mapped[int] = mapped_column(Integer, default=0)
    created_at: Mapped[datetime] = mapped_column(DateTime)


class RagChunk(Base):
    __tablename__ = "rag_chunks"

    id: Mapped[int] = mapped_column(BigInteger, primary_key=True)
    source_id: Mapped[int] = mapped_column(ForeignKey("rag_sources.id"))
    ref_key: Mapped[str] = mapped_column(String(120))
    title: Mapped[str | None] = mapped_column(String(255))
    content: Mapped[str] = mapped_column(Text)
    content_hash: Mapped[str] = mapped_column(String(64))
    embedding: Mapped[bytes | None] = mapped_column(LargeBinary)
    created_at: Mapped[datetime] = mapped_column(DateTime)


class AiUsage(Base):
    __tablename__ = "ai_usage"

    id: Mapped[int] = mapped_column(BigInteger, primary_key=True)
    provider_id: Mapped[int | None] = mapped_column(Integer)
    model: Mapped[str] = mapped_column(String(120))
    purpose: Mapped[str] = mapped_column(String(30), default="chat")
    prompt_tokens: Mapped[int] = mapped_column(Integer, default=0)
    completion_tokens: Mapped[int] = mapped_column(Integer, default=0)
    ok: Mapped[bool] = mapped_column(Boolean, default=True)
    conversation_id: Mapped[int | None] = mapped_column(Integer)
    created_at: Mapped[datetime] = mapped_column(DateTime)


class ChatbotFeedback(Base):
    __tablename__ = "chatbot_feedback"

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    message_id: Mapped[int] = mapped_column(Integer, unique=True)
    rating: Mapped[int] = mapped_column(SmallInteger)
    comment: Mapped[str | None] = mapped_column(String(500))
    created_at: Mapped[datetime] = mapped_column(DateTime)
