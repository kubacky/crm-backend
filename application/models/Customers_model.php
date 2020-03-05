<?php

class Customers_model extends Platform_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getCustomers($parent_id = 0)
    {
        $this->db->select('crm_customers.id, name, plain_name, alias, plain_alias, user_id as userId, '
            . 'phone, mail, products_list_id as listId, address_id as addressId, date_add, parent_id as parentId');
        $this->db->from('customers');
        $this->db->where('flag', 1);
        $this->db->where('parent_id', $parent_id);
        $this->db->order_by('name', 'ASC');

        $query = $this->db->get();
        return $query->result();
    }

    public function getAllCustomers()
    {
        $this->db->select('crm_customers.id, name, plain_name, alias, plain_alias, user_id as userId, '
            . 'phone, mail, products_list_id as listId, address_id as addressId, date_add, parent_id as parentId');
        $this->db->from('customers');
        $this->db->where('flag', 1);
        $this->db->order_by('name', 'ASC');

        $query = $this->db->get();
        return $query->result();
    }

    public function getCustomersByProvinces($provinces = array())
    {
        $this->db->select('crm_customers.id, name, plain_name, alias, plain_alias, '
            . 'phone, mail, products_list_id as listId, address_id as addressId');
        $this->db->from('customers, addresses');
        $this->db->where('customers.flag', 1);
        $this->db->where('parent_id', 0);
        $this->db->where_in('address_province_id', $provinces);
        $this->db->where('crm_addresses_id = customers.address_id');
        $this->db->order_by('name', 'ASC');

        $query = $this->db->get();
        return $query->result();
    }


    public function getCustomersByGroups($groups = array(), $parent_id = 0)
    {
        $this->db->select('crm_customers.id, name, plain_name, alias, plain_alias, '
            . 'address_id as addressId');
        $this->db->from('customers, grouped');
        $this->db->where('customers.flag', 1);
        $this->db->where('parent_id', $parent_id);
        $this->db->where('crm_customers.id = link_id');
        $this->db->where_in('group_id', $groups);
        $this->db->group_by('crm_customers.id');
        $this->db->order_by('name', 'ASC');

        $query = $this->db->get();
        return $query->result();
    }

/*
    public function getCustomersByGroups($group_id, $parent_id = 0)
    {
        $this->db->select('crm_customers.id, name, plain_name, alias, plain_alias, '
            . 'address_id as addressId');
        $this->db->from('customers, grouped');
        $this->db->where('customers.flag', 1);
        $this->db->where('parent_id', $parent_id);
        $this->db->where('crm_customers.id = link_id');
        $this->db->where('group_id', $group_id);
        $this->db->group_by('crm_customers.id');
        $this->db->order_by('name', 'ASC');

        $query = $this->db->get();
        return $query->result();
    }
    **/

    public function findCustomers($query)
    {
        $this->db->select('crm_customers.id, name, plain_name, alias, plain_alias, '
            . 'phone, mail, products_list_id as listId, address_id as addressId');
        $this->db->from('customers');
        $this->db->like('name', $query);
        $this->db->where('flag', 1);
        $this->db->where('parent_id', 0);
        $this->db->or_like('alias', $query);
        $this->db->where('flag', 1);
        $this->db->where('parent_id', 0);
        $this->db->order_by('name', 'ASC');

        $query = $this->db->get();
        return $query->result();
    }

    public function checkAlias($query)
    {
        $this->db->select('crm_customers.id as id');
        $this->db->from('customers');
        $this->db->where('plain_name', $query);
        $this->db->where('flag', 1);
        $this->db->limit(1);

        $query = $this->db->get();
        return $query->result();
    }

    public function addCustomer($customer = array())
    {
        $customer['date_add'] = $this->date;
        $customer['date_upd'] = $this->date;
        $this->db->insert('customers', $customer);

        $id = $this->db->insert_id('customers');

        return $id;

    }

    public function updateCustomer($customer_id, $customer = array())
    {
        $customer['date_upd'] = $this->date;
        $this->db->set($customer);
        $this->db->where('id', $customer_id);
        $this->db->update('customers');

    }

    public function getCustomer($customer_id)
    {
        $this->db->select('id, name, alias, tax_no as taxNo, phone, mail, '
            . 'address_id as addressId, products_list_id as listId, '
            . 'parent_id as parentId, discount');
        $this->db->from('customers');
        $this->db->where('id', $customer_id);
        $this->db->limit(1);

        return $this->getOnce();
    }

    public function deleteCustomer($customer_id)
    {
        $upd = [];
        $upd['flag'] = 0;

        $this->updateCustomer($customer_id, $upd);
    }

    public function getCustomerByNip($nip)
    {
        $this->db->select('id');
        $this->db->from('customers');
        $this->db->where('tax_no', str_replace(array(' ', '-'), '', $nip));
        $this->db->limit(1);
        $query = $this->db->get();
        $result = $query->result();
        if (!empty($result)) {
            return $result[0]->id;
        }
        return null;
    }
}
