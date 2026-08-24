<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Language_detector - Détection hybride de langue pour Kirundi/Français/Anglais
 * 
 * Architecture: Normalisation + Lexique + Expressions + Morphologie + Scoring + Confiance
 * 
 * @author  NUFOTEC Chatbot Team
 * @version 2.0
 */
class Language_detector
{
    /**
     * Lexique Kirundi par catégorie avec poids
     * Poids: expression_très_caractéristique=8, mot_caractéristique=5, moyen=3, faible=1
     */
    protected $kirundi_lexicon = array(
        // ===== EXPRESSIONS CARACTÉRISTIQUES (poids 8) =====
        'expressions' => array(
            // Salutations
            'muraho' => 8, 'amakuru' => 8, 'bite neza' => 8, 'mwiriwe' => 8,
            'mwaramutse' => 8, 'murakoze cane' => 8, 'amakuru yawe' => 8,
            'amakuru mwe' => 8, 'ni amakuru' => 8,
            
            // Santé - expressions
            'ndarwaye' => 8, 'ndicumva ndarwaye' => 8, 'ndumva ndwaye' => 8,
            'ndafise ikibazo' => 8, 'ndafise indwara' => 8, 'ndafise uburwayi' => 8,
            'ndashaka umuganga' => 8, 'ndashaka kubonana na muganga' => 8,
            'ndashaka consultation' => 8, 'ndashaka kuvugana na docteur' => 8,
            'ndashaka gufata rendezvous' => 8, 'ndashaka rendezvous' => 8,
            'umuganga wanjye' => 8, 'kwa muganga' => 8,
            
            // Prix / Questions
            'igiciro cay\'o' => 8, 'igiciro cayi' => 8, 'igiciro cawe' => 8,
            'ugurwa angahe' => 8, 'igurwa angahe' => 8, 'ibiciro vyose' => 8,
            'ndashaka kumenya igiciro' => 8, 'mbwira igiciro' => 8,
            'ni angahe' => 8, 'ari angahe' => 8, 'ni ikihe igiciro' => 8,
            
            // Commande
            'ndashaka gutenga' => 8, 'ndashaka kugura' => 8, 'ndashaka gufata' => 8,
            'ndashaka gutumiza' => 8, 'nshaka gutumiza' => 8,
            
            // Intention
            'ndashaka kumenya' => 8, 'nshaka kumenya' => 8,
            'ndashaka kubona' => 8, 'nshaka kubona' => 8,
            'ndashaka kuvugana' => 8, 'nshaka kuvugana' => 8,
            'ndashaka gutegura' => 8, 'nshaka gutegura' => 8,
            
            // Symptômes
            'ndumva ndababara' => 8, 'ndumva ndarwaye' => 8,
            'ndafise umuriro' => 8, 'ndafise inkorora' => 8,
            'ndwaye umutwe' => 8, 'ndwaye inda' => 8,
            'umubiri wanjye' => 8, 'ububabare bwose' => 8,
            
            // Contact
            'nkorera hehe' => 8, 'uri hehe' => 8, 'hamwe hehe' => 8,
            'nshobora kukuganira' => 8, 'nshobora kugufasha' => 8,
            
            // Temps
            'ryari ryose' => 8, 'kugeza ryari' => 8,
            'mbere y' => 8, 'nyuma y' => 8,
            
            // Général
            'ndabizi neza' => 8, 'sinzi neza' => 8,
            'ndategeje neza' => 8, 'ndategeje' => 8,
            'ndagukwiza' => 8, 'ndagukunda' => 8,
        ),
        
        // ===== MOTS CARACTÉRISTIQUES (poids 5) =====
        'strong_words' => array(
            // Salutations
            'muraho', 'amakuru', 'bite', 'mwiriwe', 'mwaramutse',
            
            // Pronoms / Verbes forts
            'ndashaka', 'nshaka', 'ndafise', 'mfise', 'dufise', 'twifise',
            'ndumva', 'ndarwaye', 'ndwaye', 'sinzi', 'sinshobora', 'nshobora',
            'ndipfuza', 'ndabaza', 'ndabizi', 'ndategeje',
            
            // Santé
            'umuganga', 'muganga', 'uburwayi', 'indwara', 'umuti', 'imiti',
            'ubuvuzi', 'umutwe', 'inda', 'umuriro', 'inkorora', 'umubiri',
            'ububabare', 'indwara', 'urwayi',
            
            // Prix
            'igiciro', 'ibiciro', 'angahe', 'gutenga', 'gutumiza',
            'kugura', 'kwishura', 'ubwishyu',
            
            // Commande
            'gutega', 'gutegura', 'gufata',
            
            // Intention
            'kumenya', 'kubona', 'kuvugana', 'gufasha',
            
            // Pays
            'burundi', 'bujumbura', 'gitega', 'ngozi', 'rumonge',
            
            // Temps
            'ryari', 'hehe', 'ubu', 'none', 'hasho',
            
            // Négation
            'sinzi', 'sinshobora', 'ntashoboka', 'ntabwo',
        ),
        
        // ===== MOTS MOYENS (poids 3) =====
        'medium_words' => array(
            // Pronoms
            'ndi', 'uri', 'ari', 'twari', 'muri',
            
            // Prépositions courantes
            'mu', 'ku', 'kuri', 'ku', 'mu', 'iyo',
            
            // Conjonctions
            'kandi', 'cyangwa', 'kubera', 'kuko',
            
            // Déterminants
            'uyu', 'uywo', 'iri', 'uru', 'ubu',
            
            // Noms courants
            'abantu', 'umuntu', 'ibintu', 'ikintu',
            'amahano', 'amakosa', 'ibyo', 'ibyo',
            
            // Verbes courants
            'kuba', 'kujya', 'kugenda', 'kuba',
            'kurya', 'kunywa', 'gukora',
            
            // Adverbes
            'cane', 'biri', 'hejuru', 'hasi',
            'mbere', 'nyuma', 'hafi', 'kure',
        ),
        
        // ===== MOTS FAIBLES (poids 1) =====
        'weak_words' => array(
            'ni', 'na', 'yo', 'vyo', 'byo',
            'uwo', 'icyo', 'aryo', 'ubwo',
            'naho', 'buri', 'mbere',
            'vyose', 'byose', 'amwe',
            'rimwe', 'kabiri', 'gatatu',
        ),
    );
    
