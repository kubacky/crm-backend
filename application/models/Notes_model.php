<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Notes_model extends Platform_Model
{

    protected $u_id = 1; //user id

    public function __construct()
    {
        parent::__construct();
    }

    public function addNote($note = array())
    {
        $note['user_id'] = $this->u_id;
        $note['date_add'] = $this->date;
        $note['date_upd'] = $this->date;
        $this->db->insert('notes', $note);
        return $this->db->insert_id('notes');
    }

    public function getNotes($id, $type = 'customer')
    {

        $this->db->select('note_id as id, user_id as uId, title, '
            . 'content, class, date_add as date, user_id as userId, '
            . 'first_name as firstName, last_name as lastName, ' 
            . 'source_id as sourceId, source_type as type');
        $this->db->from('notes, users');
        $this->db->where('source_id', $id);
        $this->db->where('source_type', $type);
        $this->db->where('users.id = user_id');
        $this->db->where('flag', 1);
        $this->db->order_by('date_add', 'DESC');

        //echo $this->db->get_compiled_select();

        $query = $this->db->get();
        $result = $query->result();

        $c = count($result);
        for ($i = 0; $i < $c; $i++) {
            $result[$i]->date = formatDate($result[$i]->date, 'j n Y H:i');
            $result[$i]->locked = ($result[$i]->uId == $this->u_id) ? false : true;
        }

        return $result;
    }

    public function getNote($note_id)
    {
        $this->db->select('note_id as id, title, content, class, '
            . 'date_upd as date, user_id as uId, source_id, source_type');
        $this->db->from('notes');
        $this->db->where('note_id', $note_id);

        return $this->getOnce();
    }

    public function updateNote($note_id, $note = array())
    {
        $note['date_upd'] = $this->date;
        $this->db->set($note);
        $this->db->where('note_id', $note_id);
        $this->db->update('notes');
    }

    public function deleteNote($note_id)
    {
        $note = $this->getNote($note_id);

        if ($note->uId == $this->u_id) {
            $upd = array();
            $upd['flag'] = 0;
            $this->updateNote($note_id, $upd);
        }
    }
}
