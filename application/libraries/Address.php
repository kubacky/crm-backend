<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Address
{
    private $address = [];
    private $response = [];
    private $api_url;
    private $api_format;

    public function __construct()
    {
        $this->api_format = 'json';

        $this->api_url = 'https://maps.googleapis.com/maps/api/geocode/';
        $this->api_url .= $this->api_format;
        $this->api_url .= '?sensor=false&key=AIzaSyClmpxs0zPeW70NL3qBFFYW-NhjZrC9XLw&address=';
    }

    public function prepareAddress($address = array(), $return = false)
    {
        $addr = array();
        $addr['address_street'] = $address['street'];
        $addr['address_postcode'] = $address['postcode'];
        $addr['address_city'] = $address['city'];

        if (strlen($addr['address_street']) == 0
            || strlen($addr['address_postcode']) == 0
            || strlen($addr['address_city']) == 0) {
            return false;
        }

        $this->geocode($addr);
        $addr['address_lat'] = $this->getLatitude();
        $addr['address_lng'] = $this->getLongitude();

        $this->address = $addr;

        if ($return) {
            return $addr;
        }
    }

    public function assignPrevious($prev_id)
    {
        $this->address['address_previous_id'] = $prev_id;
    }

    public function get()
    {
        return $this->address;
    }

    public function setFormat($format = 'json')
    {
        $this->api_format = $format;
    }

    public function geocode($address = array())
    {
        $find = $address['address_street'] . ', ' . $address['address_city'];

        $resp = $this->getGeocode($find);

        if ($resp['status'] == 'OK') {
            $this->response = $resp['results'][0];
        } else {
            $resp = $this->getGeocode($address['address_city']);
            $this->response = $resp['results'][0];
        }
    }

    private function getGeocode($address)
    {
        $url = $this->api_url . urlencode($address);

        $resp_json = file_get_contents($url);
        return json_decode($resp_json, true);
    }

    public function getCoordinates()
    {
        if (count($this->response) > 0) {
            $return = [];
            $return['lat'] = $this->getLatitude();
            $return['lng'] = $this->getLongitude();
            return $return;
        }
        return false;
    }

    public function getLatitude()
    {
        if (count($this->response) > 0) {
            return $this->response['geometry']['location']['lat'];
        }
        return false;
    }

    public function getLongitude()
    {
        if (count($this->response) > 0) {
            return $this->response['geometry']['location']['lng'];
        }
        return false;
    }
}