    /**
     * Lexique Français par catégorie avec poids
     */
    protected $french_lexicon = array(
        'expressions' => array(
            'bonjour' => 8, 'bonsoir' => 8, 'comment allez' => 8,
            'je voudrais' => 8, 'je veux' => 8, 'est-ce que' => 8,
            'combien ça coûte' => 8, 'quel est le prix' => 8,
            'je suis malade' => 8, 'j\'ai mal' => 8, 'j\'ai besoin' => 8,
            'je voudrais prendre' => 8, 'comment faire' => 8,
            'où se trouve' => 8, 'quelles sont les' => 8,
            'à quelle heure' => 8, 'depuis combien' => 8,
            'merci beaucoup' => 8, 'je vous remercie' => 8,
        ),
        'strong_words' => array(
            'bonjour', 'bonsoir', 'salut', 'merci', 'please',
            'medecin', 'medicament', 'consultation', 'rendezvous',
            'maladie', 'douleur', 'symptome', 'traitement',
            'pharmacie', 'ordonnance', 'examen', 'analyse',
            'prix', 'combien', 'cout', 'tarif',
            'payer', 'paiement', 'lumicash', 'western',
            'whatsapp', 'telephone', 'email', 'adresse',
            'produit', 'acheter', 'commander', 'livraison',
            'horaires', 'ouverture', 'fermeture',
            'docteur', 'infirmier', 'urse',
        ),
        'medium_words' => array(
            'je', 'tu', 'il', 'elle', 'nous', 'vous', 'ils', 'elles',
            'etre', 'avoir', 'faire', 'aller', 'dire', 'pouvoir',
            'vouloir', 'devoir', 'prendre', 'donner', 'voir',
            'poids', 'taille', 'age', 'nom', 'prenom',
            'pays', 'ville', 'maison', 'hopital', 'clinique',
            'santé', 'corps', 'tête', 'ventre', 'dos',
            'fievre', 'toux', 'maux', 'douleurs',
        ),
        'weak_words' => array(
            'le', 'la', 'les', 'un', 'une', 'des',
            'du', 'de', 'au', 'aux', 'et', 'ou',
            'que', 'qui', 'quoi', 'dont', 'où',
            'mon', 'ma', 'mes', 'ton', 'ta', 'tes',
            'son', 'sa', 'ses', 'notre', 'votre', 'leur',
        ),
    );
    
