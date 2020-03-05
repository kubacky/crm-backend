<?php

class Contacts_model extends Platform_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function addContact($contact = array())
    {
        $contact['contact_user_id'] = $this->u_id;
        $contact['date_add'] = $this->date;
        $contact['date_upd'] = $this->date;
        $this->db->insert('contacts', $contact);
        return $this->db->insert_id('contacts');
    }

    public function updateContact($contact_id, $contact = array())
    {
        $contact['contact_user_id'] = $this->u_id;
        $contact['date_upd'] = $this->date;

        $this->db->set($contact);
        $this->db->where('crm_contacts_id', $contact_id);
        $this->db->update('contacts');
    }

    public function getContact($id, $type = 'customer')
    {
        $col = 'contact_customer_id';

        if ($type == 'product') {
            $col = 'contact_product_id';
        }

        $this->db->select('crm_contacts_id as id, contact_name as name, '
            . 'contact_workplace as workplace, contact_phone as phone, '
            . 'contact_mail as mail');
        $this->db->from('contacts');
        $this->db->where($col, $id);
        $this->db->where('flag', 1);
        $this->db->limit(1);

        return $this->getOnce();
    }

    public function getRawContact($contact_id)
    {
        $this->db->select('crm_contacts_id as id, contact_name as name, '
            . 'contact_workplace as workplace, contact_phone as phone, '
            . 'contact_mail as mail');
        $this->db->from('contacts');
        $this->db->where('crm_contacts_id', $contact_id);
        $this->db->where('flag', 1);
        $this->db->limit(1);

        $query = $this->db->get();
        $result = $query->result();

        if (!empty($result)) {
            return $result[0];
        }
        return false;
    }

    public function deleteContact($contact_id)
    {
        $upd = [];
        $upd['flag'] = 0;

        $this->updateContact($contact_id, $upd);
    }
}
