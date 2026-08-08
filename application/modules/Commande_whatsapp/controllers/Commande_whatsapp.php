<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Commande_whatsapp extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        is_admin();
        $this->load->model('Commande_whatsapp/Commande_whatsapp_model', 'CommandeModel');
    }

    public function index()
    {
        $data['orders'] = $this->CommandeModel->get_orders();
        $data['stats']  = $this->CommandeModel->get_stats();
        $data['title']  = 'Commandes WhatsApp';
        $this->load->view('Commande_whatsapp_View', $data);
    }

    public function search()
    {
        $q = trim($this->input->get('q'));
        $stats = $this->CommandeModel->get_stats();

        if (empty($q)) {
            $orders = $this->CommandeModel->get_orders();
        } else {
            $orders = $this->CommandeModel->search_orders($q);
        }

        $data['orders'] = $orders;
        $data['stats']  = $stats;
        $data['q']      = $q;
        $data['title']  = 'Recherche commandes';
        $this->load->view('Commande_whatsapp_View', $data);
    }

    public function view_order($id)
    {
        $data['detail'] = $this->CommandeModel->get_order($id);
        $data['stats']  = $this->CommandeModel->get_stats();
        $this->load->view('Commande_whatsappDetail_View', $data);
    }

    public function ChangeStatus()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $this->output->set_content_type('application/json');

        $order_id = $this->input->post('order_id');
        $status   = $this->input->post('status');

        $allowed = array('pending', 'processing', 'completed', 'cancelled');
        if (!in_array($status, $allowed)) {
            echo json_encode(array('success' => false, 'message' => 'Statut invalide'));
            return;
        }

        $updated = $this->CommandeModel->update_status($order_id, $status);

        if ($updated) {
            echo json_encode(array(
                'success' => true,
                'message' => 'Statut mis à jour avec succès',
                'new_status' => $status,
            ));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Erreur lors de la mise à jour'));
        }
    }

    public function Delete()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $this->output->set_content_type('application/json');

        $order_id = $this->input->post('order_id');
        $deleted  = $this->CommandeModel->delete_order($order_id);

        echo json_encode(array(
            'success' => $deleted,
            'message' => $deleted ? 'Commande supprimée' : 'Erreur de suppression',
        ));
    }

    public function export_csv()
    {
        $orders = $this->CommandeModel->get_orders();

        $filename = 'commandes_whatsapp_' . date('Y-m-d_H-i-s') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, array('ID', 'N° Commande', 'Produit', 'Client', 'Téléphone', 'Pays', 'Ville', 'Adresse', 'Notes', 'Montant', 'Statut', 'Date'));

        foreach ($orders as $order) {
            fputcsv($output, array(
                $order['id'],
                'CMD-' . str_pad($order['id'], 6, '0', STR_PAD_LEFT),
                $order['product_title'],
                $order['customer_name'],
                $order['customer_phone'],
                $order['customer_country'],
                $order['customer_city'],
                $order['customer_address'],
                $order['customer_notes'],
                $order['product_price'],
                $order['order_status'],
                $order['created_at'],
            ));
        }
        fclose($output);
        exit;
    }
}