    /**
     * Lexique Anglais par catégorie avec poids
     */
    protected $english_lexicon = array(
        'expressions' => array(
            'hello' => 8, 'good morning' => 8, 'good evening' => 8,
            'how are you' => 8, 'i would like' => 8, 'i want' => 8,
            'how much' => 8, 'what is the price' => 8,
            'i am sick' => 8, 'i have pain' => 8, 'i need' => 8,
            'i want to book' => 8, 'how to' => 8,
            'where is' => 8, 'what are' => 8,
            'at what time' => 8, 'how long' => 8,
            'thank you' => 8, 'thanks a lot' => 8,
        ),
        'strong_words' => array(
            'hello', 'hi', 'morning', 'evening',
            'doctor', 'medicine', 'consultation', 'appointment',
            'disease', 'pain', 'symptom', 'treatment',
            'pharmacy', 'prescription', 'examination', 'analysis',
            'price', 'cost', 'pay', 'payment',
            'whatsapp', 'phone', 'email', 'address',
            'product', 'buy', 'order', 'delivery',
            'hours', 'opening', 'closing',
            'patient', 'health', 'medical', 'hospital',
        ),
        'medium_words' => array(
            'i', 'you', 'he', 'she', 'we', 'they',
            'am', 'is', 'are', 'was', 'were',
            'have', 'has', 'had', 'do', 'does',
            'can', 'could', 'will', 'would', 'should',
            'want', 'need', 'like', 'know', 'see',
            'weight', 'height', 'age', 'name',
            'country', 'city', 'house', 'clinic',
            'body', 'head', 'stomach', 'back',
            'fever', 'cough', 'pain', 'hurt',
        ),
        'weak_words' => array(
            'the', 'a', 'an', 'and', 'or', 'but',
            'in', 'on', 'at', 'to', 'for', 'of',
            'my', 'your', 'his', 'her', 'its',
            'our', 'their', 'this', 'that',
            'what', 'which', 'who', 'where', 'when',
        ),
    );
    
    /**
     * Prefixes morphologiques Kirundi
     */
    protected $kirundi_morphology = array(
        'prefixes_verbaux' => array(
            'nd' => 5,    // nd- (je)
            'n' => 3,     // n- (je, variante)
            'ku' => 2,    // ku- (infinitif)
            'gu' => 2,    // gu- (infinitif variante)
            'ka' => 2,    // ka- (impératif)
            'tu' => 2,    // tu- (nous)
            'mu' => 2,    // mu- (vous)
            'ba' => 2,    // ba- (ils)
            'i' => 1,     // i- (préfixe nominal)
            'u' => 1,     // u- (préfixe nominal)
            'a' => 1,     // a- (préfixe nominal)
        ),
        'suffixes_noms' => array(
            'ngo' => 4,   // -ngo (nom)
            'ko' => 3,    // -ko (nom)
            'no' => 3,    // -no (nom)
            'ri' => 2,    // -ri (adjectif)
            're' => 2,    // -re (adjectif)
            'ni' => 1,    // -ni (nom)
        ),
        'prefixes_noms' => array(
            'um' => 5,    // um- (nom singulier)
            'ubu' => 5,   // ubu- (nom abstrait)
            'ibi' => 5,   // ibi- (nom pluriel)
            'ama' => 4,   // ama- (nom pluriel)
            'iku' => 3,   // iku- (nom)
            'ubu' => 3,   // ubu- (nom)
            'aga' => 2,   // aga- (nom)
        ),
    );
    
