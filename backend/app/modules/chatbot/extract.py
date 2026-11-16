"""Extraction du texte des documents envoyés (PDF, Word, Excel, CSV, TXT, Markdown) et découpage en passages."""

import csv
import io
import re

from app.core.responses import ApiError

MAX_BYTES = 20 * 1024 * 1024
TYPES = {
    "pdf": "application/pdf",
    "docx": "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
    "xlsx": "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    "csv": "text/csv",
    "txt": "text/plain",
    "md": "text/markdown",
}


def detect(filename: str, raw: bytes) -> str:
    ext = filename.rsplit(".", 1)[-1].lower() if "." in filename else ""
    if ext not in TYPES:
        raise ApiError(422, "INVALID_FILE", "Formats acceptés : PDF, DOCX, XLSX, CSV, TXT, MD")
    if not raw:
        raise ApiError(422, "INVALID_FILE", "Fichier vide")
    if len(raw) > MAX_BYTES:
        raise ApiError(422, "INVALID_FILE", "Fichier trop lourd (maximum 20 Mo)")
    # Le contenu doit correspondre à l'extension
    if ext == "pdf" and not raw.startswith(b"%PDF-"):
        raise ApiError(422, "INVALID_FILE", "Ce fichier n'est pas un vrai PDF")
    if ext in ("docx", "xlsx") and not raw.startswith(b"PK"):
        raise ApiError(422, "INVALID_FILE", "Ce fichier n'est pas un vrai document Office")
    return ext


def _decode(raw: bytes) -> str:
    for encoding in ("utf-8-sig", "cp1252", "latin-1"):
        try:
            return raw.decode(encoding)
        except UnicodeDecodeError:
            continue
    return raw.decode("utf-8", "replace")


def extract_text(ext: str, raw: bytes) -> list[tuple[str, str]]:
    """Renvoie [(repère, texte)] : une entrée par page (PDF), feuille (Excel) ou document."""
    try:
        if ext == "pdf":
            from pypdf import PdfReader

            reader = PdfReader(io.BytesIO(raw))
            return [(f"page {i + 1}", page.extract_text() or "") for i, page in enumerate(reader.pages)]
        if ext == "docx":
            from docx import Document

            doc = Document(io.BytesIO(raw))
            parts = [p.text for p in doc.paragraphs]
            for table in doc.tables:
                for row in table.rows:
                    parts.append(" | ".join(cell.text.strip() for cell in row.cells))
            return [("document", "\n".join(parts))]
        if ext == "xlsx":
            from openpyxl import load_workbook

            wb = load_workbook(io.BytesIO(raw), read_only=True, data_only=True)
            out = []
            for ws in wb.worksheets:
                rows = list(ws.iter_rows(values_only=True))
                if not rows:
                    continue
                header = [str(c) if c is not None else "" for c in rows[0]]
                lines = []
                for row in rows[1:]:
                    cells = [f"{h}: {v}" for h, v in zip(header, row, strict=False) if v not in (None, "")]
                    if cells:
                        lines.append(" ; ".join(cells))
                out.append((f"feuille {ws.title}", "\n".join(lines)))
            return out
        if ext == "csv":
            text = _decode(raw)
            dialect = csv.Sniffer().sniff(text[:4096], delimiters=",;\t") if text.strip() else csv.excel
            rows = list(csv.reader(io.StringIO(text), dialect))
            if not rows:
                return []
            header = rows[0]
            lines = [" ; ".join(f"{h}: {v}" for h, v in zip(header, row, strict=False) if v) for row in rows[1:]]
            return [("tableau", "\n".join(line for line in lines if line))]
        return [("document", _decode(raw))]
    except ApiError:
        raise
    except Exception as exc:
        raise ApiError(422, "UNREADABLE_FILE", f"Impossible de lire ce fichier ({type(exc).__name__})") from None


def chunk(text: str, size: int = 900, overlap: int = 150) -> list[str]:
    """Découpe en passages d'environ `size` caractères, en respectant les paragraphes et les phrases."""
    text = re.sub(r"[ \t]+", " ", text).strip()
    text = re.sub(r"\n{3,}", "\n\n", text)
    if len(text) <= size:
        return [text] if text else []
    pieces = re.split(r"(?<=[.!?])\s+|\n\n", text)
    chunks, current = [], ""
    for piece in pieces:
        piece = piece.strip()
        if not piece:
            continue
        while len(piece) > size:  # phrase géante : coupe brute
            chunks.append(piece[:size])
            piece = piece[size - overlap:]
        if len(current) + len(piece) + 1 > size and current:
            chunks.append(current)
            current = current[-overlap:].split(" ", 1)[-1] + " " + piece
        else:
            current = f"{current} {piece}".strip()
    if current:
        chunks.append(current)
    return chunks
