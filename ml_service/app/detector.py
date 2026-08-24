"""
detector.py - Détection de langue ML avec TF-IDF + character n-grams
Modèle léger optimisé pour Kirundi/Français/Anglais
"""

import os
import joblib
import numpy as np
from pathlib import Path
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.linear_model import LogisticRegression
from sklearn.svm import LinearSVC
from sklearn.calibration import CalibratedClassifierCV
from sklearn.pipeline import Pipeline
from sklearn.model_selection import cross_val_score
from sklearn.metrics import classification_report

from .preprocessing import normalize_text

MODEL_DIR = Path(__file__).parent / 'models'
LANGUAGE_MODEL_PATH = MODEL_DIR / 'language_model.joblib'
VECTORIZER_PATH = MODEL_DIR / 'language_vectorizer.joblib'

# Langues supportées
LANGUAGES = {'rn': 'Kirundi', 'fr': 'Français', 'en': 'Anglais'}


class LanguageDetector:
    """
    Détection de langue par ML.
    Utilise TF-IDF sur character n-grams + Logistic Regression.
    """

    def __init__(self):
        self.pipeline = None
        self.model_loaded = False
        self._load_model()

    def _load_model(self):
        """Charge le modèle pré-entraîné si disponible."""
        try:
            if LANGUAGE_MODEL_PATH.exists():
                self.pipeline = joblib.load(LANGUAGE_MODEL_PATH)
                self.model_loaded = True
        except Exception as e:
            print(f"[ML] Impossible de charger le modèle langue: {e}")
            self.model_loaded = False

    def train(self, texts: list[str], labels: list[str], save: bool = True) -> dict:
        """
        Entraîne le modèle de détection de langue.
        Retourne les métriques.
        """
        # Normaliser les textes
        clean_texts = [normalize_text(t) for t in texts]

        # Pipeline: character n-grams TF-IDF + Classifier calibré
        self.pipeline = Pipeline([
            ('tfidf', TfidfVectorizer(
                analyzer='char_wb',
                ngram_range=(2, 5),
                max_features=50000,
                sublinear_tf=True,
                min_df=1,
                max_df=0.95,
            )),
            ('classifier', CalibratedClassifierCV(
                LinearSVC(
                    C=1.0,
                    max_iter=5000,
                    class_weight='balanced',
                ),
                cv=3,
                method='sigmoid',
            )),
        ])

        # Entraîner
        self.pipeline.fit(clean_texts, labels)
        self.model_loaded = True

        # Évaluation par cross-validation
        scores = cross_val_score(self.pipeline, clean_texts, labels, cv=min(5, len(set(labels))), scoring='accuracy')

        metrics = {
            'accuracy_mean': float(np.mean(scores)),
            'accuracy_std': float(np.std(scores)),
            'n_samples': len(texts),
            'n_classes': len(set(labels)),
            'classes': list(set(labels)),
        }

        # Rapport de classification
        predictions = self.pipeline.predict(clean_texts)
        report = classification_report(labels, predictions, output_dict=True)
        metrics['classification_report'] = report

        # Sauvegarder
        if save:
            MODEL_DIR.mkdir(parents=True, exist_ok=True)
            joblib.dump(self.pipeline, LANGUAGE_MODEL_PATH)

        return metrics

    def predict(self, text: str) -> dict:
        """
        Prédit la langue du texte.
        Retourne: {'language': 'rn', 'confidence': 0.94}
        """
        if not self.model_loaded or self.pipeline is None:
            return {
                'language': 'rn',
                'confidence': 0.30,
                'method': 'no_model',
                'low_confidence': True,
            }

        normalized = normalize_text(text)
        if not normalized or len(normalized) < 2:
            return {
                'language': 'rn',
                'confidence': 0.20,
                'method': 'empty_text',
                'low_confidence': True,
            }

        try:
            # Prédire la classe
            prediction = self.pipeline.predict([normalized])[0]

            # Obtenir les probabilités
            probas = self.pipeline.predict_proba([normalized])[0]
            classes = self.pipeline.classes_
            confidence = float(max(probas))

            # Construire le dictionnaire de confiance par langue
            confidence_by_lang = {cls: float(p) for cls, p in zip(classes, probas)}

            low_confidence = confidence < 0.60

            return {
                'language': prediction,
                'confidence': round(confidence, 3),
                'confidence_by_lang': confidence_by_lang,
                'method': 'ml_model',
                'low_confidence': low_confidence,
            }

        except Exception as e:
            return {
                'language': 'rn',
                'confidence': 0.30,
                'method': 'prediction_error',
                'error': str(e),
                'low_confidence': True,
            }
