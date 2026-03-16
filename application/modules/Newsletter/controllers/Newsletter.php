<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Newsletter extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        is_admin();
    }
    
    public function index()
    {
        // Récupérer toutes les inscriptions newsletter
        $data['Newsletter'] = $this->Model->read('newsletter', [], 'id_newsletter', 'DESC');
        
        // Statistiques
        $data['total'] = count($data['Newsletter']);
        $data['avec_email'] = count(array_filter($data['Newsletter'], fn($n) => !empty($n['email'])));
        $data['avec_telephone'] = count(array_filter($data['Newsletter'], fn($n) => !empty($n['telephone'])));
        
        $this->load->view('Newsletter_View', $data);
    }

    // Export CSV
    public function export_csv()
    {
        $Newsletter = $this->Model->read('newsletter', [], 'id_newsletter', 'DESC');
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="Newsletter_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // BOM UTF-8 pour Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // En-têtes
        fputcsv($output, ['ID', 'Email', 'Téléphone', 'Date d\'inscription'], ';');
        
        foreach ($Newsletter as $n) {
            fputcsv($output, [
                $n['id_newsletter'],
                $n['email'],
                $n['telephone'] ?? '',
                date('d/m/Y H:i', strtotime($n['date_inscription']))
            ], ';');
        }
        
        fclose($output);
        exit;
    }

    // Suppression AJAX
    public function delete()
    {
        header('Content-Type: application/json');
        
        $id = $this->input->post('id');
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
            return;
        }

        $rsp = $this->Model->delete('newsletter', ['id_newsletter' => $id]);

        echo json_encode([
            'success' => (bool)$rsp,
            'message' => $rsp ? 'Inscription supprimée' : 'Erreur lors de la suppression'
        ]);
    }

    // Suppression multiple
    public function delete_multiple()
    {
        header('Content-Type: application/json');
        
        $ids = $this->input->post('ids');
        
        if (empty($ids) || !is_array($ids)) {
            echo json_encode(['success' => false, 'message' => 'Aucun ID sélectionné']);
            return;
        }

        $deleted = 0;
        foreach ($ids as $id) {
            if ($this->Model->delete('newsletter', ['id_newsletter' => $id])) {
                $deleted++;
            }
        }

        echo json_encode([
            'success' => true,
            'message' => $deleted . ' inscription(s) supprimée(s)'
        ]);
    }
}