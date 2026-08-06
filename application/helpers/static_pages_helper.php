<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PAGES STATIQUES
 * La table `pages` a été supprimée volontairement. Toutes les données
 * de pages sont désormais définies ici, en dur, pour reproduire
 * exactement le contenu de l'ancienne table.
 */

if (!function_exists('static_pages_all')) {

    function static_pages_all()
    {
        static $pages = null;

        if ($pages === null) {
            $pages = array (
  0 => 
  array (
    'id_page' => 1,
    'titre_page' => 'Home',
    'slug' => 'home',
    'menu_ordre' => 1,
    'menu_parent_id' => NULL,
    'est_publiee' => 1,
    'meta_description' => 'AGF - Zambian agro-industrial biotechnology enterprise. USD 40+ million vision 2026-2031.',
    'meta_keywords' => '',
    'contenu_page' => '<div class="hero"><h1>Bienvenue chez AGF NuFoTEc</h1><p>Leader en biotechnologie agro-industrielle en Zambie</p></div><div class="features"><div class="feature"><h3>Innovation</h3><p>Recherche et développement de pointe</p></div><div class="feature"><h3>Durabilité</h3><p>Engagement environnemental et social</p></div><div class="feature"><h3>Qualité</h3><p>Standards GMP et certification internationale</p></div></div>',
    'template_specifique' => 'home',
    'icone_menu' => '',
    'date_creation' => '2026-02-25 17:03:01',
    'date_modification' => '2026-04-14 18:28:53',
    'deleted_at' => NULL,
  ),
  1 => 
  array (
    'id_page' => 2,
    'titre_page' => 'About',
    'slug' => 'about',
    'menu_ordre' => 2,
    'menu_parent_id' => NULL,
    'est_publiee' => 1,
    'meta_description' => 'Discover AGF: vertically integrated Zambian agro-industrial biotechnology company',
    'meta_keywords' => '',
    'contenu_page' => '<h1>À propos de AGF NuFoTEc</h1><p>AGF NuFoTEc est une entreprise innovante spécialisée dans la biotechnologie agro-industrielle en Zambie.</p><h2>Notre histoire</h2><p>Fondée en 2019, nous avons pour mission de transformer le secteur pharmaceutique en Afrique.</p><h2>Nos valeurs</h2><ul><li>Innovation</li><li>Qualité</li><li>Durabilité</li><li>Intégrité</li></ul>',
    'template_specifique' => 'about',
    'icone_menu' => '',
    'date_creation' => '2026-02-25 17:03:01',
    'date_modification' => '2026-04-14 18:28:53',
    'deleted_at' => NULL,
  ),
  2 => 
  array (
    'id_page' => 3,
    'titre_page' => 'Background & Strategic Rationale',
    'slug' => 'background-strategic-rationale',
    'menu_ordre' => 3,
    'menu_parent_id' => NULL,
    'est_publiee' => 1,
    'meta_description' => 'Why AGF? Context of pharmaceutical sector in Zambia',
    'meta_keywords' => '',
    'contenu_page' => NULL,
    'template_specifique' => 'default',
    'icone_menu' => '',
    'date_creation' => '2026-02-25 17:03:01',
    'date_modification' => '2026-04-14 18:08:06',
    'deleted_at' => NULL,
  ),
  3 => 
  array (
    'id_page' => 4,
    'titre_page' => 'ESG & Sustainability',
    'slug' => 'esg-sustainability',
    'menu_ordre' => 4,
    'menu_parent_id' => NULL,
    'est_publiee' => 1,
    'meta_description' => 'Our environmental, social and governance commitment',
    'meta_keywords' => NULL,
    'contenu_page' => NULL,
    'template_specifique' => 'esg',
    'icone_menu' => NULL,
    'date_creation' => '2026-02-25 17:03:01',
    'date_modification' => '2026-04-14 18:08:06',
    'deleted_at' => NULL,
  ),
  4 => 
  array (
    'id_page' => 5,
    'titre_page' => 'Research & Innovation',
    'slug' => 'research-innovation',
    'menu_ordre' => 5,
    'menu_parent_id' => NULL,
    'est_publiee' => 1,
    'meta_description' => 'Our scientific research platform and innovation',
    'meta_keywords' => NULL,
    'contenu_page' => NULL,
    'template_specifique' => 'research',
    'icone_menu' => NULL,
    'date_creation' => '2026-02-25 17:03:01',
    'date_modification' => '2026-04-14 18:08:06',
    'deleted_at' => NULL,
  ),
  5 => 
  array (
    'id_page' => 6,
    'titre_page' => 'Corporate Structure & Governance',
    'slug' => 'corporate-structure-governance',
    'menu_ordre' => 6,
    'menu_parent_id' => NULL,
    'est_publiee' => 1,
    'meta_description' => 'Our corporate structure and leadership team',
    'meta_keywords' => NULL,
    'contenu_page' => NULL,
    'template_specifique' => 'governance',
    'icone_menu' => NULL,
    'date_creation' => '2026-02-25 17:03:01',
    'date_modification' => '2026-04-14 18:08:06',
    'deleted_at' => NULL,
  ),
  6 => 
  array (
    'id_page' => 7,
    'titre_page' => 'nufotec-phytomed-industries-facility',
    'slug' => 'nufotec-phytomed-industries-facility',
    'menu_ordre' => 7,
    'menu_parent_id' => NULL,
    'est_publiee' => 1,
    'meta_description' => 'Facility layout plan and infrastructure',
    'meta_keywords' => '',
    'contenu_page' => NULL,
    'template_specifique' => 'facility',
    'icone_menu' => '',
    'date_creation' => '2026-02-25 17:03:01',
    'date_modification' => '2026-04-14 18:08:06',
    'deleted_at' => NULL,
  ),
  7 => 
  array (
    'id_page' => 8,
    'titre_page' => 'Our Product Categories',
    'slug' => 'product-categories',
    'menu_ordre' => 8,
    'menu_parent_id' => NULL,
    'est_publiee' => 1,
    'meta_description' => 'Our 7 GMP-compliant product categories',
    'meta_keywords' => NULL,
    'contenu_page' => NULL,
    'template_specifique' => 'products',
    'icone_menu' => NULL,
    'date_creation' => '2026-02-25 17:03:01',
    'date_modification' => '2026-04-14 18:08:06',
    'deleted_at' => NULL,
  ),
  8 => 
  array (
    'id_page' => 9,
    'titre_page' => 'Raw Material Acquisition',
    'slug' => 'raw-material-acquisition',
    'menu_ordre' => 9,
    'menu_parent_id' => NULL,
    'est_publiee' => 1,
    'meta_description' => 'How we source our raw materials',
    'meta_keywords' => NULL,
    'contenu_page' => NULL,
    'template_specifique' => 'default',
    'icone_menu' => NULL,
    'date_creation' => '2026-02-25 17:03:01',
    'date_modification' => '2026-04-14 18:08:06',
    'deleted_at' => NULL,
  ),
  9 => 
  array (
    'id_page' => 10,
    'titre_page' => 'Industrial Technology & Processing Systems',
    'slug' => 'industrial-technology',
    'menu_ordre' => 10,
    'menu_parent_id' => NULL,
    'est_publiee' => 1,
    'meta_description' => 'Our processing and manufacturing systems',
    'meta_keywords' => NULL,
    'contenu_page' => NULL,
    'template_specifique' => 'tech',
    'icone_menu' => NULL,
    'date_creation' => '2026-02-25 17:03:01',
    'date_modification' => '2026-04-14 18:08:06',
    'deleted_at' => NULL,
  ),
  10 => 
  array (
    'id_page' => 11,
    'titre_page' => 'Market & Industry Outlook',
    'slug' => 'market-outlook',
    'menu_ordre' => 11,
    'menu_parent_id' => NULL,
    'est_publiee' => 1,
    'meta_description' => 'Market analysis and opportunities',
    'meta_keywords' => NULL,
    'contenu_page' => NULL,
    'template_specifique' => 'market',
    'icone_menu' => NULL,
    'date_creation' => '2026-02-25 17:03:01',
    'date_modification' => '2026-04-14 18:08:06',
    'deleted_at' => NULL,
  ),
  11 => 
  array (
    'id_page' => 12,
    'titre_page' => 'Digital Growth & Market Expansion Platform',
    'slug' => 'digital-growth',
    'menu_ordre' => 12,
    'menu_parent_id' => NULL,
    'est_publiee' => 1,
    'meta_description' => 'Our AI-powered platform and WhatsApp communities',
    'meta_keywords' => NULL,
    'contenu_page' => NULL,
    'template_specifique' => 'digital',
    'icone_menu' => NULL,
    'date_creation' => '2026-02-25 17:03:01',
    'date_modification' => '2026-04-14 18:08:06',
    'deleted_at' => NULL,
  ),
  12 => 
  array (
    'id_page' => 13,
    'titre_page' => 'Digital Health Consultation',
    'slug' => 'digital-health',
    'menu_ordre' => 13,
    'menu_parent_id' => NULL,
    'est_publiee' => 1,
    'meta_description' => 'Online consultation services',
    'meta_keywords' => NULL,
    'contenu_page' => NULL,
    'template_specifique' => 'health',
    'icone_menu' => NULL,
    'date_creation' => '2026-02-25 17:03:01',
    'date_modification' => '2026-04-14 18:08:06',
    'deleted_at' => NULL,
  ),
  13 => 
  array (
    'id_page' => 14,
    'titre_page' => 'Phased Investment Projection',
    'slug' => 'investment-projection',
    'menu_ordre' => 14,
    'menu_parent_id' => NULL,
    'est_publiee' => 1,
    'meta_description' => 'Investment phases and seed capital allocation',
    'meta_keywords' => NULL,
    'contenu_page' => NULL,
    'template_specifique' => 'investment',
    'icone_menu' => NULL,
    'date_creation' => '2026-02-25 17:03:01',
    'date_modification' => '2026-04-14 18:08:06',
    'deleted_at' => NULL,
  ),
  14 => 
  array (
    'id_page' => 15,
    'titre_page' => 'Our Investor & Partner Commitment',
    'slug' => 'investor-commitment',
    'menu_ordre' => 15,
    'menu_parent_id' => NULL,
    'est_publiee' => 1,
    'meta_description' => 'Our commitment to financial partners',
    'meta_keywords' => NULL,
    'contenu_page' => NULL,
    'template_specifique' => 'investor',
    'icone_menu' => NULL,
    'date_creation' => '2026-02-25 17:03:01',
    'date_modification' => '2026-04-14 18:08:06',
    'deleted_at' => NULL,
  ),
  15 => 
  array (
    'id_page' => 16,
    'titre_page' => 'Commission Fee Payment to Brokers',
    'slug' => 'broker-commission',
    'menu_ordre' => 16,
    'menu_parent_id' => NULL,
    'est_publiee' => 1,
    'meta_description' => 'Broker commission payment terms',
    'meta_keywords' => NULL,
    'contenu_page' => NULL,
    'template_specifique' => 'brokers',
    'icone_menu' => NULL,
    'date_creation' => '2026-02-25 17:03:01',
    'date_modification' => '2026-04-14 18:08:06',
    'deleted_at' => NULL,
  ),
  16 => 
  array (
    'id_page' => 17,
    'titre_page' => 'Risk Analysis & Mitigation Strategies',
    'slug' => 'risk-analysis',
    'menu_ordre' => 17,
    'menu_parent_id' => NULL,
    'est_publiee' => 1,
    'meta_description' => 'Comprehensive risk analysis',
    'meta_keywords' => NULL,
    'contenu_page' => NULL,
    'template_specifique' => 'risk',
    'icone_menu' => NULL,
    'date_creation' => '2026-02-25 17:03:01',
    'date_modification' => '2026-04-14 18:08:06',
    'deleted_at' => NULL,
  ),
  17 => 
  array (
    'id_page' => 18,
    'titre_page' => 'Strategic Partnerships',
    'slug' => 'strategic-partnerships',
    'menu_ordre' => 18,
    'menu_parent_id' => NULL,
    'est_publiee' => 1,
    'meta_description' => 'Our scientific and financial partners',
    'meta_keywords' => NULL,
    'contenu_page' => NULL,
    'template_specifique' => 'partners',
    'icone_menu' => NULL,
    'date_creation' => '2026-02-25 17:03:01',
    'date_modification' => '2026-04-14 18:08:06',
    'deleted_at' => NULL,
  ),
  18 => 
  array (
    'id_page' => 19,
    'titre_page' => 'Our Services',
    'slug' => 'our-services',
    'menu_ordre' => 19,
    'menu_parent_id' => NULL,
    'est_publiee' => 1,
    'meta_description' => 'Our services',
    'meta_keywords' => NULL,
    'contenu_page' => NULL,
    'template_specifique' => 'services',
    'icone_menu' => NULL,
    'date_creation' => '2026-02-25 17:03:01',
    'date_modification' => '2026-04-14 18:08:06',
    'deleted_at' => NULL,
  ),
  19 => 
  array (
    'id_page' => 22,
    'titre_page' => 'Vision & Mission',
    'slug' => 'vision-mission',
    'menu_ordre' => 0,
    'menu_parent_id' => 2,
    'est_publiee' => 1,
    'meta_description' => '',
    'meta_keywords' => '',
    'contenu_page' => NULL,
    'template_specifique' => 'default',
    'icone_menu' => '',
    'date_creation' => '2026-03-04 11:37:30',
    'date_modification' => '2026-04-14 18:28:53',
    'deleted_at' => NULL,
  ),
  20 => 
  array (
    'id_page' => 23,
    'titre_page' => 'Brokers Form',
    'slug' => 'brokers-form',
    'menu_ordre' => 20,
    'menu_parent_id' => NULL,
    'est_publiee' => 1,
    'meta_description' => '',
    'meta_keywords' => '',
    'contenu_page' => NULL,
    'template_specifique' => 'default',
    'icone_menu' => '',
    'date_creation' => '2026-03-04 16:18:18',
    'date_modification' => '2026-04-14 18:28:53',
    'deleted_at' => NULL,
  ),
  21 => 
  array (
    'id_page' => 24,
    'titre_page' => 'Investors form',
    'slug' => 'investors-form',
    'menu_ordre' => 0,
    'menu_parent_id' => NULL,
    'est_publiee' => 1,
    'meta_description' => '',
    'meta_keywords' => '',
    'contenu_page' => NULL,
    'template_specifique' => 'default',
    'icone_menu' => '',
    'date_creation' => '2026-03-04 16:18:53',
    'date_modification' => '2026-04-14 18:28:53',
    'deleted_at' => NULL,
  ),
);
        }

        return $pages;
    }
}

