"""
classifier.py - Détection d'intention ML
Classe les messages en intentions NUFOTEC
"""

import os
import joblib
import numpy as np
from pathlib import Path
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.linear_model import LogisticRegression
from sklearn.pipeline import Pipeline
from sklearn.model_selection import cross_val_score

from .preprocessing import normalize_text

MODEL_DIR = Path(__file__).parent / 'models'
INTENT_MODEL_PATH = MODEL_DIR / 'intent_model.joblib'

# Intentions NUFOTEC
INTENTS = {
    'greeting': 'Salutation',
    'general_information': 'Information générale',
    'product_information': 'Information produit',
    'prix_produit': 'Prix d\'un produit',
    'commande': 'Commande',
    'disponibilite_produit': 'Disponibilité produit',
    'consultation': 'Consultation médicale',
    'rendez_vous': 'Rendez-vous',
    'symptome': 'Description symptôme',
    'medecin': 'Question sur médecin',
    'paiement': 'Moyen de paiement',
    'livraison': 'Livraison',
    'contact': 'Contact',
    'horaires': 'Horaires',
    'support': 'Support technique',
    'unknown': 'Non classifié',
}


class IntentClassifier:
    """
    Classification d'intentions par ML.
    Utilise TF-IDF sur word n-grams + Logistic Regression.
    """

    def __init__(self):
        self.pipeline = None
        self.model_loaded = False
        self._load_model()

    def _load_model(self):
        """Charge le modèle pré-entraîné si disponible."""
        try:
            if INTENT_MODEL_PATH.exists():
                self.pipeline = joblib.load(INTENT_MODEL_PATH)
                self.model_loaded = True
        except Exception as e:
            print(f"[ML] Impossible de charger le modèle intention: {e}")
            self.model_loaded = False

    def train(self, texts: list[str], intents: list[str], save: bool = True) -> dict:
        """
        Entraîne le modèle de classification d'intentions.
        """
        clean_texts = [normalize_text(t) for t in texts]

        self.pipeline = Pipeline([
            ('tfidf', TfidfVectorizer(
                analyzer='word',
                ngram_range=(1, 3),
                max_features=30000,
                sublinear_tf=True,
                min_df=1,
                max_df=0.95,
            )),
            ('classifier', LogisticRegression(
                C=1.0,
                max_iter=1000,
                class_weight='balanced',
            )),
        ])

        self.pipeline.fit(clean_texts, intents)
        self.model_loaded = True

        # Évaluation
        scores = cross_val_score(self.pipeline, clean_texts, intents, cv=min(5, len(set(intents))), scoring='accuracy')

        metrics = {
            'accuracy_mean': float(np.mean(scores)),
            'accuracy_std': float(np.std(scores)),
            'n_samples': len(texts),
            'n_intents': len(set(intents)),
            'intents': list(set(intents)),
        }

        predictions = self.pipeline.predict(clean_texts)
        from sklearn.metrics import classification_report
        report = classification_report(intents, predictions, output_dict=True, zero_division=0)
        metrics['classification_report'] = report

        if save:
            MODEL_DIR.mkdir(parents=True, exist_ok=True)
            joblib.dump(self.pipeline, INTENT_MODEL_PATH)

        return metrics

    def predict(self, text: str) -> dict:
        """
        Prédit l'intention du message.
        """
        if not self.model_loaded or self.pipeline is None:
            return {
                'intent': 'unknown',
                'confidence': 0.30,
                'method': 'no_model',
                'low_confidence': True,
            }

        normalized = normalize_text(text)
        if not normalized or len(normalized) < 2:
            return {
                'intent': 'unknown',
                'confidence': 0.20,
                'method': 'empty_text',
                'low_confidence': True,
            }

        try:
            prediction = self.pipeline.predict([normalized])[0]
            probas = self.pipeline.predict_proba([normalized])[0]
            confidence = float(max(probas))

            # Top 3 intentions
            classes = self.pipeline.classes_
            top_indices = np.argsort(probas)[::-1][:3]
            top_intents = [
                {'intent': classes[i], 'confidence': round(float(probas[i]), 3)}
                for i in top_indices
            ]

            low_confidence = confidence < 0.50

            return {
                'intent': prediction,
                'confidence': round(confidence, 3),
                'top_intents': top_intents,
                'method': 'ml_model',
                'low_confidence': low_confidence,
            }

        except Exception as e:
            return {
                'intent': 'unknown',
                'confidence': 0.30,
                'method': 'prediction_error',
                'error': str(e),
                'low_confidence': True,
            }
