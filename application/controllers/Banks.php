<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Banks extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Banks_Model');
    }

    public function index()
    {
        $data['msg'] = $this->session->flashdata('message');
        $data['msg_type'] = $this->session->flashdata('message_type');
        $data['bin_banks'] = $this->Banks_Model->bin_banks();
        $data['banks'] = $this->Banks_Model->banks();
        $this->load->view('banks', $data);
        $this->load->view('banks_js');
    }

    public function save()
    {
        if ($this->input->post()) {
            if ($this->input->post('default_bank') == 1) {
                $this->Banks_Model->default_bank_empty();
            }
            $data = array(
                'name' => $this->input->post('name'),
                'method' => $this->input->post('method'),
                'model' => $this->input->post('model'),
                'status' => $this->input->post('status'),
                'bank_code' => $this->input->post('bank_code'),
                'default_bank' => $this->input->post('default_bank')
            );
            if (!empty($this->input->post('bank_id'))) {
                $this->Banks_Model->edit_bank($this->input->post('bank_id'), $data);
                $this->session->set_flashdata('message', "Banka Başarıyla Güncellendi!");
                $this->session->set_flashdata('message_type', 'success');
                redirect(base_url('banks'));
            } else {
                $this->Banks_Model->add_bank($data);
                $this->session->set_flashdata('message', "Banka Başarıyla Eklendi!");
                $this->session->set_flashdata('message_type', 'success');
                redirect(base_url('banks'));
            }
        }
    }

    public function bank_detail()
    {
        if ($this->input->is_ajax_request()) {
            $data = $this->Banks_Model->banks($this->input->post('bank_id'));
            $this->output->set_content_type('application/json')->set_output(json_encode($data));
        }
    }

    public function delete_bank()
    {
        if ($this->input->is_ajax_request()) {
            $this->Banks_Model->delete_bank($this->input->post('bank_id'));
            $this->output->set_content_type('application/json')->set_output(json_encode(array('msg' => "Seçili banka başarıyla silindi.")));
        }
    }

    public function bank_info()
    {
        if ($this->input->is_ajax_request()) {
            $bank = $this->Banks_Model->banks($this->input->post('bank_id'));
            $data['bank_id'] = $this->input->post('bank_id');
            $data['bank_name'] = $bank->name;
            $data['method'] = $bank->method;
            $data['bank_info'] = json_decode($bank->info);
            $this->output->set_content_type('application/json')->set_output(json_encode($data));
        }
    }

    public function edit_info()
    {
        $this->Banks_Model->edit_bank($this->input->post('bank_id'), array('info' => json_encode($this->input->post())));
        $this->session->set_flashdata('message', "Banka Başarıyla Güncellendi!");
        $this->session->set_flashdata('message_type', 'success');
        redirect(base_url('banks'));
    }
}