    /**
     * Caractères WhatsApp / chat à normaliser
     */
    protected $whatsapp_replacements = array(
        'ô' => 'o', 'é' => 'e', 'è' => 'e', 'ê' => 'e',
        'à' => 'a', 'â' => 'a', 'î' => 'i', 'ï' => 'i',
        'û' => 'u', 'ù' => 'u', 'ü' => 'u',
        'ç' => 'c', 'œ' => 'oe', 'æ' => 'ae',
        'ñ' => 'n',
    );
    
    /**
     * Mots très courts et ambigus (ne doivent PAS décider de la langue)
     */
    protected $ambiguous_words = array(
        'ni', 'na', 'mu', 'ku', 'ndi', 'uri', 'yo', 'vyo', 'kandi',
        'le', 'la', 'un', 'une', 'et', 'ou', 'a', 'i', 'o',
        'the', 'a', 'an', 'is', 'it', 'in', 'on',
    );
    
    /**
     * Seuil de confiance minimum
     */
    const MIN_CONFIDENCE = 0.30;
    
    /**
     * Seuil de confiance "fiable"
     */
    const GOOD_CONFIDENCE = 0.65;
    
    /**
     * Seuil de confiance "très fiable"
     */
    const HIGH_CONFIDENCE = 0.85;
    
    /**
     * Langue par défaut (Kirundi - public burundais)
     */
    const DEFAULT_LANGUAGE = 'rn';
    
    /**
     * CI instance
     */
    protected $CI;
    
    public function __construct()
    {
        $this->CI =& get_instance();
    }
    
    /**
     * Normalise le texte avant détection
     */
    protected function _normalize($text)
    {
        // Minuscules
        $text = mb_strtolower(trim($text));
        
        // Normaliser Unicode
        $text = preg_replace('/[\x{0300}-\x{036f}]/u', '', $text);
        
        // Normaliser apostrophes
        $text = str_replace(
            array("'", "\xE2\x80\x99", "`", "\xC2\xB4"),
            "'",
            $text
        );
        
        // Normaliser espaces multiples
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Supprimer caractères inutiles (garder lettres, chiffres, espaces, apostrophes, tirets)
        $text = preg_replace('/[^\p{L}\p{N}\s\'-]/u', '', $text);
        
        // Appliquer remplacements WhatsApp
        $text = strtr($text, $this->whatsapp_replacements);
        
        return trim($text);
    }
    
    /**
     * Détecte la langue via scoring hybride
     */
    public function detect($text)
    {
        $normalized = $this->_normalize($text);
        
        if (empty($normalized) || mb_strlen($normalized) < 2) {
            return array('language' => self::DEFAULT_LANGUAGE, 'confidence' => 0.20, 'method' => 'short_text');
        }
        
        $words = explode(' ', $normalized);
        $word_count = count($words);
        
        // 1. Détection par expressions (poids le plus élevé)
        $expression_scores = $this->_score_expressions($normalized);
        
        // 2. Détection par mots individuels
        $word_scores = $this->_score_words($words);
        
        // 3. Détection morphologique (uniquement pour Kirundi)
        $morpho_score = $this->_score_morphology($normalized, $words);
        
        // 4. Détection de caractéristiques Unicode (accents, caractères spécifiques)
        $unicode_score = $this->_score_unicode($normalized);
        
        // 5. Combiner les scores
        $scores = array(
            'rn' => $expression_scores['rn'] + $word_scores['rn'] + $morpho_score + $unicode_score['rn'],
            'fr' => $expression_scores['fr'] + $word_scores['fr'] + $unicode_score['fr'],
            'en' => $expression_scores['en'] + $word_scores['en'] + $unicode_score['en'],
        );
        
        // 6. Pénaliser les mots ambigus
        $scores = $this->_penalize_ambiguous($scores, $words, $word_count);
        
        // 7. Bonus Kirundi pour phrases très courtes ou ambiguës
        $scores = $this->_apply_default_bonus($scores, $word_count, $normalized);
        
        // 8. Calculer la confiance
        $result = $this->_calculate_confidence($scores, $word_count);
        
        return $result;
    }
    
