<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Upload extends MY_Controller {
    
    public function summernote_image() {
        $config['upload_path'] = './uploads/summernote/';
        $config['allowed_types'] = 'gif|jpg|png|jpeg|webp';
        $config['max_size'] = 2048; // 2MB
        $config['encrypt_name'] = true;
        
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }
        
        $this->load->library('upload', $config);
        
        if ($this->upload->do_upload('file')) {
            $data = $this->upload->data();
            echo json_encode([
                'success' => true,
                'url' => base_url('uploads/summernote/' . $data['file_name']),
                'filename' => $data['file_name']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => $this->upload->display_errors()
            ]);
        }
    }
    
    public function delete_image() {
        $src = $this->input->post('src');
        $filename = basename($src);
        $filepath = FCPATH . 'uploads/summernote/' . $filename;
        
        if (file_exists($filepath)) {
            unlink($filepath);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Fichier non trouvé']);
        }
    }
}