<?php

class Properties_model extends Platform_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getProperties()
    {
        $this->db->select('property_id as id, type, name, sortable, required');
        $this->db->from('properties');
        $this->db->where('flag', 1);
        $this->db->order_by('name', 'ASC');

        $query = $this->db->get();
        $result = $query->result();

        $c = count($result);
        for ($i = 0; $i < $c; $i++) {
           $result[$i]->required = ($result[$i]->required == '0') ? false : true;
           $result[$i]->sortable = ($result[$i]->sortable == '0') ? false : true;

            $result[$i]->values = $this->getPropertyValues($result[$i]->id);
        }

        return $result;
    }

    public function getProperty($property_id)
    {
        $this->db->select('property_id as id, type, name');
        $this->db->from('properties');
        $this->db->where('property_id', $property_id);

        return $this->getOnce();
    }

    public function getPropertyByName($name)
    {
        $this->db->select('property_id as id');
        $this->db->from('properties');
        $this->db->where('name', $name);
        $this->db->where('flag', 1);
        $this->db->limit(1);

        return $this->getOnce();
    }

    public function getPropertyValues($property_id)
    {
        $this->db->select('value_id as id, value');
        $this->db->from('values');
        $this->db->where('property_id', $property_id);
        $this->db->where('flag', 1);
        $this->db->order_by('value', 'ASC');

        $query = $this->db->get();
        return $query->result();
    }

    public function addProperty($property = array())
    {
        $property['user_id'] = $this->u_id;
        $property['date_add'] = $this->date;
        $property['date_upd'] = $this->date;

        $this->db->insert('properties', $property);
        return $this->db->insert_id('properties');
    }

    public function addValue($value = array())
    {
        $value['user_id'] = $this->u_id;
        $value['date_add'] = $this->date;
        $value['date_upd'] = $this->date;

        $this->db->insert('values', $value);
        return $this->db->insert_id('values');
    }

    public function updateProperty($property_id, $property = array())
    {
        $property['date_upd'] = $this->date;

        $this->db->set($property);
        $this->db->where('property_id', $property_id);
        $this->db->update('properties');
    }

    public function updateValue($value_id, $value = array())
    {
        $property['date_upd'] = $this->date;

        $this->db->set($value);
        $this->db->where('value_id', $value_id);
        $this->db->update('values');
    }

    public function deleteProperty($property_id)
    {
        $property = [];
        $property['flag'] = 0;

        $this->updateProperty($property_id, $property);
    }

    public function deleteValue($value_id)
    {
        $value = [];
        $value['flag'] = 0;

        $this->updateValue($value_id, $value);
    }

    public function getRequiredProperties()
    {
        $this->db->select('properties.property_id as id, name');
        $this->db->from('properties');
        $this->db->where('required', 1);
        $this->db->where('flag', 1);
        $this->db->order_by('name', 'ASC');

        $query = $this->db->get();
        return $query->result();

    }

    public function updateDeviceProperty($property_id, $property = array())
    {
        $property['date_upd'] = $this->date;

        $this->db->set($property);
        $this->db->where('device_property_id', $property_id);
        $this->db->update('device_properties');
    }

    public function clearDeviceProperties($device_id)
    {
        $upd = [];
        $upd['date_upd'] = $this->date;
        $upd['flag'] = 0;

        $this->db->set($upd);
        $this->db->where('device_id', $device_id);
        $this->db->update('device_properties');
    }

    public function addDeviceProperty($property = array())
    {
        $property['user_id'] = $this->u_id;
        $property['date_add'] = $this->date;
        $property['date_upd'] = $this->date;

        $this->db->insert('device_properties', $property);
        return $this->db->insert_id('device_properties');
    }

    public function getDeviceProperties($device_id)
    {
        $this->db->select('device_property_id as devPropId, device_properties.property_id as propertyId, '
            . 'device_properties.value as textValue, properties.name as name, values.value as value, '
            . 'values.value_id as valueId');
        $this->db->from('properties, device_properties, values');
        $this->db->where('device_properties.flag', 1);
        $this->db->where('properties.property_id = device_properties.property_id');
        $this->db->where('values.value_id = device_properties.value_id');
        $this->db->where('device_id', $device_id);

        $query = $this->db->get();
        return $query->result();
    }

}