    /**
     * Scoring par expressions (multi-mots)
     */
    protected function _score_expressions($text)
    {
        $scores = array('rn' => 0, 'fr' => 0, 'en' => 0);
        
        // Expressions Kirundi
        foreach ($this->kirundi_lexicon['expressions'] as $expr => $weight) {
            if (mb_strpos($text, $expr) !== false) {
                $scores['rn'] += $weight;
            }
        }
        
        // Expressions Françaises
        foreach ($this->french_lexicon['expressions'] as $expr => $weight) {
            if (mb_strpos($text, $expr) !== false) {
                $scores['fr'] += $weight;
            }
        }
        
        // Expressions Anglaises
        foreach ($this->english_lexicon['expressions'] as $expr => $weight) {
            if (mb_strpos($text, $expr) !== false) {
                $scores['en'] += $weight;
            }
        }
        
        return $scores;
    }
    
    /**
     * Scoring par mots individuels
     */
    protected function _score_words($words)
    {
        $scores = array('rn' => 0, 'fr' => 0, 'en' => 0);
        
        foreach ($words as $word) {
            $word = trim($word);
            if (empty($word) || mb_strlen($word) < 2) continue;
            
            // Mots Kirundi forts
            if (in_array($word, $this->kirundi_lexicon['strong_words'])) {
                $scores['rn'] += 5;
            }
            // Mots Kirundi moyens
            elseif (in_array($word, $this->kirundi_lexicon['medium_words'])) {
                $scores['rn'] += 3;
            }
            // Mots Kirundi faibles
            elseif (in_array($word, $this->kirundi_lexicon['weak_words'])) {
                $scores['rn'] += 1;
            }
            
            // Mots Français forts
            if (in_array($word, $this->french_lexicon['strong_words'])) {
                $scores['fr'] += 5;
            }
            elseif (in_array($word, $this->french_lexicon['medium_words'])) {
                $scores['fr'] += 3;
            }
            elseif (in_array($word, $this->french_lexicon['weak_words'])) {
                $scores['fr'] += 1;
            }
            
            // Mots Anglais forts
            if (in_array($word, $this->english_lexicon['strong_words'])) {
                $scores['en'] += 5;
            }
            elseif (in_array($word, $this->english_lexicon['medium_words'])) {
                $scores['en'] += 3;
            }
            elseif (in_array($word, $this->english_lexicon['weak_words'])) {
                $scores['en'] += 1;
            }
        }
        
        return $scores;
    }
    
    /**
     * Scoring morphologique (caractéristiques Kirundi)
     */
    protected function _score_morphology($text, $words)
    {
        $score = 0;
        
        // Vérifier les préfixes verbaux Kirundi
        foreach ($this->kirundi_morphology['prefixes_verbaux'] as $prefix => $weight) {
            foreach ($words as $word) {
                if (mb_strlen($word) > 3 && mb_substr($word, 0, mb_strlen($prefix)) === $prefix) {
                    $score += $weight;
                }
            }
        }
        
        // Vérifier les préfixes nominaux Kirundi
        foreach ($this->kirundi_morphology['prefixes_noms'] as $prefix => $weight) {
            foreach ($words as $word) {
                if (mb_strlen($word) > 3 && mb_substr($word, 0, mb_strlen($prefix)) === $prefix) {
                    $score += $weight;
                }
            }
        }
        
        // Vérifier les suffixes Kirundi
        foreach ($this->kirundi_morphology['suffixes_noms'] as $suffix => $weight) {
            foreach ($words as $word) {
                if (mb_strlen($word) > 3 && mb_substr($word, -mb_strlen($suffix)) === $suffix) {
                    $score += $weight;
                }
            }
        }
        
        return $score;
    }
    
