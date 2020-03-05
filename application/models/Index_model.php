<?php

class Index_model extends Platform_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function createIndex($index = array())
    {
        $index['date_add'] = $this->date;
        $index['date_upd'] = $this->date;

        $this->db->insert('index', $index);
    }

    public function updateIndex($id, $update = array()) {

        $update['date_upd'] = $this->date;
        $column = 'products';

        $this->db->set($update);
        $this->db->where('source_id', $id);
        $this->db->where('source_type', $update['source_type']);
        $this->db->update('index');
    }

    public function search($query)
    {
        $this->db->select('id, icon, class, module, title, link');
        $this->db->from('index');
        $this->db->like('title', $query);
        $this->db->where('flag', 1);
        $this->db->limit(30);
        $query = $this->db->get();
        return $query->result();
    }
    
    public function deleteIndex($id, $type) {
        $upd  = [];
        $upd['source_type'] = $type;
        $upd['flag'] = 0;

        $this->updateIndex($id, $upd);
    }

}
