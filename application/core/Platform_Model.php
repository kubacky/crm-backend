<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Platform_Model extends CI_Model
{

    protected $date;

    protected $u_id;

    protected $event;

    public function __construct()
    {
        parent::__construct();
        $this->u_id = $this->ion_auth->user()->row()->id;

        $this->load->database();
        $this->date = date('Y-m-d H:i:s');
    }

    protected function updateDate($table, $where)
    {
        $this->db->set('date_upd', $this->date);
        $this->db->where($table . '_id', $where);
        $this->db->update($table);
    }

    protected function getOnce()
    {

        $query = $this->db->get();
        $result = $query->result();

        if (!empty($result)) {
            return $result[0];
        } else {
            return false;
        }
    }

}
