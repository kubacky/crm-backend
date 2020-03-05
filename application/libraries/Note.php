<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Note {

  private $note = [];

  public function prepareNote($id, $type, $input = array(), $return = false) {

    $note = [];
    $note['source_id'] = $id;
    $note['source_type'] = $type;
    $note['class'] = $input['class'];
    $note['title'] = $input['title'];
    $note['content'] = $input['content'];

    if($return) {
      return $note;
    }
    else {
      $this->note = $note;
      return true;
    }
  }

  public function assignCustomer($customer_id) {
    $this->note['customer_id'] = $customer_id;
  }

  public function assignDevice($device_id) {
    $this->note['device_id'] = $device_id;
  }

  public function assignService($service_id) {
    $this->note['service_id'] = $service_id;
  }

  public function assignMail($mail_id) {
    $this->note['mail_id'] = $mail_id;
  }

  public function assignWrehouseDoc($doc_id) {
    $this->note['warehouse_doc_id'] = $doc_id;
  }

  public function assignWarehouseProduct($product_id) {
    $this->note['warehouse_product_id'] = $product_id;
  }

  public function get() {
    return $this->note;
  }

 }