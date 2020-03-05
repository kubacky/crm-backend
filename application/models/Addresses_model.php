<?php

class Addresses_model extends Platform_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function addAddress($address)
    {
        $province_id = $this->getProvinceId($address['address_postcode']);
        $address['address_province_id'] = $province_id;
        $address['date_add'] = $this->date;
        $address['date_upd'] = $this->date;
        $this->db->insert('addresses', $address);
        return $this->db->insert_id('addresses');
    }

    public function getAddress($address_id)
    {
        $this->db->select('crm_addresses_id as id, address_street as street, '
            . 'address_postcode as postcode, address_city as city, province_name as province, '
            . 'address_lat as lat, address_lng as lng');
        $this->db->from('addresses, provinces');
        $this->db->where('crm_addresses_id', $address_id);
        $this->db->where('province_id = address_province_id');
        $this->db->limit(1);

        $addr = $this->getOnce();
        return $this->setCoordinatesAsFloat($addr);
    }

    public function setCoordinatesAsFloat($address) {
      $address->lat = floatval($address->lat);
      $address->lng = floatval($address->lng);

      return $address;
    }

    public function getAllPostcodes()
    {
        $this->db->select('crm_addresses_id as id, address_postcode as postcode');
        $this->db->from('addresses');
        $this->db->where('flag', 1);

        $query = $this->db->get();

        return $query->result();
    }

    private function getProvinceId($postcode)
    {
        $postcode = substr(getPlainText($postcode, ''),0, 3);

        $this->db->select('province_id');
        $this->db->from('postcodes');
        $this->db->like('postcode', $postcode, 'after');
        $this->db->limit(1);

        $query = $this->db->get();
        $result = $query->result();

        if (!empty($result)) {
            return $result[0]->province_id;
        }
        return false;
    }

    public function getProvinces()
    {
        $this->db->select('province_id as id, province_name as name');
        $this->db->from('provinces');
        $this->db->order_by('province_name', 'ASC');

        $query = $this->db->get();
        $result = $query->result();

        $c = count($result);
        for ($i = 0; $i < $c; $i++) {
            $p_id = $result[$i]->id;
            $result[$i]->count = $this->getProvinceCount($p_id);
        }

        return $result;
    }

    private function getProvinceCount($province_id)
    {
        $this->db->select('count(id) as count');
        $this->db->from('customers, addresses');
        $this->db->where('customers.flag', 1);
        $this->db->where('parent_id', 0);
        $this->db->where('address_province_id', $province_id);
        $this->db->where('crm_addresses_id = customers.address_id');

        $return = $this->getOnce();
        return intval($return->count);
    }

    public function updateAddress($address_id, $address = array())
    {
        $province_id = $this->getProvinceId($address['address_postcode']);

        echo $province_id . "\n";

        $address['address_province_id'] = $province_id;
        $address['address_user_id'] = $this->u_id;
        $address['date_upd'] = $this->date;


        $this->db->set($address);
        $this->db->where('crm_addresses_id', $address_id);
        $this->db->update('addresses');
    }

    private function getProvince($province_id)
    {
        $this->db->select('province_id as id, province_name as name');
        $this->db->from('provinces');
        $this->db->where('province_id', $province_id);
        $this->db->limit(1);

        return $this->getOnce();
    }

}
