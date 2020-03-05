<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Devices extends Platform
{

    public function __construct()
    {
        parent::__construct();

        $this->load->library('index');
        $this->load->library('event');

        $this->index->setModule('devices');

        $this->load->library('device');

        $this->load->model('devices_model');
        $this->load->model('customers_model');
        $this->load->model('addresses_model');
        $this->load->model('contacts_model');
        $this->load->model('notes_model');
        $this->load->model('services_model');
        $this->load->model('properties_model');
        $this->load->model('tasks_model');
        $this->load->model('events_model');
    }

    public function get($action = 'devices', $p_1 = null, $p_2 = null)
    {
        switch ($action) {
            case 'device':
                $this->getDevice($p_1);
                break;
            case 'services':
                $this->getServiceDevices($p_1, $p_2);
                break;
            case 'counts':
                $this->getDevicesCounts($p_1, $p_2);
                break;
            case 'customer':
                $this->data = $this->devices_model->getCustomerDevice($p_1);
                break;
            case 'property':
                $this->getDevicesByProperty($p_1, $p_2);
                break;
            case 'page':
                $this->getPage($p_1, $p_2);
                break;
            case 'devices':
            default:
                $this->getDevices($p_1, $p_2);
        }
        $this->render(null, 'json');
    }

    public function find()
    {
        $input = $this->getInput();

        $devices = $this->devices_model->findDevices(trim($input['query']));

        $this->data = $this->formatDevices($devices);
        $this->render(null, 'json');
    }

    public function check()
    {
        $input = $this->getInput();

        $device = $this->devices_model->checkNumber(trim($input['query']), $input['deviceId']);

        $return = (empty($device)) ? false : true;

        $this->data = $return;
        $this->render(null, 'json');
    }

    public function moveDevicesToParent($branch_id, $parent_id)
    {
        $this->devices_model->moveDevicesToParent($branch_id, $parent_id);
    }

    public function save()
    {

        $input = $this->getInput();

        if (array_key_exists('id', $input)) {
            $this->update($input['id']);
        } else {
            $this->create();
        }
    }

    public function create()
    {

        $input = $this->getInput();

        $this->device->prepareDevice($input);
        $this->device->assignCustomer($input['customerId']);
        $device = $this->device->get();

        $check = $this->devices_model->checkNumber(trim($device['registry_no']));

        if (empty($check)) {
            $device_id = $this->devices_model->addDevice($device);
            $this->assignProperties($device_id, $input['properties']);

            $this->data['id'] = $device_id;

            $dev = $this->devices_model->getDevice($device_id);

            $this->index->create($device_id, $device['registry_no']);
            $this->event->create($device_id, 'device', 'device', 'created', DEVICE_CREATED, json_encode($dev));
            $this->event->create($dev->customerId, 'device', 'customer', 'created', DEVICE_CREATED, json_encode($dev));

            $this->data['device'] = $dev;
            $this->data['status'] = 'OK';
        } else {
            $this->data['status'] = 'ERROR';
        }

        $this->render(null, 'json');

    }

    public function edit($device_id)
    {
        $device = $this->devices_model->getDevice($device_id);
        $device->customer = $this->customers_model->getCustomer($device->customerId);
        $device->properties = $this->parseProperties($device_id);
        $device->services = $this->services_model->getServices($device_id, 'device');

        $this->data = $device;
        $this->render(null, 'json');
    }

    public function update($device_id)
    {

        $input = $this->getInput();

        $previous = $this->devices_model->getDevice($device_id);
        $previos_params = $this->properties_model->getDeviceProperties($device_id);

        $this->device->prepareDevice($input);
        $this->device->assignCustomer($input['customerId']);
        $device = $this->device->get();

        $this->devices_model->updateDevice($device_id, $device);
        $this->assignProperties($device_id, $input['properties']);

        $current = $this->devices_model->getDevice($device_id);
        $params = $this->compare($previous, $current, $previos_params);

        $this->index->update($device_id, $device['registry_no']);

        $this->event->create($device_id, 'device', 'device', 'updated', DEVICE_UPDATED, json_encode($params));
        $this->event->create($current->customerId, 'device', 'customer', 'updated', DEVICE_UPDATED, json_encode($params));

        $this->data['device'] = $current;
        $this->data['status'] = 'OK';
        $this->render(null, 'json');
    }

    public function remove($id)
    {
        $device = $this->devices_model->getDevice($id);
        $this->devices_model->deleteDevice($id);

        $this->event->create($id, 'device', 'device', 'deleted', DEVICE_DELETED, json_encode($device));
        $this->event->create($device->customerId, 'device', 'customer', 'deleted', DEVICE_DELETED, json_encode($device));
        $this->index->delete($id);

        $this->data['status'] = 'ok';
        $this->render(null, 'json');
    }

    public function cancel($id)
    {
        $device = $this->devices_model->getDevice($id);
        $this->devices_model->cancelDevice($id);

        $this->event->create($id, 'device', 'device', 'deleted', DEVICE_CANCELED, json_encode($device));
        $this->event->create($device->customerId, 'device', 'customer', 'deleted', DEVICE_CANCELED, json_encode($device));
        $this->index->delete($id);

        $this->data['status'] = 'ok';
        $this->render(null, 'json');
    }

    private function getDevices($start, $count)
    {
        $devices = $this->devices_model->getShortDevices($start, $count);

        $this->data = $this->formatShortDevices($devices);
    }

    private function getDevicesCounts($month, $year)
    {
        $counts = [];
        $counts['toDoServices'] = count($this->devices_model->getServiceDevices($month, $year));
        $counts['doneServices'] = count($this->devices_model->getServiceDevices($month, $year + 1));
        $counts['total'] = $counts['toDoServices'] + $counts['doneServices'];

        $counts['progress'] = ($counts['doneServices'] == 0) ? 0 : ($counts['doneServices'] * 100) / $counts['total'];

        $this->data = $counts;
    }

    private function getServiceDevices($month, $year)
    {
        $devices = $this->devices_model->getServiceDevices($month, $year);

        $this->data = $this->formatDevices($devices);
    }

    private function getDevicesByProperty($property_id, $value_id)
    {
        $devices = $this->devices_model->getDevicesByProperty($property_id, $value_id);

        $this->data = $this->formatDevices($devices);
    }

    private function getDevice($device_id)
    {
        $device = $this->devices_model->getDevice($device_id);

        $c_id = $device->customerId;
        $device->customer = $this->customers_model->getCustomer($c_id);

        $p_id = $device->customer->parentId;
        if ($p_id != 0) {
            $device->customer->parent = $this->customers_model->getCustomer($p_id);
        }

        $a_id = $device->customer->addressId;
        $device->address = $this->addresses_model->getAddress($a_id);
        $device->services = $this->getDeviceServices($device_id);
        $device->notes = $this->notes_model->getNotes($device_id, 'device');
        $device->tasks = $this->tasks_model->getTasks($device_id, 'device');
        $device->contact = $this->contacts_model->getContact($c_id);
        $device->events = $this->events_model->getEvents($device_id, 'device');
        $device->properties = $this->properties_model->getDeviceProperties($device_id);

        $this->data = $device;
    }

    private function formatDevices($devices = array())
    {
        $c = count($devices);
        for ($i = 0; $i < $c; $i++) {
            $c_id = $devices[$i]->customerId;
            $customer = $this->customers_model->getCustomer($c_id);
            $devices[$i]->address = $this->addresses_model->getAddress($customer->addressId);
            $devices[$i]->customer = $customer;
        }
        return $devices;
    }

    private function formatShortDevices($devices = array())
    {
        $c = count($devices);
        for ($i = 0; $i < $c; $i++) {
            $c_id = $devices[$i]->customerId;
            $customer = $this->customers_model->getCustomer($c_id);
            $address = $this->addresses_model->getAddress($customer->addressId);

            $devices[$i]->city = trim($address->city);
            $devices[$i]->province = trim($address->province);
            $devices[$i]->customer = trim($customer->name);
        }
        return $devices;
    }

    private function getDeviceServices($device_id)
    {
        $services = $this->services_model->getServices($device_id, 'device');

        $c = count($services);
        for ($i = 0; $i < $c; $i++) {
            $services[$i]->notes = $this->notes_model->getNotes($services[$i]->id, 'service');
        }
        return $services;
    }

    private function assignProperties($device_id, $properties = array())
    {
        $this->properties_model->clearDeviceProperties($device_id);

        foreach ($properties as $property_id => $value_id) {
            if (strlen($value_id) > 0 || $value_id != null) {
                $property = [];
                $property['device_id'] = $device_id;
                $property['property_id'] = $property_id;
                $property['value_id'] = $value_id;

                $this->properties_model->addDeviceProperty($property);
            }
        }
    }

    private function parseProperties($device_id)
    {
        $dev_properties = $this->properties_model->getDeviceProperties($device_id);
        $properties = $this->properties_model->getProperties();

        $return = [];
        $c = count($properties);
        for ($i = 0; $i < $c; $i++) {
            $return[$i] = [];
            $return[$i][0] = $properties[$i]->id . '';
            $return[$i][1] = $this->checkProperty($properties[$i], $dev_properties);
        }

        return $return;
    }

    private function checkProperty($property, $dev_properties)
    {

        $c = count($dev_properties);
        for ($i = 0; $i < $c; $i++) {
            if ($dev_properties[$i]->propertyId == $property->id) {
                return $dev_properties[$i]->valueId . '';
            }
        }
        return null;
    }

    private function compare($previous, $current, $prev_params)
    {
        $previous = (array) $previous;
        $prev_params = (array) $prev_params;

        $current = (array) $current;
        $curr_params = (array) $this->properties_model->getDeviceProperties($current['id']);

        $differents = [];

        $this->lang->load('compare_device', 'polish');
        foreach ($current as $key => $value) {
            if ($value !== $previous[$key] && $this->lang->line($key)) {
                $differents[] = ['name' => $this->lang->line($key), 'before' => $value, 'after' => $previous[$key]];
            }
        }

        $c = count($curr_params);
        for ($i = 0; $i < $c; $i++) {
            $curr_param = (array) $curr_params[$i];
            $prev_param = (array) $prev_params[$i];
            if ($curr_param['value'] !== $prev_param['value']) {
                $differents[] = ['name' => $prev_param['name'], 'before' => $prev_param['value'], 'after' => $curr_param['value']];
            }
        }

        return $differents;

    }

    //pseudolock
    public function ind($key)
    {
        if ($key = '03412Kazik123') {

            $dev = $this->devices_model->getDevices(0, 500);
            $c = count($dev);
            for ($i = 0; $i < $c; $i++) {
                $res = $this->index->search($dev[$i]->registryNo);
                if (count($res) === 0) {
                    $this->index->create($dev[$i]->id, $dev[$i]->registryNo);
                }
            }
        }
    }

    public function evs($key)
    {
        if ($key === '03412Kazik123') {

            $devices = $this->devices_model->getDevices();
            $c = count($devices);
            for ($i = 0; $i < $c; $i++) {
                $dev = $devices[$i];
                $this->event->setDate($dev->date_add);
                $this->event->setUser($dev->userId);
                $this->event->setIp('79.189.115.213');

                $this->event->create($dev->id, 'device', 'device', 'created', DEVICE_CREATED, json_encode($dev));
                $this->event->create($dev->customerId, 'device', 'customer', 'created', DEVICE_CREATED, json_encode($dev));
            }
        }
    }
}
