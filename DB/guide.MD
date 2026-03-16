## ✅ **VÉRIFICATION COMPLÈTE DU MENU & BASE DE DONNÉES**

### **CORRESPONDANCE PAGES ↔ TABLES**

| Page | Contenu | Table associée | Statut |
|------|---------|----------------|--------|
| **Page 1: Home** | Strategic Overview, Value addition, Key metrics | `pages` (slug='accueil') + `configurations` | ✅ OK |
| **Page 2: About AGF** | Corporate Profile, Manufacturing Facility, Vision, Mission, Objectives, Governance, Leadership, Structure | `pages` (slug='a-propos') + `temoignages` | ✅ OK |
| **Page 3: Facilities** | Land, Layout, Utilities, Warehousing, Labs, Animal Facility, Mandate, Model, Advantage, Capacity | `pages` (slug='installations') | ✅ OK |
| **Page 4: Products** | Produits 1-7 | `produits` + `produit_images` + `categories` | ✅ OK |
| **Page 5: Production** | Commercial Organic Farming, Farmland Projections, Project Fact Sheet, Executive Summary, Phases, Infrastructure, Technology, Supply Chain | `pages` (slug='production') | ✅ OK |
| **Page 6: Processing** | Workflows Produits 1-7 | `produits` + `pages` | ✅ OK |
| **Page 7: Digital Health** | Electronic intake, Telephonic, Virtual MD, Live Video, Wellness, E-Commerce | `consultations` + `medecins` + `produits` | ✅ OK |
| **Page 8: Investment** | Requirements, Brokers, Funding, Capital Model, Projections, Risk, Exit | `projets_investissement` | ✅ OK |
| **Page 9: Investor Relations** | Highlights, ROI, Use of Funds, Documentation, Due Diligence, Partners | `rapports_financiers` + `documents` | ✅ OK |
| **Page 10: Strategic Rationale** | Stratégie globale | `pages` (slug='strategie') | ✅ OK |
| **Page 11: ESG** | Impact Assessment, Governance, Certifications | `pages` (slug='esg') | ✅ OK |
| **Page 12: Partnerships** | Strategic Partners, Placement Agents, Institutional Collaborations | `contrats_partenariat` | ✅ OK |
| **Page 13: Research** | Market Analysis, Industry Report, Publications, Press Releases | `actualites` + `rapports_financiers` | ✅ OK |
| **Page 14: MOI** | Manifestation of Interest | `messages_contact` (type='investissement') | ✅ OK |
| **Page 15: EOI** | Expression of Interest | `messages_contact` (type='partenariat') | ✅ OK |
| **Page 16: Downloads** | Corporate Brochure, Project Memorandum, Technical, Legal | `documents` | ✅ OK |
| **Page 17: Contact** | Tel/WhatsApp, Emails, Inquiry Form, Partnerships, Brokerage, Negotiations, HQ | `messages_contact` + `configurations` | ✅ OK |

---

## 📋 **BASE DE DONNÉES COMPLÈTE ET CORRIGÉE**



# Documentation Technique Complète - African Green Farmers

