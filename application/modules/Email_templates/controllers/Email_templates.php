<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Email_templates extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        is_admin();
    }
    
    public function index()
    {
        // Récupérer tous les templates
        $data['templates'] = $this->Model->read('email_templates', [], 'id', 'DESC');
        $this->load->view('EmailTemplates_View', $data);
    }

    public function create()
    {
        // Validation des champs requis
        $this->form_validation->set_rules('template_key', 'Clé du template', 'required|alpha_dash|is_unique[email_templates.template_key]');
        $this->form_validation->set_rules('template_name', 'Nom du template', 'required');
        $this->form_validation->set_rules('subject', 'Sujet', 'required');
        $this->form_validation->set_rules('body', 'Corps du message', 'required');
        $this->form_validation->set_rules('category', 'Catégorie', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Email_templates'));
            return;
        }

        // Traitement des variables
        $variables = $this->input->post('variables');
        $variables_array = array_map('trim', explode(',', $variables));
        $variables_json = json_encode($variables_array);

        $data = array(
            'template_key' => $this->input->post('template_key'),
            'template_name' => $this->input->post('template_name'),
            'subject' => $this->input->post('subject'),
            'body' => $this->input->post('body'),
            'variables' => $variables_json,
            'description' => $this->input->post('description'),
            'category' => $this->input->post('category'),
            'is_active' => $this->input->post('is_active') ? 1 : 0,
            'created_by' => $this->session->userdata('user_id'),
            'updated_by' => $this->session->userdata('user_id')
        );
        
        $rsp = $this->Model->create('email_templates', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Template d\'email créé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création.');
        }
        redirect(base_url('Email_templates'));
    }

    public function update()
    {
        $id = $this->input->post('id');
        
        // Validation
        $this->form_validation->set_rules('template_key', 'Clé du template', 'required|alpha_dash');
        $this->form_validation->set_rules('template_name', 'Nom du template', 'required');
        $this->form_validation->set_rules('subject', 'Sujet', 'required');
        $this->form_validation->set_rules('body', 'Corps du message', 'required');
        $this->form_validation->set_rules('category', 'Catégorie', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Email_templates'));
            return;
        }

        // Récupérer le template existant
        $template = $this->Model->readOne('email_templates', ['id' => $id]);
        if (!$template) {
            $this->session->set_flashdata('error', 'Template non trouvé');
            redirect(base_url('Email_templates'));
            return;
        }

        // Vérifier l'unicité de la clé (sauf pour le même template)
        $existing = $this->Model->readOne('email_templates', ['template_key' => $this->input->post('template_key')]);
        if ($existing && $existing['id'] != $id) {
            $this->session->set_flashdata('error', 'Cette clé de template est déjà utilisée');
            redirect(base_url('Email_templates'));
            return;
        }

        // Traitement des variables
        $variables = $this->input->post('variables');
        $variables_array = array_map('trim', explode(',', $variables));
        $variables_json = json_encode($variables_array);

        $data = array(
            'template_key' => $this->input->post('template_key'),
            'template_name' => $this->input->post('template_name'),
            'subject' => $this->input->post('subject'),
            'body' => $this->input->post('body'),
            'variables' => $variables_json,
            'description' => $this->input->post('description'),
            'category' => $this->input->post('category'),
            'is_active' => $this->input->post('is_active') ? 1 : 0,
            'updated_by' => $this->session->userdata('user_id')
        );

        $rsp = $this->Model->update('email_templates', ['id' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Template mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Email_templates'));
    }

    public function delete()
    {
        $id = $this->input->post('id');
        
        // Récupérer le template
        $template = $this->Model->readOne('email_templates', ['id' => $id]);
        
        if (!$template) {
            $this->session->set_flashdata('error', 'Template non trouvé');
            redirect(base_url('Email_templates'));
            return;
        }

        // Suppression physique
        $rsp = $this->Model->delete('email_templates', ['id' => $id]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Template supprimé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('Email_templates'));
    }

    public function toggle_status()
    {
        $id = $this->input->post('id');
        $is_active = $this->input->post('is_active');
        
        $status = ($is_active == 1) ? 0 : 1;
        $rsp = $this->Model->update('email_templates', ['id' => $id], ['is_active' => $status]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Statut mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour du statut.');
        }
        redirect(base_url('Email_templates'));    
    }

    public function preview($id)
    {
        $template = $this->Model->readOne('email_templates', ['id' => $id]);
        
        if (!$template) {
            show_404();
        }
        
        // Décoder les variables
        $variables = json_decode($template['variables'], true);
        
        // Variables d'exemple pour la prévisualisation
        $sample_data = [
            'full_name' => 'Jean Dupont',
            'email' => 'jean.dupont@exemple.com',
            'firm_name' => 'Entreprise ABC',
            'organization' => 'Organisation XYZ',
            'login_url' => base_url('login'),
            'reset_link' => base_url('reset-password/token-exemple'),
            'year' => date('Y'),
            'type' => 'Investisseur',
            'id' => '123',
            'date' => date('d/m/Y H:i'),
            'admin_url' => base_url('Dashboard'),
            'details' => '<tr><td>Exemple</td><td>Valeur exemple</td></tr>'
        ];
        
        // Remplacer les variables dans le sujet et le corps
        $subject = $template['subject'];
        $body = $template['body'];
        
        foreach ($sample_data as $key => $value) {
            $subject = str_replace('{' . $key . '}', $value, $subject);
            $body = str_replace('{' . $key . '}', $value, $body);
        }
        
        echo '<h2>Aperçu du template : ' . htmlspecialchars($template['template_name']) . '</h2>';
        echo '<h3>Sujet : ' . htmlspecialchars($subject) . '</h3>';
        echo '<hr>';
        echo $body;
    }

    public function duplicate($id)
    {
        $template = $this->Model->readOne('email_templates', ['id' => $id]);
        
        if (!$template) {
            $this->session->set_flashdata('error', 'Template non trouvé');
            redirect(base_url('Email_templates'));
            return;
        }
        
        // Créer une copie avec une nouvelle clé
        $new_key = $template['template_key'] . '_copy_' . date('YmdHis');
        $new_name = $template['template_name'] . ' (copie)';
        
        $data = [
            'template_key' => $new_key,
            'template_name' => $new_name,
            'subject' => $template['subject'],
            'body' => $template['body'],
            'variables' => $template['variables'],
            'description' => $template['description'],
            'category' => $template['category'],
            'is_active' => 0,
            'created_by' => $this->session->userdata('user_id'),
            'updated_by' => $this->session->userdata('user_id')
        ];
        
        $rsp = $this->Model->create('email_templates', $data);
        
        if ($rsp) {
            $this->session->set_flashdata('success', 'Template dupliqué avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la duplication.');
        }
        redirect(base_url('Email_templates'));
    }

    /**
     * API pour tester l'envoi d'email
     */
    public function test_send()
    {
        $id = $this->input->post('id');
        $test_email = $this->input->post('test_email');
        
        if (empty($test_email) || !filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Email de test invalide']);
            return;
        }
        
        $template = $this->Model->readOne('email_templates', ['id' => $id]);
        
        if (!$template) {
            echo json_encode(['success' => false, 'message' => 'Template non trouvé']);
            return;
        }
        
        // Charger la bibliothèque email
        $this->load->library('email');
        
        // Configurer l'email
        $this->email->from('no-reply@africangreenfarmers.com', 'African Green Farmers');
        $this->email->to($test_email);
        $this->email->subject($template['subject']);
        
        // Variables de test
        $test_vars = [
            '{full_name}' => 'Jean Dupont',
            '{email}' => $test_email,
            '{firm_name}' => 'Entreprise Test',
            '{organization}' => 'Organisation Test',
            '{year}' => date('Y'),
            '{date}' => date('d/m/Y H:i'),
            '{id}' => 'TEST123',
            '{type}' => 'Test',
            '{login_url}' => base_url('login'),
            '{reset_link}' => base_url('reset-password/test'),
            '{admin_url}' => base_url('Dashboard')
        ];
        
        // Remplacer les variables
        $body = str_replace(array_keys($test_vars), array_values($test_vars), $template['body']);
        $this->email->message($body);
        
        if ($this->email->send()) {
            echo json_encode(['success' => true, 'message' => 'Email de test envoyé avec succès à ' . $test_email]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'envoi : ' . $this->email->print_debugger()]);
        }
    }
}