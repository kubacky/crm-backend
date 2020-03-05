<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Contact {

  private $_contact = [];

  public function prepareContact($input = array(), $return = false) {
    if(!array_key_exists('name', $input)) {
      return false;
    }
    if(strlen($input['name']) < 2 || is_null($input['name'])) {
      return false;
    }
    $contact = [];

    $contact['contact_name'] = $input['name'];
    $contact['contact_workplace'] = $input['workplace'];
    $contact['contact_phone'] = $input['phone'];
    $contact['contact_mail'] = $input['mail'];

    if($return) {
      return $contact;
    }
    else {
      $this->contact = $contact;
      return true;
    }
  }

  public function assignCustomer($customer_id) {
    $this->contact['contact_customer_id'] = $customer_id;
  }

  public function assignProduct($product_id) {
    $this->contact['contact_product_id'] = $product_id;
  }

  public function get() {
    return $this->contact;
  }

 }