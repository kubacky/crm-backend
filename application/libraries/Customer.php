<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Customer {

    protected $_customer = [];

    public function __construct() {
        
    }

    public function prepareCustomer($post, $return = false) {
        $customer = array();

        $customer['name'] = $post['name'];
        $customer['plain_name'] = getPlainText($post['name']);

        $customer['alias'] = $post['alias'];
        $customer['plain_alias'] = getPlainText($post['alias']);

        $customer['discount'] = (array_key_exists('discount', $post)) ? $post['discount'] : 0;
        $customer['tax_no'] = (array_key_exists('taxNo', $post)) ? getPlainText($post['taxNo'], '') : '';

        $customer['phone'] = $post['phone'];
        $customer['mail'] = $post['mail'];

        if($return) {
            return $customer;
        }
        else {
            $this->_customer = $customer;
        }
    }

    public function prepareToAssignGroups($customer_id, $groups = array()) {
        $c = count($groups);
        $return = array();

        for($i = 0; $i < $c; $i++) {
            $return[$i] = array();
            $return[$i]['group_id'] = $groups[$i];
            $return[$i]['link_id'] = $customer_id;
            $return[$i]['group_type'] = 'customers';
        }

        return $return;
    }

    public function assignAddress($address_id) {
        $this->_customer['address_id'] = $address_id;
    }

    public function assignProvince($province_id) {
        $this->_customer['province_id'] = $province_id;
    }

    public function assignProductsList($list_id) {
        $this->_customer['products_list_id'] = $list_id;
    }

    public function assignParent($parent_id) {
        $this->_customer['parent_id'] = $parent_id;
    }

    public function assignAccountNo($ccount_id) {
        $this->_customer['account_id'] = $account_id;
    }

    public function get() {
        return $this->_customer;
    }

    public function reset() {
        $this->_customer = [];
    }

}
