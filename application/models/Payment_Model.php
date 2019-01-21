<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment_Model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function bin_bank($bin_number)
    {
        return $this->db->where('bin_number', $bin_number)->get('tbl_bins')->row();
    }

    public function bank($bank_id, $bank_code = '', $active = '')
    {
        if (!empty($bank_id)) {
            $this->db->where('bank_id', $bank_id);
        }
        if (!empty($bank_code)) {
            $this->db->where('bank_code', $bank_code);
        }
        if (!empty($active)) {
            $this->db->where('status', 1);
        }
        return $this->db->get('tbl_banks')->row();
    }

    public function default_bank()
    {
        return $this->db->where('default_bank', 1)->get('tbl_banks')->row();
    }
}