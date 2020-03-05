<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Devices_model extends Platform_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getDevices($start = 0, $count = 60)
    {
        $this->db->select('device_id as id, customer_id as customerId, registry_no as registryNo, comment, '
            . 'purchase_date as purchaseDate, price_gross as price, last_service_date as lastService, '
            . 'service_period as servicePeriod, next_service_date as nextService, date_add, user_id as userId, flag');
        $this->db->from('devices');
        $this->db->where('flag', 1);
        $this->db->order_by('purchase_date', 'DESC');
        $this->db->limit($count, $start);

        $query = $this->db->get();
        $result = $query->result();

        return $this->formatDevices($result);
    }

    public function getShortDevices($start = 0, $count = 100)
    {
        $this->db->select('device_id as id, customer_id as customerId, registry_no as registryNo, '
            . 'purchase_date as purchaseDate, price_gross as price, last_service_date as lastService, '
            . 'next_service_date as nextService, flag');
        $this->db->from('devices');
        $this->db->where('flag !=', 0);
        $this->db->order_by('purchase_date', 'DESC');
        $this->db->limit($count, $start);

        $query = $this->db->get();
        $result = $query->result();

        return $this->formatDevices($result);
    }

    public function getServiceDevices($month, $year)
    {
        $this->db->select('device_id as id, customer_id as customerId, registry_no as registryNo, comment, '
            . 'purchase_date as purchaseDate, price_gross as price, last_service_date as lastService, '
            . 'service_period as servicePeriod, next_service_date as nextService');
        $this->db->from('devices');
        $this->db->where('month(next_service_date)', $month);
        $this->db->where('year(next_service_date)', $year);
        $this->db->where('flag', 1);
        $this->db->order_by('purchase_date', 'DESC');

        $query = $this->db->get();
        $result = $query->result();

        return $this->formatDevices($result);
    }

    public function getDevicesByProperty($property_id, $value_id)
    {
        $this->db->select('devices.device_id as id, customer_id as customerId, registry_no as registryNo, comment, '
            . 'purchase_date as purchaseDate, price_gross as price, last_service_date as lastService, '
            . 'next_service_date as nextService, devices.flag as flag');
        $this->db->from('devices, device_properties');
        $this->db->where('devices.device_id = device_properties.device_id');
        $this->db->where('property_id', $property_id);
        $this->db->where('value_id', $value_id);
        $this->db->where('devices.flag !=', 0);
        $this->db->where('device_properties.flag', 1);
        $this->db->order_by('purchase_date', 'DESC');

        $query = $this->db->get();
        $result = $query->result();

        return $this->formatDevices($result);
    }

    public function findDevices($query)
    {
        $this->db->select('device_id as id, customer_id as customerId, registry_no as registryNo, comment, '
            . 'purchase_date as purchaseDate, price_gross as price, last_service_date as lastService, '
            . 'next_service_date as nextService');
        $this->db->from('devices');
        $this->db->like('registry_no', $query);
        $this->db->where('flag', 1);
        $this->db->order_by('purchase_date', 'DESC');

        $query = $this->db->get();
        $result = $query->result();

        return $this->formatDevices($result);
    }

    public function checkNumber($query, $device_id = false)
    {
        $this->db->select('device_id as id');
        $this->db->from('devices');
        $this->db->where('registry_no', trim($query));
        if ($device_id) {
            $this->db->where('device_id !=', $device_id);
        }

        $this->db->where('flag !=', 0);
        $this->db->limit(1);

        $query = $this->db->get();
        return $query->result();
    }

    public function addDevice($device = array())
    {
        $device['user_id'] = $this->u_id;
        $device['date_add'] = $this->date;
        $device['date_upd'] = $this->date;
        $this->db->insert('devices', $device);

        $id = $this->db->insert_id('devices');

        return $id;
    }

    public function getCustomerDevices($customer_id)
    {
        $this->db->select('device_id as id, registry_no as registryNo, comment, '
            . 'name, purchase_date as purchaseDate, price_gross as price, '
            . 'last_service_date as lastService, next_service_date as nextService, devices.flag as flag');
        $this->db->from('devices');
        $this->db->where('customer_id', $customer_id);
        $this->db->where('devices.flag !=', 0);
        $this->db->order_by('purchase_date', 'DESC');

        $query = $this->db->get();
        $result = $query->result();

        return $this->formatDevices($result);
    }

    public function moveDevicesToParent($branch_id, $parent_id)
    {
        $device['customer_id'] = $parent_id;
        $device['date_upd'] = $this->date;

        $this->db->set($device);
        $this->db->where('customer_id', $branch_id);
        $this->db->update('devices');
    }

    public function getDevice($device_id)
    {
        $this->db->select('device_id as id, registry_no as registryNo, customer_id as customerId, '
            . 'name, purchase_date as purchaseDate, purchase_date, price_gross as price, comment, service_period as servicePeriod, '
            . 'last_service_date as lastService, last_service_date, next_service_date as nextService, flag');
        $this->db->from('devices');
        $this->db->where('device_id', $device_id);
        $this->db->limit(1);

        $device = $this->getOnce();

        if ($device) {
            $device = $this->setPrettyDates($device);
        }
        return $device;
    }

    public function updateDevice($device_id, $device = array())
    {
        $device['date_upd'] = $this->date;

        $this->db->set($device);
        $this->db->where('device_id', $device_id);
        $this->db->update('devices');
    }

    public function deleteDevice($device_id)
    {
        $upd = [];
        $upd['flag'] = 0;
        $this->updateDevice($device_id, $upd);
    }

    public function updateService($device_id)
    {
        $d_id = $service['device_id'];
        $device = $this->getDevice($d_id);

        $c_service = strtotime($device->last_service_date);
        $n_service = strtotime($service['service_date']);

        $days = countNextDays($device->servicePeriod);

        if ($n_service > $c_service) {
            $upd = [];
            $upd['last_service_date'] = $service['service_date'];

            $next = strtotime($service['service_date'] . $days);
            $upd['next_service_date'] = Date('Y-m-d', $next);

            $this->updateDevice($d_id, $upd);
        }
    }

    private function formatDevices($devices = array())
    {
        $c = count($devices);
        for ($i = 0; $i < $c; $i++) {
            $devices[$i] = $this->setPrettyDates($devices[$i]);

            $devices[$i]->registryNo = trim($devices[$i]->registryNo);
            $devices[$i]->flag = intval($devices[$i]->flag);
        }

        return $devices;
    }

    private function setPrettyDates($device = array())
    {
        $device->isoDate = '';
        $device->lastIsoDate = '';

        if (strlen($device->purchaseDate) > 0) {
            $device->purchaseDate = Date('c', strtotime($device->purchaseDate));
            $device->purchasePrettyDate = formatDate($device->purchaseDate, 'j n Y');
        }

        if (strlen($device->lastService) > 0) {
            $device->lastIsoDate = Date('c', strtotime($device->lastService));
            $device->lastService = formatDate($device->lastService, 'j n Y');
        }

        if (strlen($device->nextService) > 0) {
            $device->nextService = formatDate($device->nextService, 'F Y');
        }
        return $device;
    }

}
