"""Règles de mot de passe communes (inscription, changement, réinitialisation)."""

import re

# Mots de passe les plus utilisés (listes publiques), en minuscules.
COMMON_PASSWORDS = {
    "12345678", "123456789", "1234567890", "password", "password1", "password123", "motdepasse",
    "azerty123", "azertyuiop", "qwerty123", "qwertyuiop", "11111111", "00000000", "abcd1234",
    "iloveyou", "admin123", "administrator", "welcome1", "letmein1", "sunshine1", "princess1",
    "football1", "baseball1", "monkey123", "dragon123", "passw0rd", "p@ssw0rd", "p@ssword",
    "bonjour1", "soleil123", "burundi1", "burundi123", "bujumbura", "nufotec1", "nufotec123",
    "1q2w3e4r", "1qaz2wsx", "zaq12wsx", "qazwsx123", "123qweasd", "a1b2c3d4", "aa123456",
}


def password_problems(password: str, email: str | None = None, names: tuple[str, ...] = ()) -> list[str]:
    problems = []
    if len(password) < 8:
        problems.append("au moins 8 caractères")
    if len(password.encode("utf-8")) > 72:
        problems.append("au plus 72 octets")
    if not re.search(r"[^\W\d_]", password):
        problems.append("au moins une lettre")
    if not re.search(r"\d", password):
        problems.append("au moins un chiffre")
    lowered = password.lower()
    if lowered in COMMON_PASSWORDS:
        problems.append("ne doit pas être un mot de passe courant")
    if email:
        local = email.split("@")[0].lower()
        if len(local) >= 4 and local in lowered:
            problems.append("ne doit pas contenir votre email")
    for name in names:
        if name and len(name) >= 4 and name.lower() in lowered:
            problems.append("ne doit pas contenir votre nom")
            break
    return problems
