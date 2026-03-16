<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Actualites Controller
 * 
 * Gère l'affichage des actualités et articles de blog.
 * 
 * @author Dushime Paul
 * @email dushimeyesupaulin@gmail.com
 * @date 04/03/2026
 */
class Actualites extends Public_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper('text'); // Pour word_limiter, etc.
        
        // Charger le modèle si nécessaire (à adapter selon votre structure)
        if (!isset($this->Model)) {
            $this->load->model('Model');
        }
    }

    /**
     * Affiche la liste des actualités
     */
    public function index()
    {
        // Récupérer les actualités publiées, triées par date de publication décroissante
        $actualites = $this->Model->read('actualites_blog', 
            ['deleted_at' => null], 
            'date_publication', 
            'DESC'
        );

        // Si vous voulez paginer, utilisez $this->Model->readLimit() ou intégrez une librairie de pagination

        $data = [
            'title'      => 'Actualités et Blog',
            'actualites' => $actualites,
        ];

        $this->load->view('actualites_View', $data);
    }

    /**
     * Affiche le détail d'une actualité
     * @param string $slug
     */
    public function view($slug)
    {
        // Récupérer l'actualité par son slug
        $actualite = $this->Model->readOne('actualites_blog', [
            'slug'       => $slug,
            'deleted_at' => null
        ]);

        if (empty($actualite)) {
            show_404();
        }

        // Incrémenter le compteur de vues
        $this->db->set('vues', 'vues+1', FALSE);
        $this->db->where('id_actualite', $actualite['id_actualite']);
        $this->db->update('actualites_blog');

        $data = [
            'title'      => $actualite['titre'],
            'actualite'  => $actualite,
        ];

        $this->load->view('actualites_detail', $data);
    }
}