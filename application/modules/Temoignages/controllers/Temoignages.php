<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Temoignages extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        is_admin();
        $this->load->helper('form');
        $this->load->library('form_validation');
    }
    
    public function index()
    {
        $data['temoignages'] = $this->Model->read('temoignages', [], 'id_temoignage', 'DESC');
        $this->load->view('Temoignages_View', $data);
    }

    private function get_youtube_id($url) {
        $shortUrlRegex = '/youtu.be\/([a-zA-Z0-9_-]+)\??/i';
        $longUrlRegex = '/youtube.com\/((?:embed)|(?:watch))((?:\?v\=)|(?:\/))([a-zA-Z0-9_-]+)/i';
        if (preg_match($longUrlRegex, $url, $matches)) {
            if (!empty($matches[3])) return $matches[3];
        }
        if (preg_match($shortUrlRegex, $url, $matches)) {
            if (!empty($matches[1])) return $matches[1];
        }
        return '';
    }

    private function fetch_youtube_info($url) {
        $video_id = $this->get_youtube_id($url);
        if (empty($video_id)) return null;

        $oembed_url = "https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v=" . $video_id . "&format=json";
        $json = @file_get_contents($oembed_url);
        if ($json) {
            $data = json_decode($json, true);
            return [
                'titre' => $data['title'] ?? '',
                'miniature' => "https://img.youtube.com/vi/" . $video_id . "/hqdefault.jpg"
            ];
        }
        return [
            'titre' => '',
            'miniature' => "https://img.youtube.com/vi/" . $video_id . "/hqdefault.jpg"
        ];
    }

    function ChangeStatus(){
        $id = $this->input->post('id');
        $est_approuve = $this->input->post('est_approuve');
        
        $status = ($est_approuve == 1) ? 0 : 1;
        $rsp = $this->Model->update('temoignages', ['id_temoignage' => $id], ['est_approuve' => $status]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Statut mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour du statut.');
        }
        redirect(base_url('Temoignages'));    
    }

    function Create(){
        $this->form_validation->set_rules('video_url', 'Lien YouTube', 'required|valid_url');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Temoignages'));
            return;
        }

        $video_url = $this->input->post('video_url');
        $yt_info = $this->fetch_youtube_info($video_url);

        $data = array(
            'titre' => $yt_info && !empty($yt_info['titre']) ? $yt_info['titre'] : 'Témoignage Vidéo',
            'video_url' => $video_url,
            'miniature' => $yt_info ? $yt_info['miniature'] : '',
            'est_approuve' => $this->input->post('est_approuve') ? 1 : 0
        );

        if (!empty($_FILES['miniature']['name'])) {
            $photo = $this->upload_image($_FILES['miniature']['tmp_name'], $_FILES['miniature']['name']);
            if ($photo !== NULL) {
                $data['miniature'] = $photo;
            }
        }

        $rsp = $this->Model->create('temoignages', $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Témoignage vidéo créé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la création.');
        }
        redirect(base_url('Temoignages'));
    }

    function Update(){
        $id = $this->input->post('id_temoignage');
        
        $this->form_validation->set_rules('video_url', 'Lien YouTube', 'required|valid_url');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(base_url('Temoignages'));
            return;
        }

        $video_url = $this->input->post('video_url');
        $old = $this->Model->read_one('temoignages', ['id_temoignage' => $id]);

        $data = array(
            'video_url' => $video_url,
            'est_approuve' => $this->input->post('est_approuve') ? 1 : 0
        );

        if ($video_url !== ($old['video_url'] ?? '')) {
            $yt_info = $this->fetch_youtube_info($video_url);
            if ($yt_info) {
                $data['titre'] = $yt_info['titre'];
                $data['miniature'] = $yt_info['miniature'];
            }
        }

        if (!empty($_FILES['miniature']['name'])) {
            $photo = $this->upload_image($_FILES['miniature']['tmp_name'], $_FILES['miniature']['name']);
            if ($photo !== NULL) {
                if (!empty($old['miniature']) && strpos($old['miniature'], 'uploads/') !== false && file_exists(FCPATH . $old['miniature'])) {
                    @unlink(FCPATH . $old['miniature']);
                }
                $data['miniature'] = $photo;
            }
        }

        $rsp = $this->Model->update('temoignages', ['id_temoignage' => $id], $data);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Témoignage vidéo mis à jour avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la mise à jour.');
        }
        redirect(base_url('Temoignages'));
    }

    function Delete(){
        $id = $this->input->post('id');
        $old = $this->Model->read_one('temoignages', ['id_temoignage' => $id]);
        
        if (!empty($old['miniature']) && strpos($old['miniature'], 'uploads/') !== false && file_exists(FCPATH . $old['miniature'])) {
            @unlink(FCPATH . $old['miniature']);
        }

        $rsp = $this->Model->delete('temoignages', ['id_temoignage' => $id]);

        if ($rsp) {
            $this->session->set_flashdata('success', 'Témoignage supprimé avec succès.');
        } else {
            $this->session->set_flashdata('error', 'Une erreur est survenue lors de la suppression.');
        }
        redirect(base_url('Temoignages'));
    }
}
