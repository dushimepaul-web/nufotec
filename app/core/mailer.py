"""Envoi d'emails. En mode console (dev/tests), les emails sont gardés dans `outbox` et journalisés."""

import html as html_lib
import logging
import smtplib
import ssl
from dataclasses import dataclass
from email.message import EmailMessage
from email.utils import formataddr

from sqlalchemy import select

from app.core.config import get_settings
from app.core.database import SessionLocal

log = logging.getLogger("nufotec.mail")


@dataclass
class SentMail:
    to: str
    subject: str
    text: str
    html: str


outbox: list[SentMail] = []


def _smtp_settings() -> tuple[str, int, str | None, str | None]:
    s = get_settings()
    if s.smtp_host:
        return s.smtp_host, s.smtp_port or 465, s.smtp_user, s.smtp_password
    from app.models import Configuration  # import local : évite un import circulaire

    with SessionLocal() as db:
        rows = dict(db.execute(
            select(Configuration.cle, Configuration.valeur)
            .where(Configuration.cle.in_(["smtp_host", "smtp_port", "smtp_user", "smtp_pass"]))
        ).all())
    if not rows.get("smtp_host"):
        raise RuntimeError("SMTP non configuré (ni .env ni table configurations)")
    return rows["smtp_host"], int(rows.get("smtp_port") or 465), rows.get("smtp_user"), rows.get("smtp_pass")


def send_email(to: str, subject: str, text: str, html: str) -> None:
    """À appeler via BackgroundTasks : une panne SMTP ne doit pas faire échouer la requête."""
    s = get_settings()
    if s.mail_mode == "console":
        outbox.append(SentMail(to, subject, text, html))
        log.info("Email (console) à %s : %s\n%s", to, subject, text)
        return
    try:
        host, port, user, password = _smtp_settings()
        msg = EmailMessage()
        msg["From"] = formataddr((s.mail_from_name, s.mail_from))
        msg["To"] = to
        msg["Subject"] = subject
        msg.set_content(text)
        msg.add_alternative(html, subtype="html")
        context = ssl.create_default_context()
        if port == 465:
            with smtplib.SMTP_SSL(host, port, context=context, timeout=20) as smtp:
                if user:
                    smtp.login(user, password or "")
                smtp.send_message(msg)
        else:
            with smtplib.SMTP(host, port, timeout=20) as smtp:
                smtp.starttls(context=context)
                if user:
                    smtp.login(user, password or "")
                smtp.send_message(msg)
        log.info("Email envoyé à %s : %s", to, subject)
    except Exception:
        log.exception("Échec d'envoi de l'email à %s : %s", to, subject)


def _layout(title: str, body_html: str) -> str:
    return f"""<div style="font-family:Arial,sans-serif;max-width:520px;margin:auto;color:#1f2933">
<h2 style="color:#0b6e4f">{title}</h2>{body_html}
<p style="color:#7b8794;font-size:12px;margin-top:32px">Nufotec — cet email a été envoyé automatiquement,
merci de ne pas y répondre.</p></div>"""


def reset_code_email(prenom: str, code: str, minutes: int) -> tuple[str, str, str]:
    prenom = html_lib.escape(prenom)
    subject = "Votre code de réinitialisation Nufotec"
    text = (f"Bonjour {prenom},\n\nVotre code de réinitialisation est : {code}\n"
            f"Il est valable {minutes} minutes.\n\nSi vous n'êtes pas à l'origine de cette demande, ignorez cet email.")
    html = _layout("Réinitialisation du mot de passe", f"""<p>Bonjour {prenom},</p>
<p>Votre code de réinitialisation :</p>
<p style="font-size:32px;letter-spacing:8px;font-weight:bold">{code}</p>
<p>Il est valable {minutes} minutes. Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.</p>""")
    return subject, text, html


def verification_email(prenom: str, link: str) -> tuple[str, str, str]:
    prenom = html_lib.escape(prenom)
    subject = "Confirmez votre adresse email Nufotec"
    text = f"Bonjour {prenom},\n\nConfirmez votre adresse email en ouvrant ce lien :\n{link}\n"
    html = _layout("Confirmez votre adresse email", f"""<p>Bonjour {prenom},</p>
<p>Merci de votre inscription. Confirmez votre adresse email :</p>
<p><a href="{link}" style="background:#0b6e4f;color:#fff;padding:12px 20px;border-radius:6px;
text-decoration:none">Confirmer mon email</a></p>""")
    return subject, text, html
