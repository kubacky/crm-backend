<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Product {
    private $product = [];

    public function prepareProduct($input = array(), $return = false) {
        
        $prod = [];
        $prod['quantity'] = intval($input['quantity']);
        $prod['name'] = $input['name'];
        $prod['plain_name'] = getPlainText($input['name']);

        $this->product = $prod;

        if($return) {
            return $this->product;
        }
    }

    public function assignList($list_id) {
        $this->product['type'] = 'set';
        $this->product['list_id'] = $list_id;
    }

    public function get() {
        return $this->product;
    }
}