    /**
     * Scoring Unicode (caractères spécifiques)
     */
    protected function _score_unicode($text)
    {
        $scores = array('rn' => 0, 'fr' => 0, 'en' => 0);
        
        // Caractères accentués français (é, è, ê, ë, à, â, ù, û, ç, etc.)
        if (preg_match('/[éèêëàâùûüîïôœç]/u', $text)) {
            $scores['fr'] += 3;
        }
        
        // Apostrophes typiques du Kirundi (n', d', b', etc.)
        if (preg_match('/[nkbd]\'/u', $text)) {
            $scores['rn'] += 2;
        }
        
        // Patterns typiques Kirundi (consonnes suivies de voyelles)
        if (preg_match('/\b[bcdfghjklmnpqrstvwxyz][aeiou]/u', $text)) {
            $scores['rn'] += 1;
        }
        
        return $scores;
    }
    
    /**
     * Pénaliser les mots ambigus
     */
    protected function _penalize_ambiguous($scores, $words, $word_count)
    {
        // Si le texte est très court, ne pas trop compter les mots faibles
        if ($word_count <= 3) {
            foreach ($words as $word) {
                if (in_array($word, $this->ambiguous_words)) {
                    // Réduire le poids des mots ambigus dans les scores
                    foreach ($scores as $lang => &$score) {
                        $score = max(0, $score - 0.5);
                    }
                }
            }
        }
        
        return $scores;
    }
    
    /**
     * Appliquer le bonus par défaut (Kirundi)
     */
    protected function _apply_default_bonus($scores, $word_count, $text)
    {
        // Si aucun score significatif, favoriser Kirundi
        $max_score = max($scores);
        
        if ($max_score < 3) {
            $scores['rn'] += 2; // Léger bonus Kirundi
        }
        
        // Si le texte contient des indices Kirundi forts
        $kirundi_indicators = array('nd', 'um', 'ubu', 'ibi', 'ama');
        foreach ($kirundi_indicators as $indicator) {
            if (mb_strpos($text, $indicator) !== false) {
                $scores['rn'] += 1;
            }
        }
        
        return $scores;
    }
    
    /**
     * Calculer la confiance et retourner le résultat
     */
    protected function _calculate_confidence($scores, $word_count)
    {
        $total = $scores['rn'] + $scores['fr'] + $scores['en'];
        
        if ($total == 0) {
            return array(
                'language' => self::DEFAULT_LANGUAGE,
                'confidence' => 0.20,
                'method' => 'no_signal',
                'scores' => $scores
            );
        }
        
        // Trouver la langue avec le score le plus élevé
        $max_lang = 'rn';
        $max_score = $scores['rn'];
        
        foreach (array('fr', 'en') as $lang) {
            if ($scores[$lang] > $max_score) {
                $max_score = $scores[$lang];
                $max_lang = $lang;
            }
        }
        
        // Calculer la confiance
        $confidence = $max_score / $total;
        
        // Ajuster la confiance selon le nombre de mots
        if ($word_count <= 2) {
            $confidence *= 0.7; // Réduire confiance pour très courts textes
        } elseif ($word_count <= 4) {
            $confidence *= 0.85;
        }
        
        // Si un seul mot, réduire davantage
        if ($word_count == 1) {
            $confidence *= 0.5;
        }
        
        // S'assurer que la confiance est dans les bornes
        $confidence = max(self::MIN_CONFIDENCE, min(1.0, $confidence));
        
        // Déterminer la méthode utilisée
        $method = 'hybrid_scoring';
        if ($max_score < 3) {
            $method = 'low_signal';
        } elseif ($max_score > 15) {
            $method = 'strong_signal';
        }
        
        return array(
            'language' => $max_lang,
            'confidence' => round($confidence, 2),
            'method' => $method,
            'scores' => $scores,
            'word_count' => $word_count
        );
    }
    
