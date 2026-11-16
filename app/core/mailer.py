"""Emails : file d'attente en base (table email_outbox) + envoi avec nouvelles tentatives.

1. `queue_email(db, …)` ajoute l'email dans la MÊME transaction que l'action (inscription, code…) :
   si l'action échoue, aucun email ne part ; si elle réussit, l'email ne peut plus être perdu.
2. `deliver_pending()` envoie les emails dus : juste après la requête (BackgroundTasks) et toutes les
   minutes (tâche de fond lancée au démarrage) pour les nouvelles tentatives.
En mode console (dev/tests), « envoyer » = garder l'email dans `outbox` et l'écrire dans le journal."""

import html as html_lib
import logging
import smtplib
import ssl
from dataclasses import dataclass
from datetime import timedelta
from email.message import EmailMessage
from email.utils import formataddr

from sqlalchemy import select
from sqlalchemy.orm import Session

from app.core.config import get_settings
from app.core.database import background_session
from app.core.helpers import now

log = logging.getLogger("nufotec.mail")

# Délai avant chaque nouvelle tentative ; après la dernière, l'email est abandonné (failed).
RETRY_DELAYS = [timedelta(minutes=1), timedelta(minutes=5), timedelta(minutes=30), timedelta(hours=2),
                timedelta(hours=6)]


@dataclass
class SentMail:
    to: str
    subject: str
    text: str
    html: str


outbox: list[SentMail] = []


def queue_email(db: Session, to: str, subject: str, text: str, html: str) -> None:
    """Ajoute l'email à la file. Ne fait pas le commit : il part avec la transaction de l'appelant."""
    from app.models import EmailOutbox

    db.add(EmailOutbox(to_email=to, subject=subject[:255], text_body=text, html_body=html,
                       status="pending", attempts=0, next_attempt_at=now()))


def _smtp_settings(db: Session) -> tuple[str, int, str | None, str | None]:
    s = get_settings()
    if s.smtp_host:
        return s.smtp_host, s.smtp_port or 465, s.smtp_user, s.smtp_password
    from app.models import Parametre

    rows = dict(db.execute(
        select(Parametre.cle, Parametre.valeur)
        .where(Parametre.cle.in_(["smtp_host", "smtp_port", "smtp_user", "smtp_pass"]))
    ).all())
    if not rows.get("smtp_host"):
        raise RuntimeError("SMTP non configuré (ni .env ni table parametres)")
    return rows["smtp_host"], int(rows.get("smtp_port") or 465), rows.get("smtp_user"), rows.get("smtp_pass")


def _transport(db: Session, to: str, subject: str, text: str, html: str) -> None:
    s = get_settings()
    if s.mail_mode == "console":
        outbox.append(SentMail(to, subject, text, html))
        log.info("Email (console) à %s : %s\n%s", to, subject, text)
        return
    host, port, user, password = _smtp_settings(db)
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


def deliver_pending(limit: int = 20) -> int:
    """Envoie les emails dus. Sûr avec plusieurs serveurs : SKIP LOCKED évite les doubles envois."""
    from app.models import EmailOutbox

    sent = 0
    db = background_session()
    try:
        rows = list(db.scalars(
            select(EmailOutbox)
            .where(EmailOutbox.status == "pending", EmailOutbox.next_attempt_at <= now())
            .order_by(EmailOutbox.id).limit(limit)
            .with_for_update(skip_locked=True)
        ))
        for mail in rows:
            try:
                _transport(db, mail.to_email, mail.subject, mail.text_body or "", mail.html_body or "")
                mail.status, mail.sent_at, mail.last_error = "sent", now(), None
                mail.text_body = mail.html_body = None  # le contenu peut contenir un code : on l'efface
                sent += 1
                log.info("Email envoyé à %s : %s", mail.to_email, mail.subject)
            except Exception as exc:
                mail.attempts += 1
                mail.last_error = f"{type(exc).__name__}: {exc}"[:500]
                if mail.attempts > len(RETRY_DELAYS):
                    mail.status = "failed"
                    mail.text_body = mail.html_body = None
                    log.error("Email abandonné après %s tentatives : %s (%s)", mail.attempts, mail.to_email,
                              mail.last_error)
                else:
                    mail.next_attempt_at = now() + RETRY_DELAYS[mail.attempts - 1]
                    log.warning("Échec d'envoi à %s (tentative %s) : %s", mail.to_email, mail.attempts,
                                mail.last_error)
        db.commit()
    except Exception:
        db.rollback()
        log.exception("Erreur pendant l'envoi de la file d'emails")
    finally:
        db.close()
    return sent


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


def invitation_email(prenom: str, email: str, code: str) -> tuple[str, str, str]:
    subject = "Votre compte Nufotec a été créé"
    text = (f"Bonjour {prenom},\n\nUn compte Nufotec a été créé pour vous ({email}).\n"
            f"Pour choisir votre mot de passe, utilisez « Mot de passe oublié » avec ce code : {code}\n"
            "Il est valable 72 heures.")
    prenom_html, email_html = html_lib.escape(prenom), html_lib.escape(email)
    html = _layout("Bienvenue sur Nufotec", f"""<p>Bonjour {prenom_html},</p>
<p>Un compte Nufotec a été créé pour vous (<b>{email_html}</b>).</p>
<p>Pour choisir votre mot de passe, ouvrez l'application, touchez « Mot de passe oublié » et saisissez ce code :</p>
<p style="font-size:32px;letter-spacing:8px;font-weight:bold">{code}</p>
<p>Il est valable 72 heures.</p>""")
    return subject, text, html


def simple_email(prenom: str, title: str, paragraphs: list[str], link: tuple[str, str] | None = None
                 ) -> tuple[str, str]:
    """Email générique (texte, html) : « Bonjour X », paragraphes, bouton facultatif (libellé, url)."""
    text = f"Bonjour {prenom},\n\n" + "\n\n".join(paragraphs)
    body = f"<p>Bonjour {html_lib.escape(prenom)},</p>" + "".join(f"<p>{html_lib.escape(p)}</p>" for p in paragraphs)
    if link:
        label, url = link
        text += f"\n\n{label} : {url}"
        body += (f'<p><a href="{html_lib.escape(url)}" style="background:#0b6e4f;color:#fff;padding:12px 20px;'
                 f'border-radius:6px;text-decoration:none">{html_lib.escape(label)}</a></p>')
    return text, _layout(html_lib.escape(title), body)
