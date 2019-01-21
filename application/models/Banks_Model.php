<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Banks_Model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function banks($bank_id = '')
    {
        if (!empty($bank_id)) {
            $this->db->where('bank_id', $bank_id);
        }
        $query = $this->db->get('tbl_banks');
        if (!empty($bank_id)) {
            return $query->row();
        } else {
            return $query->result();
        }
    }

    public function bin_banks()
    {
        return $this->db->query("SELECT DISTINCT bank_code, bank_name FROM tbl_bins")->result();
    }

    public function default_bank_empty()
    {
        $this->db->set('default_bank', 0)->update('tbl_banks');
        return true;
    }

    public function add_bank($data)
    {
        $this->db->insert('tbl_banks', $data);
        return $this->db->insert_id();
    }

    public function edit_bank($bank_id, $data)
    {
        if (!empty($bank_id) && !empty($data)) {
            $this->db->where('bank_id', $bank_id)->update('tbl_banks', $data);
            return $bank_id;
        } else {
            return 0;
        }
    }

    public function delete_bank($bank_id)
    {
        $this->db->where('bank_id', $bank_id)->delete('tbl_banks');
        return true;
    }
}