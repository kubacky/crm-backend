<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Device
{
    private $device = [];

    public function prepareDevice($input = array(), $return = false)
    {

        $dev = [];
        $dev['registry_no'] = $input['registryNo'];
        $dev['comment'] = $input['comment'];
        $dev['price_gross'] = $input['price'];
        $dev['service_period'] = $input['servicePeriod'];

        $this->device = $dev;

        $this->countNextServiceDate($input);

        if ($return) {
            return $this->device;
        }
    }

    public function countNextServiceDate($input = array())
    {

        $next = countNextDays($input['servicePeriod']);

        if (strlen($input['purchaseDate']) > 0) {
            $pd = strtotime($input['purchaseDate']);
            $this->device['purchase_date'] = Date('Y-m-d', $pd);

            $nd = strtotime($input['purchaseDate'] . $next);
            $this->device['next_service_date'] = Date('Y-m-d', $nd);
        }

        if (strlen($input['lastService']) > 0) {
            $sd = strtotime($input['lastService']);
            $this->device['last_service_date'] = Date('Y-m-d', $sd);

            $nd = strtotime($input['lastService'] . $next);
            $this->device['next_service_date'] = Date('Y-m-d', $nd);
        }

    }

    public function assignCustomer($customer_id)
    {
        $this->device['customer_id'] = $customer_id;
    }

    public function get()
    {
        return $this->device;
    }
}
