<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Addresses extends Platform
{

    public function __construct()
    {
        parent::__construct();

        $this->load->model('addresses_model');
    }

    public function get($action = 'addresses', $group_id = null)
    {
        switch ($action) {
            case 'provinces':
                $this->data = $this->addresses_model->getProvinces();
                break;
            case 'addresses':
            default:
                //$this->data = $this->groups_model->getGroups('customers');
        }
        $this->render(null, 'json');
    }

    public function update()
    {
        $addr = $this->addresses_model->getAllPostcodes();
        $c = count($addr);

        for ($i = 0; $i < $c; $i++) {
            $a = ['address_postcode' => $addr[$i]->postcode];
            $this->addresses_model->updateAddress($addr[$i]->id, $a);
        }
    }
}
