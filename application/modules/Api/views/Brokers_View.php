<?php
// Définition des fonctions manquantes (inchangé)
if (!function_exists('fixer_chemin_image')) {
    function fixer_chemin_image($chemin) {
        if (empty($chemin)) return '';
        if (preg_match('#^https?://#', $chemin)) {
            return $chemin;
        }
        $CI =& get_instance();
        return $CI->config->base_url($chemin);
    }
}

if (!function_exists('fixer_images_contenu')) {
    function fixer_images_contenu($contenu) {
        if (empty($contenu)) return $contenu;
        return preg_replace_callback(
            '<img\s+[^>]*src=["\']([^"\']+)["\'][^>]*>/i',
            function($correspondances) {
                $ancien_src = $correspondances[1];
                $nouveau_src = fixer_chemin_image($ancien_src);
                return str_replace($ancien_src, $nouveau_src, $correspondances[0]);
            },
            $contenu
        );
    }
}
?>

<?php include VIEWPATH . 'includes/frontend/EnTete.php'; ?>

<style>
    :root {
        --primaire: #0B4F2E;
        --primaire-clair: #1B7B4B;
        --primaire-plus-clair: #e8f5e9;
        --accent: #27ae60;
        --avertissement: #FF6B35;
        --erreur: #E74C3C;
        --info: #3498DB;
        --texte-sombre: #1a2e3f;
        --texte-fade: #64748b;
        --texte-clair: #94a3b8;
        --fond-clair: #f8fafc;
        --fond-chaud: #faf9f7;
        --bordure: #e2e8f0;
        --ombre-legere: 0 4px 15px rgba(0,0,0,0.05);
        --ombre-moyenne: 0 10px 30px -10px rgba(0,0,0,0.1);
        --ombre-forte: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        --rayon-petit: 12px;
        --rayon-moyen: 16px;
        --rayon-grand: 24px;
    }

    /* ===== SECTION HERO ===== */
    .page-hero {
        position: relative;
        min-height: 60vh;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: white;
        background-color: #0f4c3a;
        overflow: hidden;
        padding: 4rem 0;
    }
    .hero-image-fond {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        z-index: 1;
    }
    .hero-degrade-superposition {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(15,76,58,0.85) 0%, rgba(26,95,74,0.9) 100%);
        z-index: 2;
    }
    .page-hero .conteneur {
        position: relative;
        z-index: 3;
    }
    .page-hero-titre {
        font-size: clamp(2.5rem, 6vw, 4rem);
        font-weight: 700;
        margin-bottom: 1rem;
        text-shadow: 2px 2px 8px rgba(0,0,0,0.2);
    }
    .page-hero-titre span {
        display: block;
        font-size: 1.5rem;
        font-weight: 400;
        color: #d4af37;
    }
    .page-hero-sous-titre {
        font-size: 1.25rem;
        max-width: 700px;
        margin: 1.5rem auto;
        opacity: 0.95;
    }
    .bouton-cta {
        display: inline-block;
        background: #d4af37;
        color: #0f4c3a;
        font-weight: 600;
        padding: 0.9rem 2.5rem;
        border-radius: 50px;
        text-decoration: none;
        transition: all 0.3s;
        border: 2px solid #d4af37;
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }
    .bouton-cta:hover {
        background: transparent;
        color: #fff;
        border-color: #fff;
    }

    /* ===== SECTIONS TEXTE (CARTES) ===== */
    .section-texte {
        padding: 5rem 0;
        background-color: #fff;
    }
    .section-texte:nth-child(even) {
        background-color: #f9f9f9;
    }

    .carte-personnalisee {
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 20px 40px -12px rgba(0,32,64,0.15);
        padding: 2.5rem;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid rgba(0,0,0,0.03);
    }
    .carte-personnalisee:hover {
        transform: translateY(-5px);
        box-shadow: 0 30px 50px -12px rgba(15,76,58,0.25);
    }

    .section-badge {
        display: inline-block;
        font-size: 0.9rem;
        font-weight: 600;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: #d4af37;
        background: rgba(212,175,55,0.1);
        padding: 0.3rem 1rem;
        border-radius: 30px;
        margin-bottom: 1rem;
    }

    .section-titre {
        font-size: clamp(1.8rem, 4vw, 2.5rem);
        font-weight: 700;
        color: #0f4c3a;
        margin-bottom: 1.2rem;
    }

    .contenu-tinymce {
        font-size: 1.1rem;
        color: #334155;
    }
    .contenu-tinymce p:last-child {
        margin-bottom: 0;
    }
    .contenu-tinymce.texte-centre {
        text-align: center;
    }

    .bouton-primaire-personnalise {
        display: inline-block;
        background: #0f4c3a;
        color: white;
        font-weight: 600;
        padding: 0.8rem 2rem;
        border-radius: 40px;
        text-decoration: none;
        border: 2px solid #0f4c3a;
        transition: all 0.3s;
    }
    .bouton-primaire-personnalise:hover {
        background: transparent;
        color: #0f4c3a;
    }

    .contenant-image {
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .contenant-image img {
        width: 100%;
        height: auto;
        object-fit: cover;
        transition: transform 0.5s;
    }
    .contenant-image:hover img {
        transform: scale(1.05);
    }

    .g-5 {
        --bs-gutter-y: 3rem;
    }

    @media (max-width: 768px) {
        .section-texte {
            padding: 3rem 0;
        }
        .carte-personnalisee {
            padding: 1.8rem;
        }
        .page-hero-titre {
            font-size: 2.2rem;
        }
    }

    .contenant-principal {
        max-width: 1400px;
        margin: 0 auto 3rem;
        padding: 0 1.5rem;
    }

    .carte-formulaire {
        background: white;
        border-radius: var(--rayon-grand);
        box-shadow: var(--ombre-forte);
        overflow: hidden;
    }

    /* Étapes de progression */
    .etapes-progression {
        display: flex;
        justify-content: center;
        padding: 2rem;
        background: var(--fond-chaud);
        border-bottom: 1px solid var(--bordure);
        gap: 1rem;
        flex-wrap: wrap;
    }

    .etape {
        display: flex;
        align-items: center;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        background: white;
        border: 2px solid var(--bordure);
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .etape.active {
        border-color: var(--primaire);
        background: linear-gradient(135deg, var(--primaire-plus-clair) 0%, white 100%);
        box-shadow: var(--ombre-legere);
    }

    .etape.completee {
        border-color: var(--accent);
        background: #dcfce7;
    }

    .numero-etape {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: var(--bordure);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        margin-right: 0.75rem;
        color: var(--texte-fade);
    }

    .etape.active .numero-etape {
        background: var(--primaire);
        color: white;
    }

    .etape.completee .numero-etape {
        background: var(--accent);
        color: white;
    }

    .etape.completee .numero-etape i {
        font-size: 0.8rem;
    }

    .texte-etape {
        font-weight: 500;
        color: var(--texte-fade);
    }

    .etape.active .texte-etape {
        color: var(--primaire);
    }

    .etape.completee .texte-etape {
        color: var(--accent);
    }

    /* Sections formulaire */
    .section-formulaire {
        padding: 2.5rem;
        display: none;
    }

    .section-formulaire.active {
        display: block;
        animation: fadeIn 0.5s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .secousse {
        animation: secousse 0.5s;
    }

    @keyframes secousse {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-10px); }
        75% { transform: translateX(10px); }
    }

    .titre-section {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--primaire);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .sous-titre-section {
        color: var(--texte-fade);
        margin-bottom: 2rem;
        font-size: 0.95rem;
        border-left: 3px solid var(--primaire-clair);
        padding-left: 1rem;
    }

    /* Contrôles formulaire */
    .label-formulaire {
        font-weight: 500;
        color: var(--texte-sombre);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .controle-formulaire, .selection-formulaire {
        border: 2px solid var(--bordure);
        border-radius: var(--rayon-petit);
        padding: 0.875rem 1rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background: var(--fond-clair);
    }

    .controle-formulaire:focus, .selection-formulaire:focus {
        border-color: var(--primaire);
        box-shadow: 0 0 0 4px rgba(11, 79, 46, 0.1);
        background: white;
    }

    .controle-formulaire.invalide, .selection-formulaire.invalide {
        border-color: var(--erreur);
        background-image: none;
    }

    .controle-formulaire.valide, .selection-formulaire.valide {
        border-color: var(--accent);
    }

    .retour-invalide {
        color: var(--erreur);
        font-size: 0.875rem;
        margin-top: 0.25rem;
        display: none;
    }

    .controle-formulaire.invalide ~ .retour-invalide {
        display: block;
    }

    .groupe-saisie-texte {
        background: var(--fond-clair);
        border: 2px solid var(--bordure);
        border-right: none;
        color: var(--texte-fade);
        border-radius: var(--rayon-petit) 0 0 var(--rayon-petit);
    }

    .groupe-saisie .controle-formulaire {
        border-left: none;
        border-radius: 0 var(--rayon-petit) var(--rayon-petit) 0;
    }

    .groupe-saisie:focus-within .groupe-saisie-texte {
        border-color: var(--primaire);
    }

    /* Compteur caractères */
    .compteur-caracteres {
        font-size: 0.75rem;
        color: var(--texte-fade);
        margin-top: 0.25rem;
        text-align: right;
    }

    .compteur-caracteres.avertissement {
        color: var(--avertissement);
    }

    .compteur-caracteres.danger {
        color: var(--erreur);
    }

    /* Cartes cases à cocher */
    .grille-coches {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
        margin: 1rem 0;
    }

    .carte-coche {
        border: 2px solid var(--bordure);
        border-radius: var(--rayon-petit);
        padding: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        background: white;
    }

    .carte-coche:hover {
        border-color: var(--primaire);
        transform: translateY(-2px);
        box-shadow: var(--ombre-legere);
    }

    .carte-coche.selectionnee {
        border-color: var(--primaire);
        background: var(--primaire-plus-clair);
        box-shadow: 0 5px 15px rgba(11, 79, 46, 0.1);
    }

    .carte-coche input[type="checkbox"] {
        display: none;
    }

    .icone-coche {
        width: 50px;
        height: 50px;
        border-radius: var(--rayon-petit);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.75rem;
        font-size: 1.5rem;
        transition: all 0.3s ease;
    }

    .carte-coche.selectionnee .icone-coche {
        transform: scale(1.1);
    }

    /* Recherche pays */
    .contenant-recherche-pays {
        position: relative;
    }

    .liste-deroulante-pays {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        max-height: 300px;
        overflow-y: auto;
        background: white;
        border: 2px solid var(--bordure);
        border-radius: var(--rayon-petit);
        box-shadow: var(--ombre-moyenne);
        z-index: 1050;
        display: none;
    }

    .liste-deroulante-pays.afficher {
        display: block;
    }

    .option-pays {
        padding: 0.75rem 1rem;
        cursor: pointer;
        transition: all 0.2s ease;
        border-bottom: 1px solid var(--bordure);
    }

    .option-pays:last-child {
        border-bottom: none;
    }

    .option-pays:hover,
    .option-pays.active {
        background: var(--primaire-plus-clair);
    }

    .badge-pays-selectionne {
        background: var(--primaire-plus-clair);
        border: 1px solid var(--primaire);
        border-radius: 50px;
        padding: 0.5rem 1rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.5rem;
        font-size: 0.9rem;
    }

    /* Boîte info */
    .boite-info {
        background: linear-gradient(135deg, rgba(52, 152, 219, 0.1) 0%, rgba(52, 152, 219, 0.05) 100%);
        border-left: 4px solid var(--info);
        border-radius: var(--rayon-petit);
        padding: 1.5rem;
        margin-bottom: 2rem;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }

    .boite-info i {
        color: var(--info);
        font-size: 2rem;
    }

    /* Section résumé */
    .section-resume {
        background: var(--fond-chaud);
        border-radius: var(--rayon-petit);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .section-resume h6 {
        color: var(--primaire);
        font-weight: 600;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .element-resume {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px dashed var(--bordure);
    }

    .element-resume:last-child {
        border-bottom: none;
    }

    .label-resume {
        color: var(--texte-fade);
        font-size: 0.9rem;
    }

    .valeur-resume {
        font-weight: 600;
        color: var(--texte-sombre);
    }

    .badge-personnalise {
        background: var(--primaire-plus-clair);
        color: var(--primaire);
        padding: 0.35rem 0.75rem;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 500;
        display: inline-block;
        margin: 0.25rem;
    }

    /* Boutons */
    .btn-nav {
        padding: 1rem 2.5rem;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border: none;
    }

    .btn-suivant {
        background: linear-gradient(135deg, var(--primaire) 0%, var(--primaire-clair) 100%);
        color: white;
    }

    .btn-suivant:hover:not(:disabled) {
        transform: translateX(5px);
        box-shadow: 0 10px 25px rgba(11, 79, 46, 0.3);
        color: white;
    }

    .btn-precedent {
        background: white;
        color: var(--texte-fade);
        border: 2px solid var(--bordure);
    }

    .btn-precedent:hover {
        border-color: var(--primaire);
        color: var(--primaire);
    }

    .btn-soumettre {
        background: linear-gradient(135deg, var(--primaire) 0%, var(--primaire-clair) 100%);
        color: white;
        border: none;
        padding: 1.25rem 3rem;
        font-size: 1.1rem;
        width: 100%;
        border-radius: 50px;
        position: relative;
        overflow: hidden;
    }

    .btn-soumettre:hover:not(:disabled) {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(11, 79, 46, 0.3);
    }

    .btn-soumettre.chargement {
        pointer-events: none;
        opacity: 0.8;
    }

    .btn-soumettre.chargement .texte-bouton {
        visibility: hidden;
    }

    .btn-soumettre.chargement::after {
        content: '';
        position: absolute;
        width: 20px;
        height: 20px;
        top: 50%;
        left: 50%;
        margin: -10px 0 0 -10px;
        border: 3px solid rgba(255,255,255,0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Conteneur carte */
    .contenant-carte {
        height: 400px;
        border-radius: var(--rayon-moyen);
        overflow: hidden;
        box-shadow: var(--ombre-moyenne);
        margin: 2rem 0;
    }

    #carte {
        width: 100%;
        height: 100%;
    }

    /* Notifications Toast */
    .conteneur-toast {
        position: fixed;
        bottom: 100px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 99999;
        display: flex;
        flex-direction: column-reverse;
        align-items: center;
        gap: 0.75rem;
        padding: 0 20px;
        pointer-events: none;
        max-width: 100%;
        width: auto;
    }

    .toast-personnalise {
        background: white;
        border-radius: var(--rayon-moyen);
        padding: 1.25rem;
        box-shadow: var(--ombre-forte);
        display: flex;
        align-items: center;
        gap: 1rem;
        width: 100%;
        transform: translateX(120%);
        animation: glisser 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
        border-left: 4px solid;
        pointer-events: all;
        position: relative;
    }

    .toast-personnalise.succes {
        border-left-color: var(--accent);
    }

    .toast-personnalise.erreur {
        border-left-color: var(--erreur);
    }

    .toast-personnalise.avertissement {
        border-left-color: var(--avertissement);
    }

    @keyframes glisser {
        to { transform: translateX(0); }
    }

    @keyframes glisserSortie {
        to { transform: translateX(120%); opacity: 0; }
    }

    .toast-personnalise.disparait {
        animation: glisserSortie 0.5s ease forwards;
    }

    .icone-toast {
        width: 48px;
        height: 48px;
        border-radius: var(--rayon-petit);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .toast-personnalise.succes .icone-toast {
        background: #dcfce7;
        color: var(--accent);
    }

    .toast-personnalise.erreur .icone-toast {
        background: #fee2e2;
        color: var(--erreur);
    }

    .toast-personnalise.avertissement .icone-toast {
        background: #ffedd5;
        color: var(--avertissement);
    }

    .contenu-toast {
        flex: 1;
        min-width: 0;
    }

    .contenu-toast h4 {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
        color: var(--texte-sombre);
    }

    .contenu-toast p {
        margin: 0;
        color: var(--texte-fade);
        font-size: 0.9rem;
        word-wrap: break-word;
    }

    .fermer-toast {
        background: none;
        border: none;
        color: var(--texte-fade);
        cursor: pointer;
        padding: 0.5rem;
        border-radius: var(--rayon-petit);
        transition: all 0.3s;
        flex-shrink: 0;
    }

    .fermer-toast:hover {
        background: #f1f5f9;
        color: var(--texte-sombre);
    }

    @media (max-width: 576px) {
        .conteneur-toast {
            top: 80px;
            right: 10px;
            left: 10px;
            max-width: none;
        }
    }

    /* Barre de navigation */
    .navbar {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        padding: 1rem 0;
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
    }

    .navbar-brand {
        font-weight: 700;
        font-size: 1.5rem;
        color: var(--primaire) !important;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .navbar-brand i {
        background: linear-gradient(135deg, var(--primaire), var(--accent));
        color: white;
        width: 40px;
        height: 40px;
        border-radius: var(--rayon-petit);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    /* En-tête */
    .entete-page {
        background: linear-gradient(135deg, var(--primaire) 0%, var(--primaire-clair) 100%);
        padding: 3rem 0;
        position: relative;
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .entete-page::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        border-radius: 50%;
    }

    .entete-page h1 {
        color: white;
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        position: relative;
    }

    .entete-page p {
        color: rgba(255,255,255,0.9);
        font-size: 1.1rem;
        max-width: 600px;
        position: relative;
    }

    /* Infobulle */
    .icone-infobulle {
        color: var(--info);
        cursor: help;
        font-size: 0.9rem;
        margin-left: 0.25rem;
    }

    /* Séparateur */
    .separateur {
        display: flex;
        align-items: center;
        text-align: center;
        color: var(--texte-fade);
        margin: 2rem 0;
    }

    .separateur::before,
    .separateur::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid var(--bordure);
    }

    .separateur span {
        padding: 0 1rem;
    }

    /* Lien statut */
    .lien-statut {
        text-align: center;
        margin-top: 2rem;
    }

    .lien-statut a {
        color: var(--primaire);
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s;
    }

    .lien-statut a:hover {
        color: var(--primaire-clair);
        text-decoration: underline;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .entete-page h1 {
            font-size: 2rem;
        }
        .section-formulaire {
            padding: 1.5rem;
        }
        .etapes-progression {
            padding: 1rem;
        }
        .texte-etape {
            display: none;
        }
        .toast-personnalise {
            min-width: auto;
            max-width: 90vw;
        }
        .grille-coches {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 576px) {
        .grille-coches {
            grid-template-columns: 1fr;
        }
        .btn-nav {
            padding: 0.75rem 1.5rem;
        }
    }
</style>

<!-- Conteneur Toast -->
<div id="conteneurToast" class="conteneur-toast"></div>

<?php
// ============================================
// SECTION HERO (unique)
// ============================================
if (!empty($hero)):
    $options = $hero['options'] ?? [];
    $opacite_image = $options['opacite_image'] ?? '0.85';
    $contenu_brut = $hero['contenu_texte'] ?? '';
?>
    <section class="page-hero <?= $hero['classe_personnalisee'] ?? '' ?>">
        <?php if (!empty($hero['url_image'])): ?>
            <div class="hero-image-fond" style="background-image: url('<?= fixer_chemin_image($hero['url_image']) ?>'); opacity: <?= $opacite_image ?>;"></div>
        <?php endif; ?>
        <div class="hero-degrade-superposition"></div>
        <div class="conteneur position-relative">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <h1 class="page-hero-titre">
                        <?= $hero['titre_section'] ?? '' ?>
                        <?php if (!empty($hero['sous_titre'])): ?>
                            <span><?= $hero['sous_titre'] ?></span>
                        <?php endif; ?>
                    </h1>
                    <?php if (!empty($contenu_brut)): ?>
                        <p class="page-hero-sous-titre"><?= strip_tags($contenu_brut) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($hero['texte_bouton'])): ?>
                        <a href="<?= $hero['lien_bouton'] ?? '#' ?>" class="bouton-cta">
                            <?= $hero['texte_bouton'] ?> <i class="bi bi-arrow-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php
// ============================================
// SECTIONS TEXTE (multiples)
// ============================================
foreach ($textes as $texte):
    $options = $texte['options'] ?? [];
    $disposition = $options['disposition'] ?? 'simple';
    $a_image = !empty($texte['url_image']);
    $url_image = $a_image ? fixer_chemin_image($texte['url_image']) : null;
    $alignement_texte = $options['alignement_texte'] ?? 'text-center';
    $contenu_brut = $texte['contenu_texte'] ?? '';
    $contenu_images_fixees = fixer_images_contenu($contenu_brut);
    $couleur_fond = $options['couleur_fond'] ?? 'transparent';
?>

    <section class="section-texte <?= $texte['classe_personnalisee'] ?? '' ?>" style="background: <?= $couleur_fond ?>;">
        <div class="conteneur">
            <?php if ($disposition === 'avec-image-gauche' && $a_image): ?>
                <div class="row align-items-center g-5">
                    <div class="col-lg-6">
                        <div class="contenant-image">
                            <img src="<?= $url_image ?>" alt="<?= htmlspecialchars($texte['titre_section'] ?? 'Image') ?>" class="img-fluid">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="carte-personnalisee <?= $alignement_texte ?>">
                            <?php if (!empty($texte['titre_section'])): ?>
                                <span class="section-badge"><?= htmlspecialchars($texte['titre_section']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($texte['sous_titre'])): ?>
                                <h2 class="section-titre"><?= htmlspecialchars($texte['sous_titre']) ?></h2>
                            <?php endif; ?>
                            <div class="contenu-tinymce">
                                <?= $contenu_images_fixees ?>
                            </div>
                            <?php if (!empty($texte['texte_bouton']) && !empty($texte['lien_bouton'])): ?>
                                <a href="<?= htmlspecialchars($texte['lien_bouton']) ?>" class="bouton-primaire-personnalise mt-4 align-self-center">
                                    <?= htmlspecialchars($texte['texte_bouton']) ?> <i class="bi bi-arrow-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            <?php elseif ($disposition === 'avec-image-droite' && $a_image): ?>
                <div class="row align-items-center g-5">
                    <div class="col-lg-6">
                        <div class="carte-personnalisee <?= $alignement_texte ?>">
                            <?php if (!empty($texte['titre_section'])): ?>
                                <span class="section-badge"><?= htmlspecialchars($texte['titre_section']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($texte['sous_titre'])): ?>
                                <h2 class="section-titre"><?= htmlspecialchars($texte['sous_titre']) ?></h2>
                            <?php endif; ?>
                            <div class="contenu-tinymce">
                                <?= $contenu_images_fixees ?>
                            </div>
                            <?php if (!empty($texte['texte_bouton']) && !empty($texte['lien_bouton'])): ?>
                                <a href="<?= htmlspecialchars($texte['lien_bouton']) ?>" class="bouton-primaire-personnalise mt-4 align-self-center">
                                    <?= htmlspecialchars($texte['texte_bouton']) ?> <i class="bi bi-arrow-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="contenant-image">
                            <img src="<?= $url_image ?>" alt="<?= htmlspecialchars($texte['titre_section'] ?? 'Image') ?>" class="img-fluid">
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="carte-personnalisee <?= $alignement_texte ?>">
                            <?php if (!empty($texte['titre_section']) || !empty($texte['sous_titre'])): ?>
                                <div class="mb-4">
                                    <?php if (!empty($texte['titre_section'])): ?>
                                        <span class="section-badge"><?= htmlspecialchars($texte['titre_section']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($texte['sous_titre'])): ?>
                                        <h2 class="section-titre"><?= htmlspecialchars($texte['sous_titre']) ?></h2>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <div class="contenu-tinymce">
                                <?= $contenu_images_fixees ?>
                            </div>
                            <?php if (!empty($texte['texte_bouton']) && !empty($texte['lien_bouton'])): ?>
                                <div class="mt-5">
                                    <a href="<?= htmlspecialchars($texte['lien_bouton']) ?>" class="bouton-primaire-personnalise">
                                        <?= htmlspecialchars($texte['texte_bouton']) ?> <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

<?php endforeach; ?>

<!-- Conteneur principal -->
<div class="contenant-principal">
    <div class="carte-formulaire">

        <!-- Étapes de progression -->
        <div class="etapes-progression">
            <div class="etape active" data-etape="1" onclick="allerEtape(1)">
                <div class="numero-etape">1</div>
                <div class="texte-etape">Identité</div>
            </div>
            <div class="etape" data-etape="2" onclick="allerEtape(2)">
                <div class="numero-etape">2</div>
                <div class="texte-etape">Localisation</div>
            </div>
            <div class="etape" data-etape="3" onclick="allerEtape(3)">
                <div class="numero-etape">3</div>
                <div class="texte-etape">Régulation</div>
            </div>
            <div class="etape" data-etape="4" onclick="allerEtape(4)">
                <div class="numero-etape">4</div>
                <div class="texte-etape">Capacités</div>
            </div>
            <div class="etape" data-etape="5" onclick="allerEtape(5)">
                <div class="numero-etape">5</div>
                <div class="texte-etape">Finalisation</div>
            </div>
        </div>

        <!-- Formulaire -->
        <form id="formulaireCourtier" novalidate>
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>" id="csrfToken">

            <!-- Étape 1: Identité -->
            <div class="section-formulaire active" id="etape-1">
                <h2 class="titre-section">
                    <i class="fas fa-user-circle"></i>
                    Informations d'identité
                </h2>
                <p class="sous-titre-section">Veuillez fournir vos informations personnelles et professionnelles</p>

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="label-formulaire">
                            <i class="fas fa-user"></i>
                            Nom complet <span class="text-danger">*</span>
                            <i class="fas fa-info-circle icone-infobulle" data-bs-toggle="tooltip" title="Votre nom et prénom tels qu'ils apparaissent sur vos documents officiels"></i>
                        </label>
                        <div class="groupe-saisie">
                            <span class="groupe-saisie-texte"><i class="fas fa-user"></i></span>
                            <input type="text"
                                   class="controle-formulaire"
                                   name="nom_complet"
                                   id="nom_complet"
                                   placeholder="Jean Dupont"
                                   required
                                   maxlength="150">
                        </div>
                        <div class="retour-invalide" id="nom_complet-erreur"></div>
                        <div class="compteur-caracteres" id="nom_complet-compteur">0/150</div>
                    </div>

                    <div class="col-md-6">
                        <label class="label-formulaire">
                            <i class="fas fa-building"></i>
                            Nom de la société <span class="text-danger">*</span>
                        </label>
                        <div class="groupe-saisie">
                            <span class="groupe-saisie-texte"><i class="fas fa-building"></i></span>
                            <input type="text"
                                   class="controle-formulaire"
                                   name="nom_societe"
                                   id="nom_societe"
                                   placeholder="Votre Société SARL"
                                   required
                                   maxlength="200">
                        </div>
                        <div class="retour-invalide" id="nom_societe-erreur"></div>
                        <div class="compteur-caracteres" id="nom_societe-compteur">0/200</div>
                    </div>

                    <div class="col-md-6">
                        <label class="label-formulaire">
                            <i class="fas fa-briefcase"></i>
                            Juridiction d'incorporation
                        </label>
                        <div class="groupe-saisie">
                            <span class="groupe-saisie-texte"><i class="fas fa-gavel"></i></span>
                            <input type="text"
                                   class="controle-formulaire"
                                   name="juridiction_incorporation"
                                   id="juridiction_incorporation"
                                   placeholder="France, Luxembourg, etc."
                                   maxlength="150">
                        </div>
                        <div class="retour-invalide" id="juridiction_incorporation-erreur"></div>
                        <div class="compteur-caracteres" id="juridiction_incorporation-compteur">0/150</div>
                    </div>

                    <div class="col-md-6">
                        <label class="label-formulaire">
                            <i class="fas fa-hashtag"></i>
                            Numéro d'immatriculation
                        </label>
                        <div class="groupe-saisie">
                            <span class="groupe-saisie-texte"><i class="fas fa-hashtag"></i></span>
                            <input type="text"
                                   class="controle-formulaire"
                                   name="numero_immatriculation"
                                   id="numero_immatriculation"
                                   placeholder="RCS Paris B 123 456 789"
                                   maxlength="100">
                        </div>
                        <div class="retour-invalide" id="numero_immatriculation-erreur"></div>
                        <div class="compteur-caracteres" id="numero_immatriculation-compteur">0/100</div>
                    </div>

                    <div class="col-md-6">
                        <label class="label-formulaire">
                            <i class="fas fa-envelope"></i>
                            Courriel professionnel <span class="text-danger">*</span>
                        </label>
                        <div class="groupe-saisie">
                            <span class="groupe-saisie-texte"><i class="fas fa-envelope"></i></span>
                            <input type="email"
                                   class="controle-formulaire"
                                   name="courriel"
                                   id="courriel"
                                   placeholder="contact@votresociete.com"
                                   required
                                   maxlength="150">
                        </div>
                        <div class="retour-invalide" id="courriel-erreur"></div>
                    </div>

                    <div class="col-md-3">
                        <label class="label-formulaire">
                            <i class="fas fa-phone"></i>
                            Téléphone mobile
                        </label>
                        <div class="groupe-saisie">
                            <span class="groupe-saisie-texte"><i class="fas fa-phone"></i></span>
                            <input type="tel"
                                   class="controle-formulaire"
                                   name="telephone_mobile"
                                   id="telephone_mobile"
                                   placeholder="+33 6 12 34 56 78"
                                   maxlength="50">
                        </div>
                        <div class="retour-invalide" id="telephone_mobile-erreur"></div>
                        <div class="compteur-caracteres" id="telephone_mobile-compteur">0/50</div>
                    </div>

                    <div class="col-md-3">
                        <label class="label-formulaire">
                            <i class="fab fa-whatsapp"></i>
                            WhatsApp
                        </label>
                        <div class="groupe-saisie">
                            <span class="groupe-saisie-texte"><i class="fab fa-whatsapp" style="color: #25D366;"></i></span>
                            <input type="tel"
                                   class="controle-formulaire"
                                   name="whatsapp"
                                   id="whatsapp"
                                   placeholder="+33 6 12 34 56 78"
                                   maxlength="50">
                        </div>
                        <div class="retour-invalide" id="whatsapp-erreur"></div>
                        <div class="compteur-caracteres" id="whatsapp-compteur">0/50</div>
                    </div>

                    <div class="col-12">
                        <label class="label-formulaire">
                            <i class="fas fa-globe"></i>
                            Site web de la société
                        </label>
                        <div class="groupe-saisie">
                            <span class="groupe-saisie-texte"><i class="fas fa-globe"></i></span>
                            <input type="url"
                                   class="controle-formulaire"
                                   name="site_web_societe"
                                   id="site_web_societe"
                                   placeholder="https://www.votresociete.com"
                                   maxlength="200">
                        </div>
                        <div class="retour-invalide" id="site_web_societe-erreur"></div>
                        <div class="compteur-caracteres" id="site_web_societe-compteur">0/200</div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-5">
                    <button type="button" class="btn btn-nav btn-suivant" onclick="validerEtape(1, 2)">
                        Continuer <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Étape 2: Localisation -->
            <div class="section-formulaire" id="etape-2">
                <h2 class="titre-section">
                    <i class="fas fa-map-marked-alt" style="color: var(--info);"></i>
                    Localisation
                </h2>
                <p class="sous-titre-section">Indiquez votre pays d'exercice</p>

                <div class="row g-4">
                    <div class="col-md-12">
                        <label class="label-formulaire">
                            <i class="fas fa-map-marker-alt"></i>
                            Pays <span class="text-danger">*</span>
                        </label>
                        <div class="contenant-recherche-pays">
                            <div class="groupe-saisie">
                                <span class="groupe-saisie-texte"><i class="fas fa-map-marker-alt"></i></span>
                                <input type="text"
                                       class="controle-formulaire recherche-pays"
                                       id="pays_recherche"
                                       placeholder="Rechercher un pays..."
                                       data-cible="pays"
                                       autocomplete="off"
                                       required>
                            </div>
                            <div class="liste-deroulante-pays" id="pays_liste"></div>
                            <input type="hidden" name="id_pays" id="pays_id">
                        </div>
                        <div class="retour-invalide" id="pays-erreur"></div>
                        <div id="pays_selectionne" class="badge-pays-selectionne" style="display: none;">
                            <i class="fas fa-check-circle" style="color: var(--accent);"></i>
                            <span id="pays_nom"></span>
                            <button type="button" class="btn btn-link btn-sm p-0 ms-2" onclick="effacerPays('pays')">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="contenant-carte">
                    <div id="carte"></div>
                </div>

                <div class="d-flex justify-content-between mt-5">
                    <button type="button" class="btn btn-nav btn-precedent" onclick="allerEtape(1)">
                        <i class="fas fa-arrow-left"></i> Précédent
                    </button>
                    <button type="button" class="btn btn-nav btn-suivant" onclick="validerEtape(2, 3)">
                        Continuer <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Étape 3: Régulation -->
            <div class="section-formulaire" id="etape-3">
                <h2 class="titre-section">
                    <i class="fas fa-shield-alt" style="color: var(--violet);"></i>
                    Informations réglementaires
                </h2>
                <p class="sous-titre-section">Informations sur votre statut réglementaire</p>

                <div class="boite-info">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>Important :</strong> Ces informations nous aident à déterminer les exigences de conformité applicables à notre partenariat.
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="label-formulaire">Statut réglementaire</label>
                        <select name="statut_reglementaire" class="selection-formulaire" id="statut_reglementaire">
                            <option value="">Sélectionnez une option</option>
                            <option value="Licenced">Agréé / Licencié</option>
                            <option value="Exempt">Exempté</option>
                            <option value="Unlicensed">Non agréé</option>
                        </select>
                        <div class="retour-invalide" id="statut_reglementaire-erreur"></div>
                    </div>

                    <div class="col-md-6">
                        <label class="label-formulaire">Autorité de régulation</label>
                        <input type="text"
                               class="controle-formulaire"
                               name="autorite_reglementation"
                               id="autorite_reglementation"
                               placeholder="AMF, FCA, SEC, etc."
                               maxlength="150">
                        <div class="retour-invalide" id="autorite_reglementation-erreur"></div>
                        <div class="compteur-caracteres" id="autorite_reglementation-compteur">0/150</div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-5">
                    <button type="button" class="btn btn-nav btn-precedent" onclick="allerEtape(2)">
                        <i class="fas fa-arrow-left"></i> Précédent
                    </button>
                    <button type="button" class="btn btn-nav btn-suivant" onclick="validerEtape(3, 4)">
                        Continuer <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Étape 4: Capacités -->
            <div class="section-formulaire" id="etape-4">
                <h2 class="titre-section">
                    <i class="fas fa-briefcase" style="color: var(--avertissement);"></i>
                    Capacités et expertises
                </h2>
                <p class="sous-titre-section">Décrivez vos domaines d'expertise et types d'investisseurs</p>

                <div class="mb-4">
                    <label class="label-formulaire d-block mb-3">
                        <i class="fas fa-check-circle"></i>
                        Vos capacités <span class="text-danger">*</span>
                    </label>
                    <div class="grille-coches" id="groupeCapacite">
                        <?php
                        $capacites = [
                            'capacite_courtier_investissement' => ['label' => 'Courtier en investissement', 'icone' => 'fa-chart-line', 'couleur' => '#0B4F2E', 'desc' => 'Intermédiation et conseil en investissement'],
                            'capacite_agent_placement' => ['label' => 'Agent de placement', 'icone' => 'fa-handshake', 'couleur' => '#FF6B35', 'desc' => 'Placement de fonds et titres'],
                            'capacite_conseiller_finances_entreprise' => ['label' => 'Conseiller en finance d\'entreprise', 'icone' => 'fa-user-tie', 'couleur' => '#3498DB', 'desc' => 'Fusions-acquisitions, levée de fonds'],
                            'capacite_gestionnaire_fonds' => ['label' => 'Gestionnaire de fonds', 'icone' => 'fa-university', 'couleur' => '#9B59B6', 'desc' => 'Gestion de portefeuilles et fonds'],
                            'capacite_representant_family_office' => ['label' => 'Représentant family office', 'icone' => 'fa-home', 'couleur' => '#FFD700', 'desc' => 'Gestion de patrimoine familial'],
                            'capacite_conseiller_esg' => ['label' => 'Conseiller ESG', 'icone' => 'fa-leaf', 'couleur' => '#27ae60', 'desc' => 'Investissement durable et responsable'],
                            'capacite_introducteur_independant' => ['label' => 'Introducteur indépendant', 'icone' => 'fa-user', 'couleur' => '#94a3b8', 'desc' => 'Mise en relation d\'affaires']
                        ];
                        foreach ($capacites as $cle => $cap):
                        ?>
                        <div class="carte-coche" onclick="basculerCoche(this, 'capacite')">
                            <input type="checkbox" name="<?= $cle ?>" value="1">
                            <div class="icone-coche" style="background: <?= $cap['couleur'] ?>20; color: <?= $cap['couleur'] ?>;">
                                <i class="fas <?= $cap['icone'] ?>"></i>
                            </div>
                            <div class="fw-semibold"><?= htmlspecialchars($cap['label']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($cap['desc']) ?></small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="retour-invalide erreur-capacite" style="display: none;">Veuillez sélectionner au moins une capacité</div>
                </div>

                <div class="mb-4">
                    <label class="label-formulaire">Autre capacité (précisez)</label>
                    <input type="text"
                           class="controle-formulaire"
                           name="capacite_autre"
                           id="capacite_autre"
                           placeholder="Décrivez toute autre capacité pertinente"
                           maxlength="255">
                    <div class="compteur-caracteres" id="capacite_autre-compteur">0/255</div>
                </div>

                <div class="mb-4">
                    <label class="label-formulaire d-block mb-3">
                        <i class="fas fa-users"></i>
                        Types d'investisseurs que vous représentez
                    </label>
                    <div class="grille-coches">
                        <?php
                        $investisseurs = [
                            'investisseur_capital_investissement' => ['label' => 'Capital investissement', 'icone' => 'fa-building', 'couleur' => '#e74c3c'],
                            'investisseur_capital_risque' => ['label' => 'Capital risque', 'icone' => 'fa-rocket', 'couleur' => '#9b59b6'],
                            'investisseur_esg_impact' => ['label' => 'ESG / Impact', 'icone' => 'fa-leaf', 'couleur' => '#27ae60'],
                            'investisseur_financement_developpement' => ['label' => 'Financement du développement', 'icone' => 'fa-globe', 'couleur' => '#3498db'],
                            'investisseur_institutionnel' => ['label' => 'Institutionnel', 'icone' => 'fa-landmark', 'couleur' => '#f39c12'],
                            'investisseur_grande_fortune' => ['label' => 'Haute fortune (HNWI)', 'icone' => 'fa-gem', 'couleur' => '#1abc9c'],
                            'investisseur_souverain' => ['label' => 'Souverain / Fonds d\'État', 'icone' => 'fa-crown', 'couleur' => '#e67e22']
                        ];
                        foreach ($investisseurs as $cle => $inv):
                        ?>
                        <div class="carte-coche" onclick="basculerCoche(this, 'investisseur')">
                            <input type="checkbox" name="<?= $cle ?>" value="1">
                            <div class="icone-coche mx-auto" style="background: <?= $inv['couleur'] ?>20; color: <?= $inv['couleur'] ?>;">
                                <i class="fas <?= $inv['icone'] ?>"></i>
                            </div>
                            <div class="small fw-semibold"><?= htmlspecialchars($inv['label']) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="label-formulaire">Taille de ticket typique</label>
                        <input type="text"
                               class="controle-formulaire"
                               name="taille_billet_typique"
                               id="taille_billet_typique"
                               placeholder="1M€ - 10M€"
                               maxlength="150">
                        <div class="compteur-caracteres" id="taille_billet_typique-compteur">0/150</div>
                    </div>

                    <div class="col-md-6">
                        <label class="label-formulaire">Couverture géographique</label>
                        <input type="text"
                               class="controle-formulaire"
                               name="couverture_geographique"
                               id="couverture_geographique"
                               placeholder="Afrique de l'Ouest, Europe, etc."
                               maxlength="65535">
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-5">
                    <button type="button" class="btn btn-nav btn-precedent" onclick="allerEtape(3)">
                        <i class="fas fa-arrow-left"></i> Précédent
                    </button>
                    <button type="button" class="btn btn-nav btn-suivant" onclick="validerEtape(4, 5)">
                        Continuer <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Étape 5: Finalisation -->
            <div class="section-formulaire" id="etape-5">
                <h2 class="titre-section">
                    <i class="fas fa-check-circle" style="color: var(--accent);"></i>
                    Finalisation
                </h2>
                <p class="sous-titre-section">Vérifiez vos informations et complétez votre candidature</p>

                <div id="resume" class="mb-4"></div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="label-formulaire">Modèle d'engagement préféré</label>
                        <select name="modele_engagement" class="selection-formulaire" id="modele_engagement">
                            <option value="">Sélectionnez une option</option>
                            <option value="Success Commission">Commission sur succès</option>
                            <option value="Retainer + Success Fee">Rétribution + Commission</option>
                            <option value="Referral Arrangement">Accord de parrainage</option>
                            <option value="To be negotiated">À négocier</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="label-formulaire">Types de mandat recherchés</label>
                        <div class="grille-coches" style="grid-template-columns: repeat(2, 1fr);">
                            <?php
                            $mandats = [
                                'mandat_capitaux_propres' => 'Capitaux propres',
                                'mandat_dette_structuree' => 'Dette structurée',
                                'mandat_financement_mixte' => 'Financement mixte',
                                'mandat_subvention' => 'Subvention',
                                'mandat_partenariat_strategique' => 'Partenariat stratégique',
                                'mandat_programme_complet' => 'Programme complet'
                            ];
                            foreach ($mandats as $cle => $label):
                            ?>
                            <div class="carte-coche" onclick="basculerCoche(this, 'mandat')" style="padding: 0.5rem;">
                                <input type="checkbox" name="<?= $cle ?>" value="1">
                                <span class="small"><?= htmlspecialchars($label) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="mb-4 p-3 border rounded-3 bg-white">
                    <h6 class="mb-3" style="color: var(--primaire);">
                        <i class="fas fa-shield-alt me-2"></i>Déclarations de conformité
                    </h6>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="confirme_autorise" id="confirme_autorise" value="1" required>
                        <label class="form-check-label" for="confirme_autorise">
                            <strong>Je confirme être autorisé à représenter mon entreprise</strong>
                        </label>
                        <div class="retour-invalide" id="confirme_autorise-erreur"></div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="confirme_aml_kyc" id="confirme_aml_kyc" value="1" required>
                        <label class="form-check-label" for="confirme_aml_kyc">
                            <strong>Je confirme être conforme aux exigences AML/KYC</strong>
                        </label>
                        <div class="retour-invalide" id="confirme_aml_kyc-erreur"></div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="reconnait_non_exclusivite" id="reconnait_non_exclusivite" value="1" required>
                        <label class="form-check-label" for="reconnait_non_exclusivite">
                            <strong>Je reconnais le caractère non exclusif de ce partenariat</strong>
                        </label>
                        <div class="retour-invalide" id="reconnait_non_exclusivite-erreur"></div>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="comprend_mandat_formel_requis" id="comprend_mandat_formel_requis" value="1" required>
                        <label class="form-check-label" for="comprend_mandat_formel_requis">
                            <strong>Je comprends qu'un mandat formel sera requis</strong>
                        </label>
                        <div class="retour-invalide" id="comprend_mandat_formel_requis-erreur"></div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-5">
                    <button type="button" class="btn btn-nav btn-precedent" onclick="allerEtape(4)">
                        <i class="fas fa-arrow-left"></i> Précédent
                    </button>
                    <button type="submit" class="btn btn-nav btn-soumettre" id="boutonSoumettre">
                        <span class="texte-bouton">
                            <i class="fas fa-paper-plane me-2"></i>Soumettre ma candidature
                        </span>
                    </button>
                </div>
            </div>

        </form>
    </div>

    <div class="lien-statut">
        <p class="text-muted">
            Déjà enregistré ?
            <a href="<?= base_url('courtiers/statut') ?>">
                Vérifier mon statut <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </p>
    </div>
</div>

<!-- Modal Conditions -->
<div class="modal fade" id="modalConditions" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Conditions d'enregistrement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Acceptation des conditions</h6>
                <p>En soumettant ce formulaire, j'accepte que les informations fournies soient utilisées pour évaluer ma candidature.</p>
                <h6>Exactitude des informations</h6>
                <p>Je certifie que toutes les informations fournies sont exactes et complètes à ma connaissance.</p>
                <h6>Utilisation des données</h6>
                <p>Mes données seront traitées conformément à la politique de confidentialité d'AGF Phytomed.</p>
                <h6>Confidentialité</h6>
                <p>Je comprends que les informations partagées seront traitées de manière confidentielle.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">J'ai compris</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Succès -->
<div class="modal fade modal-succes" id="modalSucces" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>Candidature envoyée
                </h5>
            </div>
            <div class="modal-body">
                <div class="success-animation">
                    <i class="fas fa-handshake"></i>
                </div>
                <h3>Merci <span id="prenomSucces">!</span></h3>
                <p class="text-muted">Nous avons bien reçu votre candidature. Notre équipe vous contactera dans les plus brefs délais.</p>
                <button type="button" class="btn btn-primary mt-3" onclick="fermerModalSucces()" style="background: var(--primaire); border: none; padding: 0.75rem 2rem; border-radius: 12px;">
                    Fermer
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // ==================== VARIABLES GLOBALES ====================
    let etapeCourante = 1;
    const totalEtapes = 5;
    let carte, marqueur;
    const pays = <?= json_encode($pays) ?>;
    const siegeSocial = { lat: 5.345317, lng: -4.008429 };

    // ==================== INITIALISATION ====================
    document.addEventListener('DOMContentLoaded', function() {
        initialiserInfobulles();
        initialiserRecherchePays();
        initialiserCarte();
        initialiserValidation();
        initialiserCompteurs();
    });

    function initialiserInfobulles() {
        const infobulles = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        infobulles.forEach(el => new bootstrap.Tooltip(el));
    }

    // ==================== INITIALISATION DE LA CARTE ====================
    function initialiserCarte() {
        carte = L.map('carte').setView([siegeSocial.lat, siegeSocial.lng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributeurs'
        }).addTo(carte);

        const iconePersonnalisee = L.divIcon({
            className: 'marqueur-personnalise',
            html: '<i class="fas fa-map-marker-alt" style="color: #0B4F2E; font-size: 30px;"></i>',
            iconSize: [30, 30],
            iconAnchor: [15, 30]
        });

        marqueur = L.marker([siegeSocial.lat, siegeSocial.lng], { icon: iconePersonnalisee })
            .addTo(carte)
            .bindPopup(`<b>African Green Farmers</b><br>Muyinga, Burundi`)
            .openPopup();

        L.circle([siegeSocial.lat, siegeSocial.lng], {
            color: '#0B4F2E',
            fillColor: '#0B4F2E',
            fillOpacity: 0.1,
            radius: 500
        }).addTo(carte);
    }

    // ==================== RECHERCHE DE PAYS ====================
    function initialiserRecherchePays() {
        const recherches = document.querySelectorAll('.recherche-pays');
        recherches.forEach(recherche => {
            const cible = recherche.dataset.cible;
            const liste = document.getElementById(`${cible}_liste`);
            liste.innerHTML = pays.map(pays => `
                <div class="option-pays"
                     data-id="${pays.id}"
                     data-name="${pays.pays || pays.name}"
                     onclick="selectionnerPays('${cible}', ${pays.id}, '${pays.pays || pays.name}')">
                    <i class="fas fa-map-marker-alt me-2" style="color: var(--info);"></i>
                    ${pays.pays || pays.name}
                </div>
            `).join('');

            recherche.addEventListener('input', function() {
                const requete = this.value.toLowerCase();
                const options = liste.querySelectorAll('.option-pays');
                let aResultats = false;
                options.forEach(opt => {
                    const nom = opt.dataset.name.toLowerCase();
                    if (nom.includes(requete) || requete === '') {
                        opt.style.display = 'block';
                        aResultats = true;
                    } else {
                        opt.style.display = 'none';
                    }
                });
                liste.classList.toggle('afficher', aResultats && this.value.length > 0);
            });

            recherche.addEventListener('focus', function() {
                if (this.value.length > 0) liste.classList.add('afficher');
            });

            document.addEventListener('click', function(e) {
                if (!e.target.closest('.contenant-recherche-pays')) liste.classList.remove('afficher');
            });
        });
    }

    window.selectionnerPays = function(type, id, nom) {
        document.getElementById(`${type}_recherche`).value = nom;
        document.getElementById(`${type}_id`).value = id;
        document.getElementById(`${type}_nom`).textContent = nom;
        document.getElementById(`${type}_selectionne`).style.display = 'inline-flex';
        document.getElementById(`${type}_liste`).classList.remove('afficher');
        document.getElementById(`${type}_recherche`).classList.remove('invalide');
        document.getElementById(`${type}-erreur`).style.display = 'none';
    };

    window.effacerPays = function(type) {
        document.getElementById(`${type}_recherche`).value = '';
        document.getElementById(`${type}_id`).value = '';
        document.getElementById(`${type}_selectionne`).style.display = 'none';
        document.getElementById(`${type}_recherche`).focus();
    };

    // ==================== COMPTEURS DE CARACTÈRES ====================
    function initialiserCompteurs() {
        const champs = ['nom_complet', 'nom_societe', 'juridiction_incorporation', 'numero_immatriculation', 'telephone_mobile', 'whatsapp', 'site_web_societe', 'autorite_reglementation', 'capacite_autre', 'taille_billet_typique', 'couverture_geographique'];
        champs.forEach(id => {
            const champ = document.getElementById(id);
            if (champ) {
                const compteur = document.getElementById(id + '-compteur');
                if (compteur) {
                    mettreAJourCompteur(champ, compteur);
                    champ.addEventListener('input', () => mettreAJourCompteur(champ, compteur));
                }
            }
        });
    }

    function mettreAJourCompteur(champ, compteur) {
        const compte = champ.value.length;
        const max = champ.maxLength;
        compteur.textContent = `${compte}/${max}`;
        compteur.classList.toggle('avertissement', compte > max * 0.8);
        compteur.classList.toggle('danger', compte >= max);
    }

    // ==================== CARTES CASES À COCHER ====================
    window.basculerCoche = function(carte, groupe) {
        const checkbox = carte.querySelector('input[type="checkbox"]');
        checkbox.checked = !checkbox.checked;
        carte.classList.toggle('selectionnee', checkbox.checked);
        if (groupe === 'capacite') verifierCapacite();
    };

    function verifierCapacite() {
        const champsCapacite = ['capacite_courtier_investissement', 'capacite_agent_placement', 'capacite_conseiller_finances_entreprise', 'capacite_gestionnaire_fonds', 'capacite_representant_family_office', 'capacite_conseiller_esg', 'capacite_introducteur_independant'];
        let aCapacite = false;
        for (let champ of champsCapacite) {
            if (document.querySelector(`input[name="${champ}"]`)?.checked) {
                aCapacite = true;
                break;
            }
        }
        if (aCapacite) document.querySelector('.erreur-capacite').style.display = 'none';
    }

    // ==================== VALIDATION ====================
    const reglesValidation = {
        nom_complet: { required: true, min: 3, max: 150 },
        nom_societe: { required: true, min: 2, max: 200 },
        courriel: { required: true, type: 'email', max: 150 },
        telephone_mobile: { pattern: /^[0-9+\-\s]+$/, max: 50 },
        whatsapp: { pattern: /^[0-9+\-\s]+$/, max: 50 },
        site_web_societe: { type: 'url', max: 200 },
        numero_immatriculation: { max: 100 },
        autorite_reglementation: { max: 150 },
        juridiction_incorporation: { max: 150 },
        taille_billet_typique: { max: 150 }
    };

    function validerChamp(idChamp) {
        const champ = document.getElementById(idChamp);
        if (!champ) return true;
        const valeur = champ.value.trim();
        const regles = reglesValidation[idChamp];
        champ.classList.remove('valide', 'invalide');
        if (!regles) return true;
        if (regles.required && !valeur) {
            afficherErreurChamp(champ, idChamp + '-erreur', 'Ce champ est requis');
            return false;
        }
        if (valeur) {
            if (regles.min && valeur.length < regles.min) {
                afficherErreurChamp(champ, idChamp + '-erreur', `Minimum ${regles.min} caractères`);
                return false;
            }
            if (regles.max && valeur.length > regles.max) {
                afficherErreurChamp(champ, idChamp + '-erreur', `Maximum ${regles.max} caractères`);
                return false;
            }
            if (regles.pattern && !regles.pattern.test(valeur)) {
                afficherErreurChamp(champ, idChamp + '-erreur', 'Format invalide');
                return false;
            }
            if (regles.type === 'email') {
                const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!regexEmail.test(valeur)) {
                    afficherErreurChamp(champ, idChamp + '-erreur', 'Courriel invalide');
                    return false;
                }
            }
            if (regles.type === 'url') {
                try { new URL(valeur); } catch {
                    afficherErreurChamp(champ, idChamp + '-erreur', 'URL invalide');
                    return false;
                }
            }
        }
        champ.classList.add('valide');
        cacherErreurChamp(champ, idChamp + '-erreur');
        return true;
    }

    function afficherErreurChamp(champ, idErreur, message) {
        champ.classList.add('invalide');
        const erreurEl = document.getElementById(idErreur);
        if (erreurEl) { erreurEl.textContent = message; erreurEl.style.display = 'block'; }
    }

    function cacherErreurChamp(champ, idErreur) {
        const erreurEl = document.getElementById(idErreur);
        if (erreurEl) erreurEl.style.display = 'none';
    }

    function initialiserValidation() {
        Object.keys(reglesValidation).forEach(idChamp => {
            const champ = document.getElementById(idChamp);
            if (champ) {
                champ.addEventListener('blur', () => validerChamp(idChamp));
                champ.addEventListener('input', () => { if (champ.classList.contains('invalide')) validerChamp(idChamp); });
            }
        });
    }

    // ==================== VALIDATION DES ÉTAPES ====================
    window.validerEtape = function(etape, etapeSuivante) {
        let estValide = true;
        switch(etape) {
            case 1: estValide = validerEtape1(); break;
            case 2: estValide = validerEtape2(); break;
            case 3: estValide = validerEtape3(); break;
            case 4: estValide = validerEtape4(); break;
            case 5: estValide = validerEtape5(); break;
        }
        if (estValide) {
            allerEtape(etapeSuivante);
        } else {
            document.getElementById(`etape-${etape}`).classList.add('secousse');
            setTimeout(() => document.getElementById(`etape-${etape}`).classList.remove('secousse'), 500);
            afficherToast('erreur', 'Erreur de validation', 'Veuillez corriger les erreurs avant de continuer');
        }
    };

    function validerEtape1() {
        let estValide = true;
        if (!validerChamp('nom_complet')) estValide = false;
        if (!validerChamp('nom_societe')) estValide = false;
        if (!validerChamp('courriel')) estValide = false;
        if (!validerChamp('telephone_mobile')) estValide = false;
        if (!validerChamp('whatsapp')) estValide = false;
        if (!validerChamp('site_web_societe')) estValide = false;
        return estValide;
    }

    function validerEtape2() {
        let estValide = true;
        if (!document.getElementById('pays_id').value) {
            document.getElementById('pays_recherche').classList.add('invalide');
            document.getElementById('pays-erreur').textContent = 'Veuillez sélectionner votre pays';
            document.getElementById('pays-erreur').style.display = 'block';
            estValide = false;
        }
        return estValide;
    }

    function validerEtape3() { return true; }

    function validerEtape4() {
        let estValide = true;
        const champsCapacite = ['capacite_courtier_investissement', 'capacite_agent_placement', 'capacite_conseiller_finances_entreprise', 'capacite_gestionnaire_fonds', 'capacite_representant_family_office', 'capacite_conseiller_esg', 'capacite_introducteur_independant'];
        let aCapacite = false;
        for (let champ of champsCapacite) {
            if (document.querySelector(`input[name="${champ}"]`)?.checked) { aCapacite = true; break; }
        }
        if (!aCapacite) {
            document.querySelector('.erreur-capacite').style.display = 'block';
            estValide = false;
        } else {
            document.querySelector('.erreur-capacite').style.display = 'none';
        }
        return estValide;
    }

    function validerEtape5() {
        let estValide = true;
        const coches = ['confirme_autorise', 'confirme_aml_kyc', 'reconnait_non_exclusivite', 'comprend_mandat_formel_requis'];
        for (let coche of coches) {
            const el = document.getElementById(coche);
            if (!el.checked) {
                el.classList.add('invalide');
                document.getElementById(coche + '-erreur').style.display = 'block';
                estValide = false;
            }
        }
        return estValide;
    }

    // ==================== NAVIGATION ====================
    window.allerEtape = function(etape) {
        if (etape < 1 || etape > totalEtapes) return;
        document.querySelectorAll('.etape').forEach(el => el.classList.remove('active', 'completee'));
        for (let i = 1; i < etape; i++) document.querySelector(`.etape[data-etape="${i}"]`).classList.add('completee');
        document.querySelector(`.etape[data-etape="${etape}"]`).classList.add('active');
        document.querySelectorAll('.section-formulaire').forEach(el => el.classList.remove('active'));
        document.getElementById(`etape-${etape}`).classList.add('active');
        etapeCourante = etape;
        if (etape === 5) genererResume();
        document.querySelector('.carte-formulaire').scrollIntoView({ behavior: 'smooth' });
    };

    // ==================== GÉNÉRATION DU RÉSUMÉ ====================
    function genererResume() {
        const resume = document.getElementById('resume');
        const nomComplet = document.getElementById('nom_complet').value || 'Non fourni';
        const courriel = document.getElementById('courriel').value || 'Non fourni';
        const nomSociete = document.getElementById('nom_societe').value || 'Non fourni';
        const paysNom = document.getElementById('pays_nom').textContent || 'Non sélectionné';
        const capacites = [];
        if (document.querySelector('input[name="capacite_courtier_investissement"]')?.checked) capacites.push('Courtier en investissement');
        if (document.querySelector('input[name="capacite_agent_placement"]')?.checked) capacites.push('Agent de placement');
        if (document.querySelector('input[name="capacite_conseiller_finances_entreprise"]')?.checked) capacites.push('Conseiller en finance d\'entreprise');
        if (document.querySelector('input[name="capacite_gestionnaire_fonds"]')?.checked) capacites.push('Gestionnaire de fonds');
        if (document.querySelector('input[name="capacite_representant_family_office"]')?.checked) capacites.push('Représentant family office');
        if (document.querySelector('input[name="capacite_conseiller_esg"]')?.checked) capacites.push('Conseiller ESG');
        if (document.querySelector('input[name="capacite_introducteur_independant"]')?.checked) capacites.push('Introducteur indépendant');
        const capaciteAutre = document.getElementById('capacite_autre').value;
        if (capaciteAutre) capacites.push('Autre : ' + capaciteAutre);
        const statutReglementaire = document.getElementById('statut_reglementaire').value || 'Non spécifié';
        const modeleEngagement = document.getElementById('modele_engagement').value || 'Non spécifié';
        let html = `
            <div class="section-resume"><h6><i class="fas fa-user-circle"></i> Identité</h6>
            <div class="element-resume"><span class="label-resume">Nom complet</span><span class="valeur-resume">${echapperHtml(nomComplet)}</span></div>
            <div class="element-resume"><span class="label-resume">Nom de la société</span><span class="valeur-resume">${echapperHtml(nomSociete)}</span></div>
            <div class="element-resume"><span class="label-resume">Courriel</span><span class="valeur-resume">${echapperHtml(courriel)}</span></div></div>
            <div class="section-resume"><h6><i class="fas fa-map-marked-alt"></i> Localisation</h6>
            <div class="element-resume"><span class="label-resume">Pays</span><span class="valeur-resume">${echapperHtml(paysNom)}</span></div></div>
            <div class="section-resume"><h6><i class="fas fa-shield-alt"></i> Régulation</h6>
            <div class="element-resume"><span class="label-resume">Statut réglementaire</span><span class="valeur-resume">${echapperHtml(statutReglementaire)}</span></div></div>`;
        if (capacites.length > 0) html += `<div class="section-resume"><h6><i class="fas fa-briefcase"></i> Capacités</h6><div>${capacites.map(cap => `<span class="badge-personnalise">${echapperHtml(cap)}</span>`).join('')}</div></div>`;
        html += `<div class="section-resume"><h6><i class="fas fa-handshake"></i> Engagement</h6>
            <div class="element-resume"><span class="label-resume">Modèle d'engagement</span><span class="valeur-resume">${echapperHtml(modeleEngagement)}</span></div></div>`;
        resume.innerHTML = html;
    }

    function echapperHtml(texte) { const div = document.createElement('div'); div.textContent = texte; return div.innerHTML; }

    // ==================== NOTIFICATIONS TOAST ====================
    function afficherToast(type, titre, message, duree = 5000) {
        let conteneur = document.getElementById('conteneurToast');
        if (!conteneur) { conteneur = document.createElement('div'); conteneur.id = 'conteneurToast'; conteneur.className = 'conteneur-toast'; document.body.appendChild(conteneur); }
        const toast = document.createElement('div'); toast.className = `toast-personnalise ${type}`;
        const icones = { succes: 'fa-check-circle', erreur: 'fa-exclamation-circle', avertissement: 'fa-exclamation-triangle', info: 'fa-info-circle' };
        const couleurs = { succes: '#27ae60', erreur: '#E74C3C', avertissement: '#FF6B35', info: '#3498DB' };
        toast.innerHTML = `<div class="icone-toast" style="background: ${couleurs[type]}20; color: ${couleurs[type]};"><i class="fas ${icones[type] || icones.info}"></i></div>
            <div class="contenu-toast"><h4>${echapperHtml(titre)}</h4><p>${echapperHtml(message)}</p></div>
            <button class="fermer-toast" onclick="supprimerToast(this.parentElement)"><i class="fas fa-times"></i></button>`;
        conteneur.appendChild(toast);
        const suppressionAuto = setTimeout(() => supprimerToast(toast), duree);
        toast.addEventListener('mouseenter', () => clearTimeout(suppressionAuto));
        toast.addEventListener('mouseleave', () => setTimeout(() => supprimerToast(toast), 1000));
    }

    function supprimerToast(toast) { if (!toast || !toast.parentElement) return; toast.classList.add('disparait'); setTimeout(() => { if (toast.parentElement) toast.remove(); }, 300); }

    // ==================== SOUMISSION ====================
    document.getElementById('formulaireCourtier').addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!validerEtape5()) { 
            afficherToast('erreur', 'Erreur de validation', 'Veuillez accepter toutes les conditions'); 
            return; 
        }
        
        const boutonSoumettre = document.getElementById('boutonSoumettre'); 
        boutonSoumettre.classList.add('chargement'); 
        boutonSoumettre.disabled = true;
        
        const donneesFormulaire = new FormData(this); 
        const donnees = Object.fromEntries(donneesFormulaire.entries());
        
        const urlSoumission = '<?= base_url('Courtiers-formulaire') ?>';
        
        try {
            const reponse = await fetch(urlSoumission, { 
                method: 'POST', 
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-Requested-With': 'XMLHttpRequest' 
                }, 
                body: JSON.stringify(donnees) 
            });
            
            const resultat = await reponse.json();
            
            if (resultat.succes) {
                document.getElementById('prenomSucces').textContent = donnees.nom_complet.split(' ')[0] + ' !';
                const modal = new bootstrap.Modal(document.getElementById('modalSucces')); 
                modal.show();
                this.reset(); 
                reinitialiserFormulaire(); 
                afficherToast('succes', 'Succès', resultat.message);
                setTimeout(() => { 
                    allerEtape(1); 
                    modal.hide(); 
                }, 3000);
            } else {
                if (resultat.erreurs) {
                    Object.keys(resultat.erreurs).forEach(champ => { 
                        const el = document.getElementById(champ); 
                        if (el) { 
                            el.classList.add('invalide'); 
                            const retour = document.getElementById(champ + '-erreur'); 
                            if (retour) { 
                                retour.textContent = resultat.erreurs[champ]; 
                                retour.style.display = 'block'; 
                            } 
                        } 
                    });
                }
                afficherToast('erreur', 'Erreur', resultat.message || 'Une erreur est survenue');
            }
        } catch (erreur) { 
            console.error('Erreur:', erreur); 
            afficherToast('erreur', 'Erreur', 'Erreur de connexion au serveur'); 
        } finally { 
            boutonSoumettre.classList.remove('chargement'); 
            boutonSoumettre.disabled = false; 
        }
    });

    function reinitialiserFormulaire() {
        document.querySelectorAll('.selectionnee').forEach(el => el.classList.remove('selectionnee'));
        document.querySelectorAll('.valide').forEach(el => el.classList.remove('valide'));
        document.querySelectorAll('.invalide').forEach(el => el.classList.remove('invalide'));
        effacerPays('pays');
        document.querySelectorAll('.compteur-caracteres').forEach(el => { 
            const id = el.id.replace('-compteur', ''); 
            const champ = document.getElementById(id); 
            if (champ && champ.maxLength) el.textContent = `0/${champ.maxLength}`; 
        });
    }

    function fermerModalSucces() { 
        const modalEl = document.getElementById('modalSucces'); 
        const modal = bootstrap.Modal.getInstance(modalEl); 
        modal.hide(); 
    }
</script>

<?php include VIEWPATH . 'includes/frontend/PiedDePage.php'; ?>