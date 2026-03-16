<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Roles extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        if ($this->session->userdata('logged_in') !== TRUE) {
         redirect('Admin');
        }
    }
    
    public function index()
    {
        $data['roles'] = $this->Model->read('roles', null, 'id');
        $this->load->view('Roles_View', $data);
    }

    function ChangeStatus(){
        // Les rôles n'ont pas de champ IsActive, on utilise niveau comme alternative
        // ou on peut simplement retourner une erreur
        $id = $this->input->post('id');
        $sms['sms'] = '<div class="alert alert-warning fade show mt-1 message" role="alert">
                         <strong>Info!</strong> Les rôles ne peuvent pas être désactivés, seulement modifiés ou supprimés.
                     </div>';
        $this->session->set_flashdata($sms);
        redirect(base_url('Roles'));    
    }

    function RoleDetail($roleDetail){
        $id = explode('_', $roleDetail);
        $data['detail'] = $this->Model->readOne('roles', ['id' => $id[0]]);
        $this->load->view('RoleDetail_View', $data);
    }

    function Create(){
        $nom = $this->input->post('nom');
        $slug = $this->create_slug($nom);
        $description = $this->input->post('description');
        $niveau = $this->input->post('niveau');

        $data = array(
            'nom' => $nom,
            'slug' => $slug,
            'description' => $description,
            'niveau' => $niveau
        );
        
        $rsp = $this->Model->create('roles', $data);

        if ($rsp) {
            $sms['sms'] = '<div class="alert alert-success fade show mt-1 message" role="alert">
                             Rôle créé avec succès.
                         </div>';
        } else {
            $sms['sms'] = '<div class="alert alert-danger fade show mt-1 message" role="alert">
                             <strong>Erreur!</strong> Une erreur est survenue, contactez l\'administrateur.
                         </div>';
        }
        $this->session->set_flashdata($sms);
        redirect(base_url('Roles'));
    }

    function Update(){
        $id = $this->input->post('id');
        $nom = $this->input->post('nom');
        $slug = $this->input->post('slug');
        $description = $this->input->post('description');
        $niveau = $this->input->post('niveau');

        $data = array(
            'nom' => $nom,
            'slug' => $slug,
            'description' => $description,
            'niveau' => $niveau
        );

        $rsp = $this->Model->update('roles', ['id' => $id], $data);

        if ($rsp) {
            $sms['sms'] = '<div class="alert alert-success fade show mt-1 message" role="alert">
                             Rôle mis à jour avec succès.
                         </div>';
        } else {
            $sms['sms'] = '<div class="alert alert-danger fade show mt-1 message" role="alert">
                             <strong>Erreur!</strong> Une erreur est survenue, contactez l\'administrateur.
                         </div>';
        }
        $this->session->set_flashdata($sms);
        redirect(base_url('Roles'));
    }

    function Delete(){
        $id = $this->input->post('id');
        
        // Vérifier si des utilisateurs utilisent ce rôle
        $users_with_role = $this->Model->readOne('users', ['role_id' => $id]);
        
        if ($users_with_role) {
            $sms['sms'] = '<div class="alert alert-warning fade show mt-1 message" role="alert">
                             <strong>Impossible!</strong> Ce rôle est attribué à des utilisateurs. Veuillez d\'abord réassigner ces utilisateurs.
                         </div>';
        } else {
            $rsp = $this->Model->delete('roles', ['id' => $id]);
            if ($rsp) {
                $sms['sms'] = '<div class="alert alert-success fade show mt-1 message" role="alert">
                                 Rôle supprimé avec succès.
                             </div>';
            } else {
                $sms['sms'] = '<div class="alert alert-danger fade show mt-1 message" role="alert">
                                 <strong>Erreur!</strong> Une erreur est survenue, contactez l\'administrateur.
                             </div>';
            }
        }
        
        $this->session->set_flashdata($sms);
        redirect(base_url('Roles'));
    }

    // Fonction utilitaire pour créer un slug
    private function create_slug($string) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
        return $slug;
    }
}