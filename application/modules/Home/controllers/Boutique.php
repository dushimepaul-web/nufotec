<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Boutique extends Public_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'text', 'form']);
        $this->load->library(['pagination', 'session']);
        $this->load->model('Boutique_model');
    }

    public function index()
    {
        $data['hero_section'] = $this->get_hero_section();
        $data['categorie_active'] = null;
        $data['categorie_info'] = null;
        $data['categories'] = $this->Boutique_model->get_categories();
        $data['produits'] = $this->Boutique_model->get_all_products();
        $data['total_produits'] = $this->Boutique_model->count_all_products();

        $config = $this->configure_pagination(base_url('boutique/index'), $data['total_produits']);
        $this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();

        $data['titre'] = 'Tous nos produits - AGF Pharma';
        $this->load->view('Boutique_View', $data);
    }

    public function categorie($id_categorie)
    {
        $data['hero_section'] = $this->get_hero_section();
        $categorie_info = $this->Boutique_model->get_categorie_by_id($id_categorie);
        if (!$categorie_info) show_404();

        $data['categorie_active'] = $id_categorie;
        $data['categorie_info'] = $categorie_info;
        $data['categories'] = $this->Boutique_model->get_categories();
        $data['workflow'] = $this->Boutique_model->get_category_workflow($id_categorie);
        $data['produits'] = $this->Boutique_model->get_products_by_category($id_categorie);
        $data['total_produits'] = $this->Boutique_model->count_products_by_category($id_categorie);

        $config = $this->configure_pagination(
            base_url('boutique/categorie/' . $id_categorie),
            $data['total_produits']
        );
        $this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();

        $data['titre'] = $categorie_info->nom_categorie . ' - AGF Pharma';
        $this->load->view('Boutique_View', $data);
    }

    public function detail($slug)
    {
        $data['hero_section'] = $this->get_hero_section();
        $produit = $this->Boutique_model->get_product_by_slug($slug);
        if (!$produit) show_404();

        $data['produit'] = $produit;
        $data['categories'] = $this->Boutique_model->get_categories();
        $data['images'] = $this->Boutique_model->get_product_images($produit->id_produit);
        $data['certifications'] = json_decode($produit->certifications, true) ?: [];
        $data['produits_similaires'] = $this->Boutique_model->get_related_products(
            $produit->id_categorie,
            $produit->id_produit
        );
        $data['titre'] = $produit->nom_produit . ' - AGF Pharma';
        $this->load->view('produit_detail', $data);
    }

    public function recherche()
    {
        $data['hero_section'] = $this->get_hero_section();
        $query = trim($this->input->get('q', true));
        if (empty($query)) redirect('boutique');

        $data['query'] = $query;
        $data['categories'] = $this->Boutique_model->get_categories();
        $data['categorie_active'] = null;
        $data['categorie_info'] = null;
        $data['produits'] = $this->Boutique_model->search_products($query);
        $data['total_produits'] = $this->Boutique_model->count_search_results($query);

        $config = $this->configure_pagination(
            base_url('boutique/recherche?q=' . urlencode($query)),
            $data['total_produits']
        );
        $this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();

        $data['titre'] = 'Recherche : "' . $query . '" - AGF Pharma';
        $this->load->view('Boutique_View', $data);
    }

    private function configure_pagination($base_url, $total_rows)
    {
        return [
            'base_url' => $base_url,
            'total_rows' => $total_rows,
            'per_page' => 12,
            'uri_segment' => 3,
            'num_links' => 2,
            'full_tag_open' => '<ul class="pagination">',
            'full_tag_close' => '</ul>',
            'first_link' => 'Premier',
            'first_tag_open' => '<li class="page-item">',
            'first_tag_close' => '</li>',
            'last_link' => 'Dernier',
            'last_tag_open' => '<li class="page-item">',
            'last_tag_close' => '</li>',
            'next_link' => '&raquo;',
            'next_tag_open' => '<li class="page-item">',
            'next_tag_close' => '</li>',
            'prev_link' => '&laquo;',
            'prev_tag_open' => '<li class="page-item">',
            'prev_tag_close' => '</li>',
            'cur_tag_open' => '<li class="page-item active"><a class="page-link" href="#">',
            'cur_tag_close' => '</a></li>',
            'num_tag_open' => '<li class="page-item">',
            'num_tag_close' => '</li>',
            'attributes' => ['class' => 'page-link']
        ];
    }

    private function get_hero_section()
    {
        $page = static_pages_one(['slug' => 'product-categories', 'est_publiee' => 1]);
        if (empty($page)) return null;
        $hero = static_sections_one([
            'id_page' => $page['id_page'],
            'type_section' => 'hero',
            'est_active' => 1
        ]);
        if (!empty($hero['options_json'])) $hero['options'] = json_decode($hero['options_json'], true);
        return $hero;
    }

    public function ajax_get_products()
    {
        $categorie_id = $this->input->post('categorie_id');
        $search = $this->input->post('search');
        $sort = $this->input->post('sort');
        $filters = $this->input->post('filters');
        $page = (int)$this->input->post('page') ?: 0;
        $limit = 12;
        $offset = $page * $limit;

        $workflow_html = '';
        if ($categorie_id && $categorie_id !== 'all') {
            $categorie_info = $this->Boutique_model->get_categorie_by_id($categorie_id);
            if ($categorie_info) {
                $workflow = $this->Boutique_model->get_category_workflow($categorie_id);
                if (!empty($workflow)) {
                    $workflow_html = $this->load->view('partials/workflow_section', [
                        'categorie_info' => $categorie_info,
                        'workflow' => $workflow
                    ], TRUE);
                }
            }
        }

        $this->db->select('p.*, c.nom_categorie, c.code_categorie, c.icone');
        $this->db->from('produits p');
        $this->db->join('categories c', 'c.id_categorie = p.id_categorie');
        $this->db->where('p.est_actif', 1);
        $this->db->where('p.statut', 'commercialise');

        if (!empty($categorie_id) && $categorie_id !== 'all') {
            $this->db->where('p.id_categorie', $categorie_id);
        }

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('p.nom_produit', $search);
            $this->db->or_like('p.description_courte', $search);
            $this->db->or_like('p.description_longue', $search);
            $this->db->or_like('p.composition', $search);
            $this->db->group_end();
        }

        if (!empty($filters)) {
            if (in_array('vedette', $filters)) $this->db->where('p.est_vedette', 1);
            if (in_array('new', $filters)) $this->db->where('p.date_lancement >', date('Y-m-d', strtotime('-30 days')));
            if (in_array('promo', $filters)) {
                $this->db->where('p.prix_grossiste IS NOT NULL');
                $this->db->where('p.prix_grossiste < p.prix_public');
            }
            // if (in_array('stock', $filters)) $this->db->where('p.stock >', 0);
        }

        switch ($sort) {
            case 'prix_asc': $this->db->order_by('p.prix_public', 'ASC'); break;
            case 'prix_desc': $this->db->order_by('p.prix_public', 'DESC'); break;
            case 'nom': $this->db->order_by('p.nom_produit', 'ASC'); break;
            case 'vedette':
                $this->db->order_by('p.est_vedette', 'DESC');
                $this->db->order_by('p.ordre_affichage', 'ASC');
                break;
            default:
                $this->db->order_by('p.est_vedette', 'DESC');
                $this->db->order_by('p.ordre_affichage', 'ASC');
                $this->db->order_by('p.id_produit', 'DESC');
        }

        $count_query = clone $this->db;
        $total = $count_query->count_all_results();

        $this->db->limit($limit, $offset);
        $produits = $this->db->get()->result();

        $html = '';
        foreach ($produits as $prod) {
            $html .= $this->load->view('partials/product_card', ['prod' => $prod], TRUE);
        }

        $total_pages = ceil($total / $limit);

        $this->output->set_content_type('application/json')
                     ->set_output(json_encode([
                         'success' => true,
                         'html' => $html,
                         'workflow_html' => $workflow_html,
                         'total' => $total,
                         'page' => $page,
                         'total_pages' => $total_pages,
                         'limit' => $limit
                     ]));
    }
}