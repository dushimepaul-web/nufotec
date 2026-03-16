<?php
// application/helpers/sections_helper.php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Rendu complet d'une section selon son template
 */
function render_section($section, $CI = null)
{
    if (!$CI) $CI =& get_instance();
    
    $template = $section['custom_class'] ?? $section['type_section'];
    $data = [
        'section' => $section,
        'options' => json_decode($section['options_json'] ?? '{}', true)
    ];
    
    // Charger les données liées si nécessaire
    switch ($template) {
        case 'temoignages':
        case 'temoignages_carousel':
            $data['items'] = $CI->Model->read('temoignages', ['est_approuve' => 1], 'id_temoignage', 'DESC');
            break;
            
        case 'partenaires':
        case 'partenaires_logos':
            $data['items'] = $CI->Model->read('partenaires', ['est_actif' => 1], 'id_partenaire', 'ASC');
            break;
            
        case 'liste_chiffres':
        case 'chiffres_cles':
            $data['items'] = $CI->Model->read('chiffres_cles', [], 'ordre', 'ASC');
            break;
            
        case 'equipe':
            $data['items'] = $CI->Model->read('equipe', [], 'ordre_affichage', 'ASC');
            break;
            
        case 'timeline':
            $data['items'] = $CI->Model->read('etapes_projet', [], 'ordre', 'ASC');
            break;
            
        case 'faq_accordeon':
            $data['items'] = $CI->Model->read('faq', ['est_publiee' => 1], 'ordre', 'ASC');
            break;
            
        case 'produits_vitrine':
            $cat_id = $data['options']['categorie'] ?? null;
            $where = $cat_id ? ['id_categorie' => $cat_id, 'est_actif' => 1] : ['est_actif' => 1];
            $data['items'] = $CI->Model->read('produits', $where, 'ordre_affichage', 'ASC');
            break;
            
        case 'telechargements':
            $data['items'] = $CI->Model->read('ressources_telechargeables', ['est_public' => 1], 'date_publication', 'DESC');
            break;
    }
    
    // Appeler la vue partielle correspondante
    $view_file = "sections/{$template}";
    
    // Fallback si la vue spécifique n'existe pas
    if (!file_exists(APPPATH . "views/{$view_file}.php")) {
        $view_file = "sections/{$section['type_section']}"; // Type de base
    }
    
    return $CI->load->view($view_file, $data, true);
}

/**
 * Génère le HTML pour un éditeur riche (CKEditor ou simple)
 */
function render_editor($name, $value = '', $id = null)
{
    $id = $id ?: 'editor_' . uniqid();
    return "
    <textarea name=\"{$name}\" id=\"{$id}\" class=\"form-control editor-rich\" rows=\"6\">" . htmlspecialchars($value) . "</textarea>
    <script>
        if (typeof CKEDITOR !== 'undefined') {
            CKEDITOR.replace('{$id}', {
                toolbar: [
                    ['Bold', 'Italic', 'Underline', 'Strike'],
                    ['NumberedList', 'BulletedList'],
                    ['Link', 'Unlink'],
                    ['RemoveFormat', 'Source']
                ],
                height: 200
            });
        }
    </script>";
}