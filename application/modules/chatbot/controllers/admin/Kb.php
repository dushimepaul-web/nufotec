<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin - Knowledge Base CRUD
 */
class Kb extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        is_admin();

        $this->load->model('chatbot/Kb_model');
        $this->load->library('chatbot/Ai_service');
    }

    public function index()
    {
        $data['articles'] = $this->Kb_model->get_all();
        $this->load->view('admin/chatbot/kb_list', $data);
    }

    public function create()
    {
        if ($this->input->method() === 'post') {
            $id = $this->Kb_model->create(array(
                'title'    => $this->input->post('title'),
                'content'  => $this->input->post('content'),
                'category' => $this->input->post('category'),
                'active'   => $this->input->post('active') ? 1 : 0
            ));

            $this->_generate_and_save_embedding($id);

            $this->session->set_flashdata('success', 'Article ajoute a la base de connaissances.');
            redirect('admin/chatbot/kb');
        }

        $this->load->view('admin/chatbot/kb_form', array('article' => null));
    }

    public function edit($id)
    {
        $article = $this->Kb_model->get($id);
        if (!$article) show_404();

        if ($this->input->method() === 'post') {
            $this->Kb_model->update($id, array(
                'title'    => $this->input->post('title'),
                'content'  => $this->input->post('content'),
                'category' => $this->input->post('category'),
                'active'   => $this->input->post('active') ? 1 : 0
            ));

            $this->_generate_and_save_embedding($id);

            $this->session->set_flashdata('success', 'Article mis a jour.');
            redirect('admin/chatbot/kb');
        }

        $this->load->view('admin/chatbot/kb_form', array('article' => $article));
    }

    public function delete($id)
    {
        $this->Kb_model->delete($id);
        $this->session->set_flashdata('success', 'Article supprime.');
        redirect('admin/chatbot/kb');
    }

    public function regenerate_all_embeddings()
    {
        $articles = $this->Kb_model->get_all();
        foreach ($articles as $article) {
            $this->_generate_and_save_embedding($article['id']);
        }
        $this->session->set_flashdata('success', count($articles) . ' article(s) reindexe(s).');
        redirect('admin/chatbot/kb');
    }

    protected function _generate_and_save_embedding($id)
    {
        $article = $this->Kb_model->get($id);
        if (!$article) return;

        $text = $article['title'] . "\n" . $article['content'];
        $embedding = $this->ai_service->get_embedding($text);

        if ($embedding !== false) {
            $this->Kb_model->save_embedding($id, $embedding);
        } else {
            log_message('error', 'Impossible de generer l\'embedding pour l\'article KB #' . $id);
        }
    }
}
