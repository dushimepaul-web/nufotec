<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Panier extends Public_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'cookie']);
        $this->load->library(['session', 'user_agent']);
        $this->load->model('Panier_model');
        $this->load->model('Boutique_model');
    }



    /**
 * Affiche la page du panier
 */
public function index()
{
    // Récupérer le panier courant
    $panier = $this->_get_current_cart();

    $data = [
        'lignes'      => [],
        'total_ht'    => 0,
        'total_ttc'   => 0,
        'total_tva'   => 0,
        'tva_percent' => 20.0 // À adapter selon votre configuration (peut venir d'un paramètre)
    ];

    if ($panier) {
        // Récupérer les lignes
        $lignes = $this->Panier_model->get_cart_lines($panier->id);

        // Si le panier a des totaux en base, on les utilise
        if (isset($panier->montant_ht, $panier->montant_ttc)) {
            $data['total_ht']  = (float) $panier->montant_ht;
            $data['total_ttc'] = (float) $panier->montant_ttc;
            $data['total_tva'] = (float) $panier->montant_ttc - (float) $panier->montant_ht;
        } else {
            // Sinon on les recalcule (au cas où)
            $total_ht = $total_ttc = 0;
            foreach ($lignes as $ligne) {
                $total_ht  += (float) $ligne->prix_unitaire_ht * $ligne->quantite;
                $total_ttc += (float) $ligne->total_ligne_ttc;
            }
            $data['total_ht']  = $total_ht;
            $data['total_ttc'] = $total_ttc;
            $data['total_tva'] = $total_ttc - $total_ht;
        }

        // Enrichir les lignes avec les infos produit (nom, image, etc.)
        // Note: get_cart_lines() doit déjà joindre ces infos, sinon on les ajoute ici
        $data['lignes'] = $lignes;
    }

    // Charger la vue du panier
    $this->load->view('Panier_View',$data);
}

    // ------------------------------------------------------------------------

    /**
     * Récupère le panier courant (utilisé par l'offcanvas et le badge)
     */
    public function get_cart()
    {
        $panier = $this->_get_current_cart();

        if (!$panier) {
            $this->_send_cart_response([], 0, 0);
            return;
        }

        $lignes = $this->Panier_model->get_cart_lines($panier->id);
        $nb_articles = 0;
        $total_raw = 0;

        foreach ($lignes as $ligne) {
            $nb_articles += (int) $ligne->quantite;
            $total_raw   += (float) $ligne->total_ligne_ttc;
        }

        $this->_send_cart_response($lignes, $total_raw, $nb_articles);
    }

    // ------------------------------------------------------------------------

    /**
     * Ajoute un produit au panier et retourne l'état mis à jour
     */
    public function ajouter()
    {
        try {
            $produit_id = (int) $this->input->post('id');
            $quantite   = max(1, (int) $this->input->post('quantite'));

            $produit = $this->Boutique_model->get_product_by_id($produit_id);
            if (!$produit || $produit->est_actif != 1 || $produit->statut != 'commercialise') {
                throw new Exception('Produit non disponible');
            }

            $taux_tva = 20.0; // À adapter selon votre configuration
            $panier   = $this->_get_or_create_current_cart();

            $result = $this->Panier_model->add_or_update_line(
                $panier->id,
                $produit_id,
                $quantite,
                (float) $produit->prix_public,
                $taux_tva
            );

            if (!$result) {
                throw new Exception('Erreur lors de l\'ajout au panier');
            }

            // Recalculer les totaux du panier
            $this->Panier_model->update_cart_totals($panier->id);

            // Récupérer les lignes mises à jour
            $lignes = $this->Panier_model->get_cart_lines($panier->id);
            $nb_articles = 0;
            $total_raw   = 0;

            foreach ($lignes as $ligne) {
                $nb_articles += (int) $ligne->quantite;
                $total_raw   += (float) $ligne->total_ligne_ttc;
            }

            $this->_send_cart_response($lignes, $total_raw, $nb_articles, true, 'Produit ajouté avec succès');

        } catch (Exception $e) {
            log_message('error', 'ERREUR AJOUT PANIER : ' . $e->getMessage());
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]));
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Met à jour la quantité d'une ligne
     */
    public function update_quantity()
    {
        $ligne_id = (int) $this->input->post('ligne_id');
        $quantite = (int) $this->input->post('quantite');

        if (!$ligne_id || $quantite < 0) {
            $this->_send_error('Paramètres invalides');
            return;
        }

        // Vérifier que la ligne appartient bien au panier courant
        $ligne = $this->db->get_where('panier_lignes', ['id' => $ligne_id])->row();
        if (!$ligne) {
            $this->_send_error('Ligne introuvable');
            return;
        }

        $panier = $this->_get_current_cart();
        if (!$panier || $ligne->panier_id != $panier->id) {
            $this->_send_error('Action non autorisée');
            return;
        }

        if ($quantite < 1) {
            $this->Panier_model->delete_line($ligne_id);
        } else {
            $this->Panier_model->update_line_quantity($ligne_id, $quantite);
        }

        $this->Panier_model->update_cart_totals($panier->id);
        $this->_return_updated_cart($panier->id);
    }

    // ------------------------------------------------------------------------

    /**
     * Supprime une ligne du panier
     */
    public function delete_line()
    {
        $ligne_id = (int) $this->input->post('ligne_id');

        if (!$ligne_id) {
            $this->_send_error('Paramètres invalides');
            return;
        }

        $ligne = $this->db->get_where('panier_lignes', ['id' => $ligne_id])->row();
        if (!$ligne) {
            $this->_send_error('Ligne introuvable');
            return;
        }

        $panier = $this->_get_current_cart();
        if (!$panier || $ligne->panier_id != $panier->id) {
            $this->_send_error('Action non autorisée');
            return;
        }

        $this->Panier_model->delete_line($ligne_id);
        $this->Panier_model->update_cart_totals($panier->id);
        $this->_return_updated_cart($panier->id);
    }

    // ------------------------------------------------------------------------

    /**
     * Bascule un produit en favori (démo : utilisateur fixe)
     */
    public function toggle_favori()
    {
        try {
            $user_id = 1; // À remplacer par l'utilisateur connecté
            $produit_id = (int) $this->input->post('produit_id');

            if (!$produit_id) {
                throw new Exception('Produit non spécifié');
            }

            $existe = $this->db
                ->where('user_id', $user_id)
                ->where('produit_id', $produit_id)
                ->get('favoris')
                ->row();

            if ($existe) {
                $this->db->delete('favoris', ['id' => $existe->id]);
                $this->db->set('nb_favoris', 'nb_favoris - 1', false)
                         ->where('id_produit', $produit_id)
                         ->update('produits');
                $action = 'removed';
            } else {
                $this->db->insert('favoris', [
                    'user_id'     => $user_id,
                    'produit_id'  => $produit_id,
                    'date_ajout'  => date('Y-m-d H:i:s')
                ]);
                $this->db->set('nb_favoris', 'nb_favoris + 1', false)
                         ->where('id_produit', $produit_id)
                         ->update('produits');
                $action = 'added';
            }

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => true, 'action' => $action]));

        } catch (Exception $e) {
            log_message('error', 'ERREUR FAVORI : ' . $e->getMessage());
            $this->_send_error($e->getMessage());
        }
    }




    // ------------------------------------------------------------------------
    // MÉTHODES PRIVÉES
    // ------------------------------------------------------------------------

    /**
     * Retourne une réponse JSON standard avec les données du panier
     */
    private function _send_cart_response($lignes, $total_raw, $nb_articles, $success = true, $message = null)
    {
        $response = [
            'success'         => $success,
            'lignes'          => $lignes,
            'total_formatted' => number_format($total_raw, 0, ',', ' ') . '$',
            'total_raw'       => $total_raw,
            'nb_articles'     => $nb_articles
        ];

        if ($message) {
            $response['message'] = $message;
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    /**
     * Envoie une réponse d'erreur simple
     */
    private function _send_error($message)
    {
        $this->output
            ->set_status_header(400)
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => false, 'message' => $message]));
    }

    /**
     * Utilisé après update/delete pour retourner le panier mis à jour
     */
    private function _return_updated_cart($panier_id)
    {
        $lignes = $this->Panier_model->get_cart_lines($panier_id);
        $nb_articles = 0;
        $total_raw   = 0;

        foreach ($lignes as $ligne) {
            $nb_articles += (int) $ligne->quantite;
            $total_raw   += (float) $ligne->total_ligne_ttc;
        }

        $this->_send_cart_response($lignes, $total_raw, $nb_articles, true);
    }

    /**
     * Récupère le panier actif courant (utilisateur connecté ou visiteur)
     */
    private function _get_current_cart()
    {
        $user_id = $this->session->userdata('user_id');

        if ($user_id) {
            return $this->Panier_model->get_active_cart_by_user($user_id);
        }

        $visitor_token = get_cookie('visitor_token');
        if (!$visitor_token) {
            return null;
        }

        return $this->Panier_model->get_active_cart_by_token($visitor_token);
    }

    /**
     * Récupère le panier courant ou en crée un nouveau s'il n'existe pas
     */
    private function _get_or_create_current_cart()
    {
        $user_id = $this->session->userdata('user_id');

        if ($user_id) {
            $panier = $this->Panier_model->get_active_cart_by_user($user_id);
            if ($panier) {
                return $panier;
            }
            // Créer un nouveau panier pour l'utilisateur connecté
            $panier_id = $this->Panier_model->create_cart($user_id, null);
            return (object) [
                'id'          => $panier_id,
                'user_id'     => $user_id,
                'visitor_token' => null
            ];
        }

        // Visiteur : gérer via cookie
        $visitor_token = get_cookie('visitor_token');
        if (!$visitor_token) {
            $visitor_token = bin2hex(random_bytes(16));
            set_cookie('visitor_token', $visitor_token, 60 * 60 * 24 * 30); // 30 jours
        }

        $panier = $this->Panier_model->get_active_cart_by_token($visitor_token);
        if ($panier) {
            return $panier;
        }

        // Créer un nouveau panier pour le visiteur
        $panier_id = $this->Panier_model->create_cart(null, $visitor_token);
        return (object) [
            'id'            => $panier_id,
            'user_id'       => null,
            'visitor_token' => $visitor_token
        ];
    }
}