if (!function_exists('_static_page_matches')) {

    function _static_page_matches($page, $where)
    {
        if (empty($where)) {
            return true;
        }

        foreach ($where as $key => $value) {
            $col = $key;
            $op = '=';

            if (strpos($key, ' !=') !== false) {
                $col = trim(str_replace(' !=', '', $key));
                $op = '!=';
            } elseif (strpos($key, ' >=') !== false) {
                $col = trim(str_replace(' >=', '', $key));
                $op = '>=';
            } elseif (strpos($key, ' <=') !== false) {
                $col = trim(str_replace(' <=', '', $key));
                $op = '<=';
            }

            $pageVal = $page[$col] ?? null;

            if (is_array($value)) {
                if (!in_array($pageVal, $value)) {
                    return false;
                }
                continue;
            }

            switch ($op) {
                case '!=':
                    if ($pageVal == $value) return false;
                    break;
                case '>=':
                    if (!($pageVal >= $value)) return false;
                    break;
                case '<=':
                    if (!($pageVal <= $value)) return false;
                    break;
                default:
                    if ($pageVal != $value) return false;
            }
        }

        return true;
    }
}

if (!function_exists('static_pages_where')) {

    /**
     * Équivalent statique de Model::read('pages', ...)
     */
    function static_pages_where($where = [], $order_by = null, $order = 'ASC')
    {
        $out = [];

        foreach (static_pages_all() as $page) {
            if (_static_page_matches($page, $where)) {
                $out[] = $page;
            }
        }

        if ($order_by !== null) {
            usort($out, function ($a, $b) use ($order_by, $order) {
                $va = $a[$order_by] ?? null;
                $vb = $b[$order_by] ?? null;
                if ($va === $vb) return 0;
                $cmp = ($va < $vb) ? -1 : 1;
                return ($order === 'DESC') ? -$cmp : $cmp;
            });
        }

        return $out;
    }
}

