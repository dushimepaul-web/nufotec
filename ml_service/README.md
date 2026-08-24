# NUFOTEC ML Service

Service Machine Learning indépendant pour le chatbot NUFOTEC Burundi.

## Fonctionnalités

- **Détection de langue** : Kirundi (rn), Français (fr), Anglais (en)
- **Détection d'intention** : 15 intentions NUFOTEC
- **API REST** : FastAPI avec endpoints /predict, /health, /train

## Installation

```bash
cd /var/www/nufotec.com/ml_service
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt
cp .env.example .env
# Éditer .env avec le token souhaité
```

## Entraînement

```bash
python training/train.py
```

## Démarrage

```bash
# Dev
uvicorn app.main:app --host 127.0.0.1 --port 8001 --reload

# Production
uvicorn app.main:app --host 127.0.0.1 --port 8001 --workers 1
```

## Tests

```bash
# Health check
curl http://127.0.0.1:8001/health

# Prédiction
curl -X POST http://127.0.0.1:8001/predict \
  -H "Content-Type: application/json" \
  -d '{"text": "Muraho, ndashaka kumenya igiciro"}'
```

## Intégration PHP

Le chatbot PHP appelle ce service via `MLService.php`.
Si le service est indisponible, le fallback PHP prend le relais automatiquement.
