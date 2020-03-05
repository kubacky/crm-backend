<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Geocoding {
  private $address;
  private $response = [];
  private $api_url;
  private $api_format;

  public function __construct() {
    $this->api_format = 'json';

    $this->api_url = 'https://maps.googleapis.com/maps/api/geocode/';
    $this->api_url .= $this->api_format;
    $this->api_url .= '?sensor=false&address=';
  }

  public function setAddress($street, $postcode, $city) {
    $this->address = urlencode($street . ', ' . $postcode . ' ' . $city);
  }

  public function setFormat($format = 'json') {
    $this->api_format = $format;
  }

  public function geocode() {
    $url = $this->api_url . $this->address;

    $resp_json = file_get_contents($url);
    $resp = json_decode($resp_json, true);

    if($resp['status'] == 'OK') {
      $this->response = $resp['results'][0];
    }
    else {
      return false;
    }
  }

  public function getCoordinates() {
    if(count($this->response) > 0) {
      $return = [];
      $return['lat'] = $this->getLatitude();
      $return['lng'] = $this->getLongitude();
      return $return;
    }
    return false;
  }

  public function getLatitude() {
    if(count($this->response) > 0) {
      return $this->response['geometry']['location']['lat'];
    }
    return false;
  }

  public function getLongitude() {
    if(count($this->response) > 0) {
      return $this->response['geometry']['location']['lng'];
    }
    return false;
  }
}