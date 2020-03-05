<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Service
{

    private $service;

    public function prepareService($input = array(), $return = false)
    {
        $service = [];
        $service['type_id'] = $input['type'];
        $service['status_id'] = $input['status'];
        $service['price'] = intval($input['price']);
        $service['comment'] = (array_key_exists('comment', $input)) ? $input['comment'] : '';

        $date = strtotime($input['date']);
        $service['service_date'] = Date('Y-m-d', $date);

        $this->service = $service;

        if ($return) {
            return $this->get();
        }
    }

    public function assignDevice($device_id)
    {
        $this->service['device_id'] = $device_id;
    }

    public function assignCustomer($customer_id)
    {
        $this->service['customer_id'] = $customer_id;
    }

    public function assignTechnician($technician_id)
    {
        $this->service['technician_id'] = $technician_id;
    }

    public function get()
    {
        return $this->service;
    }

}