if (!function_exists('static_pages_one')) {

    /**
     * Équivalent statique de Model::readOne/read_one('pages', ...)
     */
    function static_pages_one($where = [])
    {
        foreach (static_pages_all() as $page) {
            if (_static_page_matches($page, $where)) {
                return $page;
            }
        }

        return null;
    }
}

if (!function_exists('static_pages_search')) {

    /**
     * Équivalent statique de la recherche dans la table pages
     */
    function static_pages_search($term)
    {
        $term = strtolower($term);
        $out = [];
        $limit = 10;

        foreach (static_pages_all() as $page) {
            if ($page['est_publiee'] != 1 || $page['deleted_at'] !== null) {
                continue;
            }

            $titre = strtolower($page['titre_page'] ?? '');
            $meta = strtolower($page['meta_description'] ?? '');

            if (strpos($titre, $term) !== false || strpos($meta, $term) !== false) {
                $out[] = [
                    'id' => $page['id_page'],
                    'titre' => $page['titre_page'],
                    'extrait' => $page['meta_description'] ?? '',
                    'type' => 'page',
                    'slug' => $page['slug']
                ];

                if (count($out) >= $limit) {
                    break;
                }
            }
        }

        return $out;
    }
}

if (!function_exists('static_pages_count')) {

    /**
     * Nombre de pages (publiées ou toutes selon $published_only)
     */
    function static_pages_count($published_only = false)
    {
        $count = 0;

        foreach (static_pages_all() as $page) {
            if ($page['deleted_at'] !== null) continue;
            if ($published_only && $page['est_publiee'] != 1) continue;
            $count++;
        }

        return $count;
    }
}
