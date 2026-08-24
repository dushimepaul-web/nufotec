"""
main.py - API FastAPI pour le service ML NUFOTEC
Endpoints: /predict, /health, /reload
"""

import os
import time
import logging
from contextlib import asynccontextmanager
from typing import Optional

from fastapi import FastAPI, HTTPException, Depends, Header
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field
from dotenv import load_dotenv

from .detector import LanguageDetector
from .classifier import IntentClassifier

# Configuration
load_dotenv()

ML_TOKEN = os.getenv('ML_SERVICE_TOKEN', 'nufotec-ml-secret-token-change-me')
LOG_LEVEL = os.getenv('ML_LOG_LEVEL', 'info')

# Logging
logging.basicConfig(
    level=getattr(logging, LOG_LEVEL.upper(), logging.INFO),
    format='%(asctime)s [%(levelname)s] %(name)s: %(message)s',
)
logger = logging.getLogger('nufotec-ml')

# Modèles ML (chargés au démarrage)
language_detector = LanguageDetector()
intent_classifier = IntentClassifier()


@asynccontextmanager
async def lifespan(app: FastAPI):
    """Charge les modèles au démarrage."""
    logger.info("Démarrage du service ML NUFOTEC")
    logger.info(f"Modèle langue chargé: {language_detector.model_loaded}")
    logger.info(f"Modèle intention chargé: {intent_classifier.model_loaded}")
    yield
    logger.info("Arrêt du service ML NUFOTEC")


app = FastAPI(
    title="NUFOTEC ML Service",
    description="Service de détection de langue et d'intention pour le chatbot NUFOTEC Burundi",
    version="1.0.0",
    lifespan=lifespan,
)

# CORS restreint (localhost uniquement)
app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://127.0.0.1", "http://localhost"],
    allow_methods=["POST", "GET"],
    allow_headers=["*"],
)


# === Modèles de requête/réponse ===

class PredictRequest(BaseModel):
    text: str = Field(..., min_length=1, max_length=2000, description="Texte à analyser")
    previous_language: Optional[str] = Field(None, description="Langue précédente de la conversation")

class PredictResponse(BaseModel):
    language: str
    language_confidence: float
    intent: str
    intent_confidence: float
    low_confidence: bool
    processing_time_ms: float
    language_details: Optional[dict] = None
    intent_details: Optional[dict] = None

class HealthResponse(BaseModel):
    status: str
    model_loaded: bool
    language_model_loaded: bool
    intent_model_loaded: bool
    uptime_seconds: float

class TrainRequest(BaseModel):
    texts: list[str]
    labels: Optional[list[str]] = None  # pour langue
    intents: Optional[list[str]] = None  # pour intention
    train_type: str = Field("language", pattern="^(language|intent|both)$")


# === Authentification ===

def verify_token(authorization: Optional[str] = Header(None)):
    """Vérifie le token d'authentification."""
    if authorization and authorization.replace("Bearer ", "") == ML_TOKEN:
        return True
    # En dev, accepter sans token
    if os.getenv('ML_DEV_MODE', 'false').lower() == 'true':
        return True
    raise HTTPException(status_code=401, detail="Token invalide")


# === Endpoints ===

@app.get("/health", response_model=HealthResponse)
async def health_check():
    """Vérifie l'état du service."""
    return HealthResponse(
        status="ok",
        model_loaded=language_detector.model_loaded or intent_classifier.model_loaded,
        language_model_loaded=language_detector.model_loaded,
        intent_model_loaded=intent_classifier.model_loaded,
        uptime_seconds=time.time() - app.state.start_time if hasattr(app.state, 'start_time') else 0,
    )


@app.post("/predict", response_model=PredictResponse)
async def predict(request: PredictRequest, _: bool = Depends(verify_token)):
    """
    Détecte la langue et l'intention du message.
    
    Si les modèles ML ne sont pas chargés, retourne des résultats
    avec low_confidence=true pour que PHP utilise son fallback.
    """
    start = time.time()

    try:
        # Détection de langue
        lang_result = language_detector.predict(request.text)
        
        # Si previous_language fourni et confiance faible, utiliser le contexte
        if (request.previous_language
                and lang_result.get('confidence', 0) < 0.60
                and lang_result.get('low_confidence', True)):
            lang_result['language'] = request.previous_language
            lang_result['method'] = 'context_override'

        # Détection d'intention
        intent_result = intent_classifier.predict(request.text)

        elapsed_ms = (time.time() - start) * 1000

        return PredictResponse(
            language=lang_result.get('language', 'rn'),
            language_confidence=lang_result.get('confidence', 0.30),
            intent=intent_result.get('intent', 'unknown'),
            intent_confidence=intent_result.get('confidence', 0.30),
            low_confidence=lang_result.get('low_confidence', True) or intent_result.get('low_confidence', True),
            processing_time_ms=round(elapsed_ms, 2),
            language_details=lang_result,
            intent_details=intent_result,
        )

    except Exception as e:
        logger.error(f"Erreur prédiction: {e}")
        elapsed_ms = (time.time() - start) * 1000
        return PredictResponse(
            language="rn",
            language_confidence=0.30,
            intent="unknown",
            intent_confidence=0.30,
            low_confidence=True,
            processing_time_ms=round(elapsed_ms, 2),
            language_details={"error": str(e)},
            intent_details={"error": str(e)},
        )


@app.post("/train")
async def train_model(request: TrainRequest, _: bool = Depends(verify_token)):
    """
    Entraîne ou met à jour les modèles.
    """
    results = {}

    if request.train_type in ('language', 'both') and request.labels:
        if len(request.texts) != len(request.labels):
            raise HTTPException(400, "texts et labels doivent avoir la même longueur")
        metrics = language_detector.train(request.texts, request.labels)
        results['language'] = metrics
        logger.info(f"Modèle langue réentraîné: {metrics.get('accuracy_mean', 0):.2%}")

    if request.train_type in ('intent', 'both') and request.intents:
        if len(request.texts) != len(request.intents):
            raise HTTPException(400, "texts et intents doivent avoir la même longueur")
        metrics = intent_classifier.train(request.texts, request.intents)
        results['intent'] = metrics
        logger.info(f"Modèle intention réentraîné: {metrics.get('accuracy_mean', 0):.2%}")

    return {"status": "trained", "results": results}


@app.post("/reload")
async def reload_models(_: bool = Depends(verify_token)):
    """Recharge les modèles depuis le disque."""
    global language_detector, intent_classifier
    language_detector = LanguageDetector()
    intent_classifier = IntentClassifier()
    return {
        "status": "reloaded",
        "language_model": language_detector.model_loaded,
        "intent_model": intent_classifier.model_loaded,
    }
