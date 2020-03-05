<?php

class Groups_model extends Platform_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getGroups($type = 'customer')
    {
        $this->db->select('groups.group_id as id, groups.name, count(link_id) as count');
        $this->db->from('groups, grouped, customers');
        $this->db->where('source_type', $type);
        $this->db->where('groups.group_id = grouped.group_id');
        $this->db->where('customers.id = grouped.link_id');
        $this->db->where('customers.parent_id', 0);
        $this->db->where('customers.flag', 1);
        $this->db->where('groups.flag', 1);
        $this->db->group_by('grouped.group_id');
        $this->db->order_by('groups.name', 'ASC');

        $query = $this->db->get();
        return $query->result();
    }

    public function clearGroups($id, $type)
    {
        $this->db->where('link_id', $id);
        $this->db->where('group_type', $type);
        $this->db->delete('grouped');
    }

    public function assignToGroups($groups)
    {

        $c = count($groups);
        for ($i = 0; $i < $c; $i++) {
            $this->db->insert('grouped', $groups[$i]);
        }
    }

    public function addGroup($group = array())
    {
        $group['user_id'] = $this->u_id;
        $group['date_add'] = $this->date;
        $group['date_upd'] = $this->date;

        $this->db->insert('groups', $group);
    }

    public function getCustomerGroups($customer_id)
    {
        $this->db->select('name, grouped.group_id as id');
        $this->db->from('groups, grouped');
        $this->db->where('groups.group_id = grouped.group_id');
        $this->db->where('grouped.link_id', $customer_id);
        $this->db->order_by('name', 'ASC');

        $query = $this->db->get();
        return $query->result();
    }

    public function updateGroup($group_id, $group = array())
    {
        $group['date_upd'] = date('Y-m-d H:i:s');

        $this->db->set($group);
        $this->db->where('group_id', $group_id);
        $this->db->update('groups');
    }

    public function removeFromGroup($grouped_id)
    {
        $this->db->where('id', $grouped_id);
        $this->db->delete('grouped');
    }

    public function deleteGroup($group_id)
    {
        $group = array();
        $group['flag'] = 0;

        $this->updateGroup($group_id, $group);
    }

    private function getCustomersCount($groups)
    {
        $c = count($groups);
        for ($i = 0; $i < $c; $i++) {
            $groups[$i]->count = $this->getGroupCount($groups[$i]->id);
        }

        return $groups;
    }

    private function getGroupCount($group_id)
    {
        $this->db->select('count(link_id) as count');
        $this->db->from('grouped, customers');
        $this->db->where('group_id', $group_id);
        $this->db->where('link_id = crm_customers.id');
        $this->db->where('customers.parent_id', 0);
        $this->db->where('customers.flag', 1);
        $this->db->limit(1);

        $query = $this->db->get();
        $result = $query->result();

        return $result[0]->count;
    }

}
