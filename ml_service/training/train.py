#!/usr/bin/env python3
"""
train.py - Script d'entraînement des modèles ML NUFOTEC
Charge le dataset, entraîne les modèles, sauvegarde, affiche les métriques.
"""

import sys
import os
import csv
import json
from pathlib import Path
from collections import Counter

# Ajouter le dossier parent au path
sys.path.insert(0, str(Path(__file__).parent.parent))

from app.preprocessing import normalize_text
from app.detector import LanguageDetector, LANGUAGE_MODEL_PATH
from app.classifier import IntentClassifier, INTENT_MODEL_PATH


def load_dataset(csv_path: str) -> tuple[list, list, list]:
    """
    Charge le dataset CSV.
    Format: text,language,intent
    """
    texts = []
    languages = []
    intents = []

    with open(csv_path, 'r', encoding='utf-8') as f:
        reader = csv.DictReader(f)
        for row in reader:
            text = row.get('text', '').strip()
            lang = row.get('language', '').strip()
            intent = row.get('intent', '').strip()

            if text and lang:
                texts.append(text)
                languages.append(lang)
                intents.append(intent if intent else 'unknown')

    return texts, languages, intents


def main():
    print("=" * 60)
    print("NUFOTEC ML - Entraînement des modèles")
    print("=" * 60)

    # Chemins
    data_dir = Path(__file__).parent.parent / 'data'
    dataset_path = data_dir / 'dataset.csv'

    if not dataset_path.exists():
        print(f"[ERREUR] Dataset non trouvé: {dataset_path}")
        sys.exit(1)

    # Charger les données
    print(f"\n📂 Chargement du dataset: {dataset_path}")
    texts, languages, intents = load_dataset(str(dataset_path))
    print(f"   {len(texts)} exemples chargés")

    # Statistiques
    lang_counts = Counter(languages)
    intent_counts = Counter(intents)
    print(f"\n📊 Distribution des langues:")
    for lang, count in sorted(lang_counts.items(), key=lambda x: -x[1]):
        pct = count / len(texts) * 100
        print(f"   {lang}: {count} ({pct:.1f}%)")

    print(f"\n📊 Distribution des intentions:")
    for intent, count in sorted(intent_counts.items(), key=lambda x: -x[1]):
        pct = count / len(texts) * 100
        print(f"   {intent}: {count} ({pct:.1f}%)")

    # Entraîner le modèle de langue
    print("\n" + "=" * 60)
    print("🎯 Entraînement du modèle de DÉTECTION DE LANGUE")
    print("=" * 60)

    lang_detector = LanguageDetector()
    lang_metrics = lang_detector.train(texts, languages, save=True)

    print(f"\n✅ Métriques:")
    print(f"   Accuracy: {lang_metrics['accuracy_mean']:.2%} (+/- {lang_metrics['accuracy_std']:.2%})")
    print(f"   Échantillons: {lang_metrics['n_samples']}")
    print(f"   Classes: {lang_metrics['classes']}")

    # Rapport par classe
    if 'classification_report' in lang_metrics:
        print(f"\n📋 Rapport de classification (langue):")
        for cls, metrics in lang_metrics['classification_report'].items():
            if isinstance(metrics, dict):
                print(f"   {cls}: precision={metrics.get('precision', 0):.2f}, recall={metrics.get('recall', 0):.2f}, f1={metrics.get('f1-score', 0):.2f}")

    print(f"\n💾 Modèle sauvegardé: {LANGUAGE_MODEL_PATH}")

    # Entraîner le modèle d'intention
    print("\n" + "=" * 60)
    print("🎯 Entraînement du modèle de DÉTECTION D'INTENTION")
    print("=" * 60)

    intent_clf = IntentClassifier()
    intent_metrics = intent_clf.train(texts, intents, save=True)

    print(f"\n✅ Métriques:")
    print(f"   Accuracy: {intent_metrics['accuracy_mean']:.2%} (+/- {intent_metrics['accuracy_std']:.2%})")
    print(f"   Échantillons: {intent_metrics['n_samples']}")
    print(f"   Intentions: {intent_metrics['n_intents']}")

    if 'classification_report' in intent_metrics:
        print(f"\n📋 Rapport de classification (intention):")
        for cls, metrics in intent_metrics['classification_report'].items():
            if isinstance(metrics, dict):
                print(f"   {cls}: precision={metrics.get('precision', 0):.2f}, recall={metrics.get('recall', 0):.2f}, f1={metrics.get('f1-score', 0):.2f}")

    print(f"\n💾 Modèle sauvegardé: {INTENT_MODEL_PATH}")

    # Tests rapides
    print("\n" + "=" * 60)
    print("🧪 TESTS RAPIDES")
    print("=" * 60)

    test_cases = [
        ("Muraho", "rn"),
        ("Amakuru?", "rn"),
        ("Ndashaka kumenya igiciro c'uyu muti", "rn"),
        ("Ndakeneye umuganga", "rn"),
        ("Ndwaye umutwe", "rn"),
        ("Bonjour", "fr"),
        ("Quel est le prix?", "fr"),
        ("Je veux consulter", "fr"),
        ("Hello", "en"),
        ("What is the price?", "en"),
        ("I want to see a doctor", "en"),
        ("Ndashaka consultation", "rn"),
    ]

    correct = 0
    total = len(test_cases)

    for text, expected in test_cases:
        result = lang_detector.predict(text)
        detected = result['language']
        conf = result['confidence']
        status = "✅" if detected == expected else "❌"
        if detected == expected:
            correct += 1
        print(f"   {status} '{text}' → {detected} (confiance: {conf:.2f}) [attendu: {expected}]")

    accuracy = correct / total * 100
    print(f"\n📊 Précision tests: {correct}/{total} ({accuracy:.1f}%)")

    print("\n" + "=" * 60)
    print("✅ Entraînement terminé!")
    print("=" * 60)


if __name__ == '__main__':
    main()
