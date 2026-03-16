<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Actualites extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        
        $this->load->helper('form');
        is_admin();
    }
    
    public function index()
    {
        $data['actualites'] = $this->Model->read('actualites_blog', ['deleted_at' => NULL], 'id_actualite','DESC');
        $data['pages'] = $this->Model->read('pages', ['est_publiee' => 1], 'id_page');
        $this->load->view('Actualites_View', $data);
    }

    function ChangeStatus(){
        $id = $this->input->post('id');
        $est_en_avant = $this->input->post('est_en_avant');
        
        $status = ($est_en_avant == 1) ? 0 : 1;
        $rsp = $this->Model->update('actualites_blog', ['id_actualite' => $id], ['est_en_avant' => $status]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Statut "En avant" mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Actualites'));    
    }

    function ActualiteDetail($actualiteDetail){
        $id = explode('_', $actualiteDetail);
        $data['detail'] = $this->Model->readOne('actualites_blog', ['id_actualite' => $id[0]]);
        $data['pages'] = $this->Model->read('pages', ['est_publiee' => 1], 'id_page');
        $this->load->view('ActualiteDetail_View', $data);
    }

    function Create(){
        // Validation des champs requis
        $this->form_validation->set_rules('titre', 'Titre', 'required');
        $this->form_validation->set_rules('slug', 'Slug', 'required|is_unique[actualites_blog.slug]');
        $this->form_validation->set_rules('contenu', 'Contenu', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Actualites'));
            return;
        }

        $titre = $this->input->post('titre');
        $slug = $this->url_slug($this->input->post('slug'));
        $resume = $this->input->post('resume') ?: NULL;
        $contenu = $this->input->post('contenu');
        $auteur = $this->input->post('auteur') ?: NULL;
        $date_publication = $this->input->post('date_publication') ?: date('Y-m-d H:i:s');
        $categorie = $this->input->post('categorie') ?: NULL;
        $tags = $this->input->post('tags') ? json_encode(explode(',', $this->input->post('tags'))) : NULL;
        $id_page_associee = $this->input->post('id_page_associee') ?: NULL;
        $est_en_avant = $this->input->post('est_en_avant') ? 1 : 0;

        // Upload image si fournie
        $image_principale = NULL;
        if (!empty($_FILES['image_principale']['name'])) {
            $image_principale = $this->upload_image($_FILES['image_principale']['tmp_name'], $_FILES['image_principale']['name']);
            if ($image_principale === NULL) {
                $this->session->set_flashdata('error', 'Format d\'image non valide. Formats acceptés: gif, jpg, png, jpeg, webp');
                redirect(base_url('Actualites'));
                return;
            }
        }

        $data = array(
            'titre' => $titre,
            'slug' => $slug,
            'resume' => $resume,
            'contenu' => $contenu,
            'image_principale' => $image_principale,
            'auteur' => $auteur,
            'date_publication' => $date_publication,
            'categorie' => $categorie,
            'tags' => $tags,
            'est_en_avant' => $est_en_avant,
            'vues' => 0,
            'id_page_associee' => $id_page_associee,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );
        
        $rsp = $this->Model->create('actualites_blog', $data);
          
          $image_url = base_url('attachments/Actualites/' . $image_principale);
          $dataZAPIER=array(
            'titre' => $titre,
            'description' => $contenu,
            'image' => $image_url,
          );

        if ($rsp) {

            // Envoyer une notification à l'admin
               $admin_phone = $this->Model->get_setting('admin_phone', '+33600000000');
               $message = "Nouvelle actualité : " . $titre;
               $image_url = base_url('attachments/Actualites/' . $image_principale);
               
               if (!empty($image_principale)) {
                   send_twilio_media($admin_phone, $message, $image_url, 'whatsapp');
               } else {
                   send_twilio_whatsapp($admin_phone, $message);
               }


            $result = send_to_zapier($dataZAPIER);
            $this->sendNewsletter($rsp, $titre, $resume ?: strip_tags(substr($contenu, 0, 200)));



     //$groups = get_whapi_groups();    pour lister vos groupes et récupérer leurs IDs.
     //print_r($groups);

            // Récupérer les IDs des groupes (vous pouvez les stocker en base)
    // IDs des groupes (vous pouvez les stocker en base de données)
    $group_ids = ['1234567890', '0987654321']; // Exemples d'IDs de groupes

    $message = "🔔 *Nouvelle actualité* 🔔\n\n" . $titre . "\n\n" . $resume . "\n\nLire la suite sur notre site.";
    $image_url = $image_principale ? base_url('attachments/Actualites/' . $image_principale) : null;

    if ($image_url) {
        $results = send_whapi_bulk_to_groups($group_ids, $message, $image_url, 'image');
    } else {
        $results = send_whapi_bulk_to_groups($group_ids, $message);
    }

    // Vous pouvez logger les résultats
    log_message('debug', 'Résultats Whapi: ' . print_r($results, true));





// Afficher un message de succès avec les stats
if ($resultats['success'] > 0) {
    $this->session->set_flashdata('success', "Actualité créée. {$resultats['success']} messages WhatsApp envoyés.");
} else {
    $this->session->set_flashdata('error', "Aucun message WhatsApp n'a pu être envoyé.");
}




            $this->session->set_flashdata('success', 'Actualité créée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création.');
        }
        redirect(base_url('Actualites'));
    }

    function Update(){
        $id = $this->input->post('id');
        
        // Validation
        $this->form_validation->set_rules('titre', 'Titre', 'required');
        $this->form_validation->set_rules('slug', 'Slug', 'required');
        $this->form_validation->set_rules('contenu', 'Contenu', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Actualites'));
            return;
        }

        $titre = $this->input->post('titre');
        $slug = $this->url_slug($this->input->post('slug'));
        $resume = $this->input->post('resume') ?: NULL;
        $contenu = $this->input->post('contenu');
        $auteur = $this->input->post('auteur') ?: NULL;
        $date_publication = $this->input->post('date_publication') ?: date('Y-m-d H:i:s');
        $categorie = $this->input->post('categorie') ?: NULL;
        $tags = $this->input->post('tags') ? json_encode(explode(',', $this->input->post('tags'))) : NULL;
        $id_page_associee = $this->input->post('id_page_associee') ?: NULL;
        $est_en_avant = $this->input->post('est_en_avant') ? 1 : 0;

        $data = array(
            'titre' => $titre,
            'slug' => $slug,
            'resume' => $resume,
            'contenu' => $contenu,
            'auteur' => $auteur,
            'date_publication' => $date_publication,
            'categorie' => $categorie,
            'tags' => $tags,
            'est_en_avant' => $est_en_avant,
            'id_page_associee' => $id_page_associee,
            'updated_at' => date('Y-m-d H:i:s')
        );

        // Upload nouvelle image si fournie
        if (!empty($_FILES['image_principale']['name'])) {
            $new_image = $this->upload_image($_FILES['image_principale']['tmp_name'], $_FILES['image_principale']['name']);
            if ($new_image === NULL) {
                $this->session->set_flashdata('error', 'Format d\'image non valide. Formats acceptés: gif, jpg, png, jpeg, webp');
                redirect(base_url('Actualites'));
                return;
            }
            
            // Supprimer l'ancienne image si existe
            $actualite = $this->Model->readOne('actualites_blog', ['id_actualite' => $id]);
            if ($actualite && !empty($actualite['image_principale']) && file_exists(FCPATH . 'attachments/Actualites/' . $actualite['image_principale'])) {
                unlink(FCPATH . 'attachments/Actualites/' . $actualite['image_principale']);
            }
            
            $data['image_principale'] = $new_image;
        }

        $rsp = $this->Model->update('actualites_blog', ['id_actualite' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Actualité mise à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Actualites'));
    }

    function Delete(){
        $id = $this->input->post('id');
        
        // Récupérer l'actualité pour supprimer son image
        $actualite = $this->Model->readOne('actualites_blog', ['id_actualite' => $id]);
        
        // Soft delete
        $rsp = $this->Model->update('actualites_blog', ['id_actualite' => $id], [
            'deleted_at' => date('Y-m-d H:i:s'),
            'est_en_avant' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($rsp) {
            // Supprimer l'image physique si existe
            if ($actualite && !empty($actualite['image_principale']) && file_exists(FCPATH . 'attachments/Actualites/' . $actualite['image_principale'])) {
                unlink(FCPATH . 'attachments/Actualites/' . $actualite['image_principale']);
            }
            $this->session->set_flashdata('success', 'Actualité supprimée avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('Actualites'));
    }

    // Génération de slug URL-friendly
    private function url_slug($str, $options = array()) {
        $str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());
        $defaults = array(
            'delimiter' => '-',
            'limit' => null,
            'lowercase' => true,
            'replacements' => array(),
            'transliterate' => true
        );
        $options = array_merge($defaults, $options);
        $char_map = array(
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O',
            'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH',
            'ß' => 'ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae',
            'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i',
            'ï' => 'i', 'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ő' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y',
            'þ' => 'th', 'ÿ' => 'y'
        );
        $str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);
        if ($options['transliterate']) {
            $str = str_replace(array_keys($char_map), $char_map, $str);
        }
        $str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);
        $str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);
        $str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');
        $str = trim($str, $options['delimiter']);
        return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
    }

    // Upload images
    public function upload_image($nom_file, $nom_champ)
    {
        $ref_folder = FCPATH . 'attachments/Actualites/';
        $code = date("YmdHis") . uniqid();
        $fichier = basename($code);
        $file_extension = pathinfo($nom_champ, PATHINFO_EXTENSION);
        $file_extension = strtolower($file_extension);
        $valid_ext = array('gif', 'jpg', 'png', 'jpeg', 'webp', 'svg');

        if (!in_array($file_extension, $valid_ext)) {
            return NULL;
        }

        if (!is_dir($ref_folder)) {
            mkdir($ref_folder, 0777, TRUE);
        }

        move_uploaded_file($nom_file, $ref_folder . $fichier . "." . $file_extension);
        return $fichier . "." . $file_extension;
    }

    // Catégories prédéfinies
    public function get_categories() {
        return [
            'entreprise' => 'Entreprise',
            'produits' => 'Produits',
            'recherche' => 'Recherche & Innovation',
            'partenariats' => 'Partenariats',
            'esg' => 'ESG & Durabilité',
            'evenements' => 'Événements',
            'presse' => 'Presse',
            'blog' => 'Blog'
        ];
    }





// Envoi newsletter à tous les abonnés
    private function sendNewsletter($id, $title, $details)
    {
        $subscribers = $this->Model->read('newsletter');
        if (!$subscribers) return;

        $smtp_pass = $this->Model->get_setting('smtp_password', '');
        $smtp_email = $this->Model->get_setting('smtp_email', 'noreply@agf-phytomed.com');
        $site_name = $this->Model->get_setting('site_name', 'AGF Phytomed');
        $admin_email = $this->Model->get_setting('admin_email', 'partnerships@agf-phytomed.com');
        $whatsapp = $this->Model->get_setting('site_phone', '68863945'); 
        $logo_url = base_url('attachments/Configurations/' . $this->Model->get_setting('site_logo', 'logo.png'));

        $config = [
            'protocol'    => 'smtp',
            'smtp_host'   => 'smtp.gmail.com',
            'smtp_port'   => 587,
            'smtp_user'   => $smtp_email,
            'smtp_pass'   => $smtp_pass,
            'smtp_crypto' => 'tls',
            'mailtype'    => 'html',
            'charset'     => 'utf-8',
            'newline'     => "\r\n"
        ];

        $this->email->initialize($config);

        foreach ($subscribers as $row) {
            $this->email->clear(TRUE);
            $this->email->from($smtp_email, $site_name);
            $this->email->to($row['email']);
            $this->email->subject($title);
            $this->email->message($this->newsletterTemplate($id, $title, $details, $logo_url, $site_name));
            $this->email->send();
        }
    }

    // Template HTML newsletter
    private function newsletterTemplate($id, $title, $details, $logo_url, $site_name)
    {
        return "
        <div style='font-family: Arial, sans-serif; text-align: center; background-color: #f8f9fa; padding: 20px;'>
            <img src='{$logo_url}' alt='{$site_name}' style='max-height: 60px; margin-bottom: 20px;'>
            <h2>{$title}</h2>
            <p>{$details}</p>
            <a href='".base_url('Home/News_detail/'.$id)."' style='display:inline-block; margin-top: 10px; padding: 10px 20px; background:#007bff; color:#fff; text-decoration:none; border-radius:5px;'>Voir les détails</a>
            <p style='font-size:12px; color:#777; margin-top:20px;'>&copy; ".date('Y')." {$site_name}</p>
        </div>";
    }


    public function test_sms()
{
    $this->load->helper('twilio');
    $result = send_twilio_sms('+33612345678', 'Test depuis CodeIgniter');
    print_r($result);
}


public function test_whapi()
{
    $this->load->helper('whapi');
    $group_id = 'ID_DU_GROUPE'; // Remplacez par un vrai ID
    $result = send_whapi_text($group_id, 'Test depuis CodeIgniter');
    print_r($result);
}



public function get_whatsapp_groups()
{
    // Charger le helper Whapi
    $this->load->helper('whapi');
    
    // Appeler la fonction pour récupérer les groupes
    $groups = get_whapi_groups();
    
    // Afficher le résultat
    echo '<pre>';
    print_r($groups);
    echo '</pre>';
}
}