## Table des matières
1. [Présentation générale](#1-présentation-générale)
2. [Architecture technique](#2-architecture-technique)
3. [Base de données](#3-base-de-données)
4. [Backend (PHP/CodeIgniter)](#4-backend-phpcodeigniter)
5. [Frontend](#5-frontend)
6. [Fonctionnalités détaillées](#6-fonctionnalités-détaillées)
7. [Sécurité](#7-sécurité)
8. [API et intégrations](#8-api-et-intégrations)
9. [Déploiement](#9-déploiement)
10. [Maintenance et monitoring](#10-maintenance-et-monitoring)

---

## 1. PRÉSENTATION GÉNÉRALE

### 1.1 Description du projet
**African Green Farmers (AGF)** est une plateforme web intégrée offrant trois services principaux :
- **Marketplace** : Achat de produits médicaux et agricoles
- **Téléconsultation** : Consultations médicales en visioconférence
- **Crowdfunding** : Investissement dans des projets agricoles et médicaux

### 1.2 Objectifs
- Faciliter l'accès aux produits et services médicaux
- Promouvoir l'agriculture durable
- Permettre le financement participatif de projets locaux

### 1.3 Public cible
- Particuliers (patients, agriculteurs)
- Professionnels de santé
- Investisseurs
- Vendeurs et porteurs de projets

---

## 2. ARCHITECTURE TECHNIQUE

### 2.1 Stack technologique

```
├── Frontend
│   ├── HTML5 / CSS3
│   ├── Bootstrap 5
│   ├── JavaScript (jQuery)
│   ├── Swiper.js (sliders)
│   └── Chart.js (graphiques)
│
├── Backend
│   ├── PHP 8.0+
│   ├── CodeIgniter 3 (MVC)
│   └── Jitsi Meet API (visioconférence)
│
├── Base de données
│   └── MySQL 8.0 / MariaDB 10.4+
│
├── Sécurité
│   ├── HTTPS / SSL
│   ├── CSP Headers
│   ├── XSS Protection
│   ├── CSRF Tokens
│   └── SQL Injection Prevention
│
└── Environnement
    ├── Apache/Nginx
    └── Linux/Windows Server
```

### 2.2 Structure MVC (CodeIgniter)

```
african-green-farmers/
│
├── application/
│   ├── config/
│   │   ├── database.php
│   │   ├── routes.php
│   │   └── config.php
│   │
│   ├── controllers/
│   │   ├── Auth.php
│   │   ├── Dashboard.php
│   │   ├── Consultation.php
│   │   ├── Produits.php
│   │   ├── Panier.php
│   │   ├── Commande.php
│   │   ├── Projets.php
│   │   ├── Investissement.php
│   │   └── Api.php
│   │
│   ├── models/
│   │   ├── User_model.php
│   │   ├── Consultation_model.php
│   │   ├── Produit_model.php
│   │   ├── Commande_model.php
│   │   ├── Projet_model.php
│   │   └── Investissement_model.php
│   │
│   ├── views/
│   │   ├── auth/
│   │   │   ├── login.php
│   │   │   └── register.php
│   │   │
│   │   ├── dashboard/
│   │   │   ├── patient.php
│   │   │   ├── medecin.php
│   │   │   └── admin.php
│   │   │
│   │   ├── consultation/
│   │   │   ├── index.php
│   │   │   ├── live.php
│   │   │   └── details.php
│   │   │
│   │   ├── produits/
│   │   │   ├── detail.php
│   │   │   └── liste.php
│   │   │
│   │   ├── panier/
│   │   │   └── panier.php
│   │   │
│   │   ├── commande/
│   │   │   ├── paiement.php
│   │   │   └── confirmation.php
│   │   │
│   │   ├── projets/
│   │   │   ├── liste.php
│   │   │   └── detail.php
│   │   │
│   │   ├── investissement/
│   │   │   ├── formulaire.php
│   │   │   └── mes-investissements.php
│   │   │
│   │   └── templates/
│   │       ├── header.php
│   │       └── footer.php
│   │
│   ├── libraries/
│   │   ├── Jitsi.php
│   │   └── Paiement.php
│   │
│   └── helpers/
│       └── security_helper.php
│
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── app.js
│   ├── images/
│   └── uploads/
│       ├── produits/
│       ├── users/
│       └── projets/
│
├── system/ (CodeIgniter core)
│
└── index.php
```

---

## 3. BASE DE DONNÉES

### 3.1 Structure complète

```sql
-- Voir le fichier SQL complet fourni précédemment
-- Tables principales : 25 tables
-- Vues : 3 vues
-- Triggers : 3 triggers
-- Index : 15+ index
```

### 3.2 Relations principales

```
users (1) ──┬── (0..n) commandes
            ├── (0..n) consultations (patient)
            ├── (0..n) investissements
            ├── (1)── vendeurs
            └── (1)── medecins

produits (n) ── (1) vendeurs
produits (n) ── (1) categories

commandes (1) ── (n) commande_lignes
commande_lignes (n) ── (1) produits

projets (1) ── (n) investissements
projets (1) ── (1) porteurs (users)

consultations (1) ── (1) medecins
consultations (1) ── (1) patients (users)
consultations (1) ── (n) prescriptions
consultations (1) ── (n) consultation_chats
```

---

## 4. BACKEND (PHP/CODEIGNITER)

### 4.1 Authentification (Auth.php)

```php
// Fonctionnalités principales
- register() : Inscription avec validation
- login() : Connexion sécurisée
- logout() : Déconnexion
- forgot_password() : Réinitialisation mot de passe
- verify_email() : Validation email
- two_factor() : Double authentification
```

**Sécurité :**
- Hashage bcrypt des mots de passe
- Protection CSRF
- Rate limiting sur tentatives de connexion
- Sessions avec régénération d'ID

### 4.2 Dashboard (Dashboard.php)

```php
// Rôles supportés
- patient() : Vue patient
- medecin() : Vue médecin
- admin() : Vue administrateur
- vendeur() : Vue vendeur
- investisseur() : Vue investisseur
```

**Fonctionnalités :**
- Statistiques personnalisées
- Graphiques d'évolution
- Gestion des rendez-vous
- Suivi des commandes
- Suivi des investissements

### 4.3 Consultation (Consultation.php)

```php
// Méthodes principales
- index() : Liste des consultations
- live($id) : Visioconférence Jitsi
- details($id) : Détails consultation
- send_message() : Chat en temps réel
- add_prescription() : Ajout prescription
- download_prescription() : PDF ordonnance
```

**Intégration Jitsi :**
```javascript
// Configuration P2P sécurisée
const options = {
    roomName: roomName,
    configOverwrite: {
        p2p: { enabled: true },
        enableP2P: true,
        enableE2EPing: true
    }
};
```

### 4.4 Produits (Produits.php)

```php
// Méthodes principales
- index() : Liste des produits avec filtres
- detail($id) : Page produit
- get_prix_degressif() : Calcul prix selon quantité
- search() : Recherche avancée
- get_categories() : Navigation par catégories
```

### 4.5 Panier (Panier.php)

```php
// Méthodes principales
- index() : Affichage panier
- add() : Ajout article
- update() : Modification quantité
- remove() : Suppression article
- calculate() : Calcul totaux (HT/TTC)
- apply_voucher() : Code promo
```

### 4.6 Commande (Commande.php)

```php
// Méthodes principales
- checkout() : Formulaire de commande
- process_payment() : Traitement paiement
- confirm() : Confirmation commande
- invoice() : Génération facture PDF
- track() : Suivi commande
```

### 4.7 Projets (Projets.php)

```php
// Méthodes principales
- index() : Liste projets avec filtres
- detail($id) : Page projet
- get_projets_vedette() : Projets à la une
- calculate_progress() : Calcul progression
```

### 4.8 Investissement (Investissement.php)

```php
// Méthodes principales
- form($projet_id) : Formulaire investissement
- process() : Traitement investissement
- my_investments() : Liste investissements
- returns_calculator() : Calculateur rendement
- download_contract() : Contrat PDF
```

---

## 5. FRONTEND

### 5.1 Structure des pages

#### Page d'accueil (index.php)
- Header avec navigation
- Section des 3 modules principaux
- Produits phares
- Section consultation
- Projets vedettes
- Newsletter
- Footer

#### Page authentification (auth_view.php)
- Formulaire connexion/inscription
- Validation en temps réel
- Messages d'erreur personnalisés
- Design responsive

#### Page produit (produit-detail.php)
- Galerie d'images
- Tableau prix dégressifs
- Calculateur de quantité
- Tabs description/fiche technique
- Produits recommandés

#### Page panier (panier.php)
- Articles par vendeur
- Tableaux de prix par article
- Résumé commande
- Suggestions produits

#### Page paiement (paiement.php)
- Multiples modes de paiement
- Calculateur investissement
- Formulaire sécurisé
- Récapitulatif commande

#### Page confirmation (confirmation.php)
- Animation succès
- Suivi commande
- Détails commande
- Recommandations
- Téléchargement facture

#### Page projets (projets.php)
- Bannière statistiques
- Filtres avancés
- Slider catégories
- Grille projets avec badges
- Newsletter

#### Page détail projet (projet-detail.php)
- Galerie projet
- Barre de progression
- Statistiques investissement
- Calculateur rendement
- FAQ
- Documents téléchargeables

#### Page investissement (investir.php)
- Résumé projet
- Calculateur parts
- Options investissement
- Formulaire informations
- Récapitulatif temps réel

#### Page mes investissements (mes-investissements.php)
- Statistiques globales
- Graphique évolution
- Filtres
- Vue carte/tableau
- Liste investissements actifs/terminés

### 5.2 Composants réutilisables

```css
/* Variables CSS */
:root {
    --vert-feuilles: rgb(34, 139, 34);
    --vert-agf: rgb(0, 128, 0);
    --vert-fonce: rgb(0, 100, 0);
    --bleu-ruban: rgb(0, 0, 255);
}

/* Cards */
.card {
    border-radius: 20px;
    box-shadow: var(--shadow-md);
    transition: transform 0.3s;
}

.card:hover {
    transform: translateY(-5px);
}

/* Boutons */
.btn-primary {
    background: var(--vert-feuilles);
    border-radius: 50px;
    padding: 12px 30px;
}

/* Progress bars */
.progress-bar-custom {
    height: 8px;
    background: var(--border-light);
    border-radius: 10px;
}

.progress-fill {
    background: linear-gradient(90deg, var(--vert-feuilles), var(--vert-fonce));
}
```

### 5.3 JavaScript

```javascript
// Gestionnaire AJAX global
const ajaxRequest = (url, data, successCallback, errorCallback) => {
    $.ajax({
        url: url,
        type: 'POST',
        data: data,
        dataType: 'json',
        success: successCallback,
        error: errorCallback
    });
};

// Validation formulaires
const validators = {
    email: (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email),
    password: (pwd) => pwd.length >= 8,
    age: (birthDate) => {
        const age = new Date().getFullYear() - new Date(birthDate).getFullYear();
        return age >= 18;
    }
};

// Calculateur prix dégressifs
const calculatePrice = (quantity, priceTable) => {
    for (let tier of priceTable) {
        if (quantity >= tier.min && quantity <= tier.max) {
            return quantity * tier.price;
        }
    }
    return quantity * priceTable[0].price;
};

// Timer consultation
class ConsultationTimer {
    constructor(duration) {
        this.duration = duration;
        this.interval = null;
    }
    
    start() {
        this.interval = setInterval(() => {
            this.duration--;
            if (this.duration <= 0) this.end();
        }, 1000);
    }
    
    end() {
        clearInterval(this.interval);
        // Auto-hangup
    }
}
```

---

## 6. FONCTIONNALITÉS DÉTAILLÉES

### 6.1 Module Marketplace

#### Parcours utilisateur
1. Navigation par catégories
2. Recherche produits
3. Consultation fiche produit (prix dégressifs)
4. Ajout au panier
5. Validation panier
6. Paiement sécurisé
7. Confirmation commande
8. Suivi livraison

#### Fonctionnalités spécifiques
- **Prix dégressifs** : Calcul automatique selon quantité
- **Multi-vendeurs** : Regroupement par vendeur dans panier
- **Calculateur** : Simulation montant total en temps réel
- **Téléchargement facture** : PDF généré automatiquement

### 6.2 Module Téléconsultation

#### Parcours utilisateur
1. Prise de rendez-vous (symptômes, date)
2. Confirmation et paiement
3. Réception lien visio
4. Consultation vidéo (Jitsi)
5. Chat avec médecin
6. Réception ordonnance
7. Achat médicaments prescrits

#### Fonctionnalités spécifiques
- **Visioconférence P2P** : Chiffrée, sans serveur intermédiaire
- **Chat en direct** : Messages instantanés
- **Prescriptions** : Ajout en temps réel
- **Timer** : Suivi durée consultation
- **Reconnexion auto** : 3 tentatives maximum

### 6.3 Module Investissement

#### Parcours utilisateur
1. Exploration projets
2. Consultation détails (objectif, rendement)
3. Simulation investissement
4. Choix montant et type
5. Paiement
6. Suivi investissement
7. Réception dividendes

#### Fonctionnalités spécifiques
- **Calculateur rendement** : Projection sur la durée
- **Graphiques** : Évolution prévisionnelle
- **Documents** : Business plan, étude de marché
- **Portefeuille** : Vue d'ensemble investissements
- **Paiements récurrents** : Versement automatique dividendes

---

## 7. SÉCURITÉ

### 7.1 Protection contre les attaques courantes

| Attaque | Protection |
|---------|------------|
| SQL Injection | Prepared statements, ORM |
| XSS | htmlspecialchars(), CSP |
| CSRF | Tokens, SameSite cookies |
| Session hijacking | Régénération ID, HTTPS |
| Brute force | Rate limiting, CAPTCHA |
| Man-in-the-middle | HTTPS, HSTS |

### 7.2 Headers de sécurité

```php
// .htaccess
Header set X-Frame-Options "SAMEORIGIN"
Header set X-Content-Type-Options "nosniff"
Header set X-XSS-Protection "1; mode=block"
Header set Referrer-Policy "strict-origin-when-cross-origin"
Header set Content-Security-Policy "default-src 'self'; script-src 'self' https://meet.jit.si; style-src 'self' 'unsafe-inline'"
Header set Strict-Transport-Security "max-age=31536000; includeSubDomains"
```

### 7.3 Validation des données

```php
// Côté serveur
$this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[users.email]');
$this->form_validation->set_rules('password', 'Mot de passe', 'required|min_length[8]');
$this->form_validation->set_rules('nom', 'Nom', 'required|regex_match[/^[a-zA-ZÀ-ÿ\s-]+$/]');

// Côté client
function validateForm() {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    if (!validators.email(email)) {
        showError('Email invalide');
        return false;
    }
    
    if (!validators.password(password)) {
        showError('Mot de passe trop court');
        return false;
    }
    
    return true;
}
```

### 7.4 Gestion des sessions

```php
// Sécurisation session
session_start();
session_regenerate_id(true);

$_SESSION['user_id'] = $user->id;
$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
$_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

// Vérification session
if ($_SESSION['ip'] !== $_SERVER['REMOTE_ADDR'] || 
    $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
    session_destroy();
    redirect('auth');
}
```

---

## 8. API ET INTÉGRATIONS

### 8.1 API Jitsi Meet

```javascript
// Configuration
const domain = "meet.jit.si";
const options = {
    roomName: "AGF-" + consultation_id,
    width: "100%",
    height: "100%",
    parentNode: document.querySelector('#meet'),
    configOverwrite: {
        startWithAudioMuted: false,
        startWithVideoMuted: false,
        enableWelcomePage: false,
        enableClosePage: false,
        p2p: {
            enabled: true,
            preferH264: true
        }
    }
};

const api = new JitsiMeetExternalAPI(domain, options);

// Événements
api.addEventListener('videoConferenceJoined', handleJoin);
api.addEventListener('participantJoined', handleParticipantJoin);
api.addEventListener('readyToClose', handleClose);
```

### 8.2 API Paiement (Orange Money, etc.)

```php
// Exemple intégration Orange Money
class OrangeMoneyAPI {
    private $apiKey;
    private $apiSecret;
    private $endpoint = "https://api.orange.com/om/v1";
    
    public function processPayment($amount, $phone, $orderId) {
        $data = [
            'amount' => $amount,
            'currency' => 'XOF',
            'customer' => $phone,
            'order_id' => $orderId
        ];
        
        $headers = [
            'Authorization: Bearer ' . $this->getToken(),
            'Content-Type: application/json'
        ];
        
        return $this->callAPI('/payments', $data, $headers);
    }
}
```

### 8.3 Génération PDF (factures, ordonnances)

```php
use Dompdf\Dompdf;

class PDFGenerator {
    public function generateInvoice($order) {
        $dompdf = new Dompdf();
        $html = $this->load->view('commandes/invoice', $order, true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("facture-{$order->numero}.pdf");
    }
}
```

---

## 9. DÉPLOIEMENT

### 9.1 Prérequis serveur

```
PHP 8.0 ou supérieur
MySQL 8.0 ou MariaDB 10.4
Apache 2.4 / Nginx 1.18
Extensions PHP : mysqli, gd, curl, json, mbstring
SSL Certificate
```

### 9.2 Configuration serveur

#### Apache Virtual Host
```apache
<VirtualHost *:443>
    ServerName www.africangreenfarmers.org
    DocumentRoot /var/www/agf
    
    <Directory /var/www/agf>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/agf.crt
    SSLCertificateKeyFile /etc/ssl/private/agf.key
    
    ErrorLog ${APACHE_LOG_DIR}/agf-error.log
    CustomLog ${APACHE_LOG_DIR}/agf-access.log combined
</VirtualHost>
```

#### Nginx
```nginx
server {
    listen 443 ssl;
    server_name www.africangreenfarmers.org;
    root /var/www/agf;
    index index.php;
    
    ssl_certificate /etc/ssl/certs/agf.crt;
    ssl_certificate_key /etc/ssl/private/agf.key;
    
    location / {
        try_files $uri $uri/ /index.php?$args;
    }
    
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
    }
    
    location ~ /\.ht {
        deny all;
    }
}
```

### 9.3 Configuration CodeIgniter

```php
// config/database.php
$db['default'] = array(
    'dsn'   => '',
    'hostname' => 'localhost',
    'username' => 'agf_user',
    'password' => getenv('DB_PASSWORD'),
    'database' => 'africangreenfarmers',
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => (ENVIRONMENT !== 'production'),
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8mb4',
    'dbcollat' => 'utf8mb4_unicode_ci',
    'swap_pre' => '',
    'encrypt' => FALSE,
    'compress' => FALSE,
    'stricton' => FALSE,
    'failover' => array(),
    'save_queries' => (ENVIRONMENT !== 'production')
);

// config/config.php
$config['base_url'] = 'https://www.africangreenfarmers.org/';
$config['sess_driver'] = 'database';
$config['sess_save_path'] = 'ci_sessions';
$config['sess_expiration'] = 7200;
$config['sess_match_ip'] = TRUE;
$config['sess_time_to_update'] = 300;
```

### 9.4 Variables d'environnement

```env
# .env
DB_HOST=localhost
DB_NAME=africangreenfarmers
DB_USER=agf_user
DB_PASS=SecurePassword123
JITSI_API_KEY=xxx
PAYPAL_CLIENT_ID=xxx
ORANGE_MONEY_API_KEY=xxx
MAIL_HOST=smtp.gmail.com
MAIL_USER=contact@africangreenfarmers.org
MAIL_PASS=xxx
```

---

## 10. MAINTENANCE ET MONITORING

### 10.1 Scripts de maintenance

```bash
#!/bin/bash
# backup.sh - Sauvegarde quotidienne

DATE=$(date +%Y%m%d)
BACKUP_DIR="/backups/agf"

# Sauvegarde base de données
mysqldump -u agf_user -p africangreenfarmers > $BACKUP_DIR/db_$DATE.sql

# Sauvegarde fichiers
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/agf/assets/uploads/

# Nettoyage fichiers > 30 jours
find $BACKUP_DIR -type f -mtime +30 -delete
```

### 10.2 Monitoring

```php
// libraries/Monitoring.php
class Monitoring {
    public function checkHealth() {
        $checks = [
            'database' => $this->checkDatabase(),
            'disk' => $this->checkDiskSpace(),
            'memory' => $this->checkMemory(),
            'jitsi' => $this->checkJitsiAPI()
        ];
        
        if (in_array(false, $checks)) {
            $this->sendAlert('System health check failed');
        }
        
        return $checks;
    }
    
    private function checkDatabase() {
        try {
            $this->db->query("SELECT 1");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
```

### 10.3 Logs et erreurs

```php
// helpers/log_helper.php
function log_activity($action, $details = null) {
    $CI =& get_instance();
    $data = [
        'user_id' => $CI->session->userdata('user_id'),
        'action' => $action,
        'description' => $details,
        'ip_address' => $CI->input->ip_address(),
        'user_agent' => $CI->input->user_agent(),
        'url' => current_url(),
        'method' => $CI->input->method(),
        'niveau' => 'info'
    ];
    
    $CI->db->insert('logs', $data);
}

// Gestion des erreurs
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    log_activity('error', "$errstr in $errfile:$errline");
    
    if (ENVIRONMENT === 'production') {
        return true; // Masquer erreurs en prod
    }
    return false;
});
```

### 10.4 Cache et optimisation

```php
// libraries/CacheManager.php
class CacheManager {
    public function cacheProduct($product_id) {
        $this->load->driver('cache');
        
        // Vérifier si en cache
        $product = $this->cache->get("product_$product_id");
        
        if (!$product) {
            $product = $this->db->get_where('produits', ['id' => $product_id])->row();
            $this->cache->save("product_$product_id", $product, 3600);
        }
        
        return $product;
    }
    
    public function clearProductCache($product_id) {
        $this->cache->delete("product_$product_id");
    }
}
```

---

## 11. GUIDE DE DÉVELOPPEMENT

### 11.1 Standards de code

```php
// Nommage
class User_model          // Classes: PascalCase
function getUserById()     // Méthodes: camelCase
$userName                 // Variables: camelCase
TABLE_USERS               // Constantes: UPPERCASE

// Commentaires
/**
 * Récupère un utilisateur par son ID
 * @param int $id ID utilisateur
 * @return object|null
 */
public function getUserById($id) {
    return $this->db->get_where('users', ['id' => $id])->row();
}

// Indentation
if ($condition) {
    $result = $this->doSomething();
    if ($result) {
        return $result;
    }
}
```

### 11.2 Workflow Git

```bash
# Branches
main           # Production
develop        # Développement
feature/*      # Nouvelles fonctionnalités
hotfix/*       # Corrections urgentes
release/*      # Préparation release

# Commits conventionnels
git commit -m "feat: ajout module investissement"
git commit -m "fix: correction calcul prix dégressifs"
git commit -m "docs: mise à jour documentation"
git commit -m "style: formatage CSS"
git commit -m "refactor: optimisation requêtes SQL"
```

### 11.3 Tests

```php
// tests/TestAuth.php
class TestAuth extends CI_TestCase {
    public function testLoginSuccess() {
        $_POST['email'] = 'test@test.com';
        $_POST['password'] = 'password123';
        
        $this->request('POST', 'auth/login');
        $this->assertResponseCode(200);
        $this->assertSessionHas('user_id');
    }
    
    public function testLoginFail() {
        $_POST['email'] = 'wrong@test.com';
        $_POST['password'] = 'wrong';
        
        $this->request('POST', 'auth/login');
        $this->assertResponseCode(200);
        $this->assertSessionNotHas('user_id');
    }
}
```

---

## 12. ANNEXES

### 12.1 Diagramme de navigation

```
Accueil
├── Boutique
│   ├── Catégories
│   ├── Produit
│   └── Panier → Paiement → Confirmation
├── Consultations
│   ├── Prise RDV
│   ├── Visio → Chat → Prescription
│   └── Historique
├── Investissement
│   ├── Projets
│   ├── Détail projet → Investir → Confirmation
│   └── Mes investissements
├── Mon compte
│   ├── Profil
│   ├── Commandes
│   ├── Consultations
│   └── Investissements
└── Admin
    ├── Dashboard
    ├── Utilisateurs
    ├── Produits
    ├── Commandes
    ├── Consultations
    └── Projets
```

### 12.2 Codes d'erreur

| Code | Description |
|------|-------------|
| 1001 | Email déjà utilisé |
| 1002 | Mot de passe incorrect |
| 1003 | Compte désactivé |
| 2001 | Produit indisponible |
| 2002 | Stock insuffisant |
| 3001 | Consultation inexistante |
| 3002 | Heure de consultation dépassée |
| 4001 | Projet clôturé |
| 4002 | Montant minimum non atteint |

### 12.3 Événements système

```php
// events.php
$events = [
    'user.register' => ['EmailSender', 'sendWelcomeEmail'],
    'user.login' => ['Logger', 'logLogin'],
    'order.created' => ['EmailSender', 'sendOrderConfirmation'],
    'order.paid' => ['StockManager', 'updateStock'],
    'consultation.started' => ['JitsiAPI', 'createRoom'],
    'investment.made' => ['PDFGenerator', 'generateContract']
];
```

---

## Conclusion

Cette documentation couvre l'intégralité du fonctionnement du site African Green Farmers, depuis l'architecture technique jusqu'au déploiement en production. Le site est conçu pour être :

- **Sécurisé** : Protection contre les attaques courantes
- **Scalable** : Architecture modulaire et optimisée
- **Maintenable** : Code structuré et documenté
- **Performant** : Cache, index, requêtes optimisées
- **Utilisable** : Interface intuitive et responsive

Pour toute question ou contribution, contacter l'équipe technique à dev@africangreenfarmers.org.