    /**
     * Détecte la langue avec gestion de la mémoire conversationnelle
     */
    public function detect_with_context($text, $previous_language = null)
    {
        $result = $this->detect($text);
        
        // Si la confiance est faible et qu'on a une langue précédente, l'utiliser
        if ($result['confidence'] < self::GOOD_CONFIDENCE && $previous_language !== null) {
            $result['language'] = $previous_language;
            $result['method'] = 'context_override';
            $result['confidence'] = max($result['confidence'], 0.50);
        }
        
        return $result;
    }
    
    /**
     * Formate le résultat pour le chatbot
     */
    public function get_language_code($text, $previous_language = null)
    {
        $result = $this->detect_with_context($text, $previous_language);
        return $result['language'];
    }
    
    /**
     * Retourne le nom complet de la langue
     */
    public function get_language_name($code)
    {
        $names = array(
            'rn' => 'Kirundi',
            'fr' => 'Français',
            'en' => 'Anglais'
        );
        return isset($names[$code]) ? $names[$code] : 'Français';
    }
    
    /**
     * Retourne les messages traduits pour les tests
     */
    public function get_test_messages()
    {
        return array(
            // ===== KIRUNDI (30 phrases) =====
            array('text' => 'Muraho', 'expected' => 'rn', 'min_confidence' => 0.60),
            array('text' => 'Amakuru?', 'expected' => 'rn', 'min_confidence' => 0.60),
            array('text' => 'Ndashaka kumenya igiciro c\'uyu muti', 'expected' => 'rn', 'min_confidence' => 0.75),
            array('text' => 'Ndafise ikibazo', 'expected' => 'rn', 'min_confidence' => 0.70),
            array('text' => 'Ndumva ndwaye', 'expected' => 'rn', 'min_confidence' => 0.70),
            array('text' => 'Ndarwaye', 'expected' => 'rn', 'min_confidence' => 0.70),
            array('text' => 'Ndashaka umuganga', 'expected' => 'rn', 'min_confidence' => 0.75),
            array('text' => 'Ndashaka kubonana na muganga', 'expected' => 'rn', 'min_confidence' => 0.80),
            array('text' => 'Umuti ugurwa angahe?', 'expected' => 'rn', 'min_confidence' => 0.75),
            array('text' => 'Mbwira igiciro c\'uyu muti', 'expected' => 'rn', 'min_confidence' => 0.75),
            array('text' => 'Ndashaka gufata rendez-vous', 'expected' => 'rn', 'min_confidence' => 0.75),
            array('text' => 'Ndashaka consultation', 'expected' => 'rn', 'min_confidence' => 0.70),
            array('text' => 'Ndumva ndababara mu nda', 'expected' => 'rn', 'min_confidence' => 0.75),
            array('text' => 'Ndwaye umutwe', 'expected' => 'rn', 'min_confidence' => 0.70),
            array('text' => 'Ndafise umuriro', 'expected' => 'rn', 'min_confidence' => 0.70),
            array('text' => 'Ndashaka kumenya uko nobonana na muganga', 'expected' => 'rn', 'min_confidence' => 0.75),
            array('text' => 'Ni amakuru', 'expected' => 'rn', 'min_confidence' => 0.60),
            array('text' => 'Murakoze cane', 'expected' => 'rn', 'min_confidence' => 0.70),
            array('text' => 'Nshaka kugura umuti', 'expected' => 'rn', 'min_confidence' => 0.75),
            array('text' => 'Igiciro cawe ni ikihe?', 'expected' => 'rn', 'min_confidence' => 0.75),
            array('text' => 'Ndashaka gutumiza umuti', 'expected' => 'rn', 'min_confidence' => 0.75),
            array('text' => 'Uri hehe?', 'expected' => 'rn', 'min_confidence' => 0.65),
            array('text' => 'Nshobora kukuganira', 'expected' => 'rn', 'min_confidence' => 0.70),
            array('text' => 'Ndabizi neza', 'expected' => 'rn', 'min_confidence' => 0.65),
            array('text' => 'Ndategeje', 'expected' => 'rn', 'min_confidence' => 0.60),
            array('text' => 'Yego, ndashaka', 'expected' => 'rn', 'min_confidence' => 0.65),
            array('text' => 'Oya, sinshobora', 'expected' => 'rn', 'min_confidence' => 0.65),
            array('text' => 'Ndafise indwara y\'inda', 'expected' => 'rn', 'min_confidence' => 0.75),
            array('text' => 'Uburwayi bwanjye bukwiye', 'expected' => 'rn', 'min_confidence' => 0.70),
            array('text' => 'Ndashaka gufata rendez-vous na muganga', 'expected' => 'rn', 'min_confidence' => 0.80),
            
            // ===== FRANÇAIS (10 phrases) =====
            array('text' => 'Bonjour', 'expected' => 'fr', 'min_confidence' => 0.60),
            array('text' => 'Comment allez-vous?', 'expected' => 'fr', 'min_confidence' => 0.70),
            array('text' => 'Je voudrais connaître le prix', 'expected' => 'fr', 'min_confidence' => 0.75),
            array('text' => 'Je suis malade', 'expected' => 'fr', 'min_confidence' => 0.70),
            array('text' => 'J\'ai mal à la tête', 'expected' => 'fr', 'min_confidence' => 0.75),
            array('text' => 'Quel est le prix de ce médicament?', 'expected' => 'fr', 'min_confidence' => 0.80),
            array('text' => 'Comment prendre rendez-vous?', 'expected' => 'fr', 'min_confidence' => 0.75),
            array('text' => 'Où se trouve votre clinique?', 'expected' => 'fr', 'min_confidence' => 0.75),
            array('text' => 'Merci beaucoup', 'expected' => 'fr', 'min_confidence' => 0.70),
            array('text' => 'Je veux acheter un produit', 'expected' => 'fr', 'min_confidence' => 0.75),
            
            // ===== ANGLAIS (10 phrases) =====
            array('text' => 'Hello', 'expected' => 'en', 'min_confidence' => 0.55),
            array('text' => 'How are you?', 'expected' => 'en', 'min_confidence' => 0.70),
            array('text' => 'I would like to know the price', 'expected' => 'en', 'min_confidence' => 0.75),
            array('text' => 'I am sick', 'expected' => 'en', 'min_confidence' => 0.65),
            array('text' => 'I have a headache', 'expected' => 'en', 'min_confidence' => 0.70),
            array('text' => 'What is the price of this medicine?', 'expected' => 'en', 'min_confidence' => 0.80),
            array('text' => 'How to make an appointment?', 'expected' => 'en', 'min_confidence' => 0.75),
            array('text' => 'Where is your clinic?', 'expected' => 'en', 'min_confidence' => 0.75),
            array('text' => 'Thank you very much', 'expected' => 'en', 'min_confidence' => 0.70),
            array('text' => 'I want to buy a product', 'expected' => 'en', 'min_confidence' => 0.75),
            
            // ===== PHRASES MIXTES (bonus) =====
            array('text' => 'Ndashaka kumenya le prix y\'umuti', 'expected' => 'rn', 'min_confidence' => 0.60),
            array('text' => 'Muraho, nshaka consultation', 'expected' => 'rn', 'min_confidence' => 0.65),
            array('text' => 'Ndafise ikibazo, nshaka kuvugana na docteur', 'expected' => 'rn', 'min_confidence' => 0.65),
            array('text' => 'Bonjour, ndashaka kumenya igiciro', 'expected' => 'rn', 'min_confidence' => 0.60),
            array('text' => 'Je veux prendre rendez-vous na muganga', 'expected' => 'fr', 'min_confidence' => 0.55),
        );
    }
}
