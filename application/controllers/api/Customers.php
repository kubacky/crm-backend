<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Customers extends Platform
{

    public function __construct()
    {
        parent::__construct();

        $this->load->library('index');
        $this->load->library('event');

        $this->index->setModule('customers');

        $this->load->library('customer');
        $this->load->library('address');
        $this->load->library('contact');

        $this->load->model('groups_model');
        $this->load->model('products_model');
        $this->load->model('customers_model');
        $this->load->model('addresses_model');
        $this->load->model('contacts_model');
        $this->load->model('devices_model');
        $this->load->model('services_model');
        $this->load->model('events_model');
        $this->load->model('notes_model');
        $this->load->model('tasks_model');
    }

    public function get($action = 'customers', $id = null)
    {
        switch ($action) {
            case 'customer':
                $this->getCustomer($id);
                break;
            case 'provinces':
                $this->getByProvinces($id);
                break;
            case 'groups':
                $this->getByGroups($id);
                break;
            case 'count':
                $this->getCustomersCount();
                break;
            case 'customers':
            default:
                $this->getCustomers($id);
        }
        $this->render(null, 'json');
    }

    public function create($action = 'customer', $customer_id = null)
    {
        switch ($action) {
            case 'branch':
                $this->createBranch($customer_id);
                break;
            case 'customer':
            default:
                $this->createCustomer();
        }
        $this->render(null, 'json');
    }

    public function update($action = 'customer', $id = null)
    {
        switch ($action) {
            case 'customer':
            default:
                $this->updateCustomer($id);
        }
    }

    public function delete($action = 'customer', $id = null)
    {
        switch ($action) {
            case 'customer':
            default:
                $this->deleteCustomer($id);
        }
    }

    public function edit($customer_id)
    {
        $customer = $this->customers_model->getCustomer($customer_id);

        if ($customer) {
            $customer = $this->getCustomerData($customer);
        }

        $this->data = $customer;
    }

    public function find()
    {
        $customers = [];
        $input = json_decode(trim(file_get_contents('php://input')), true);

        if (strlen($input['query']) > 2) {
            $customers = $this->customers_model->findCustomers($input['query']);

            $c = count($customers);
            for ($i = 0; $i < $c; $i++) {
                $addr_id = $customers[$i]->addressId;
                $address = $this->addresses_model->getAddress($addr_id);

                $customers[$i]->name = trim($customers[$i]->name);
                $customers[$i]->branches = $this->getBranches($customers[$i]->id);
                $customers[$i]->address = $address;
            }

            $this->data = $customers;
            $this->render(null, 'json');
        }
    }

    public function check()
    {
        $input = json_decode(trim(file_get_contents('php://input')), true);
        $return = false;

        if (strlen($input['query']) > 2) {
            $alias = getPlainText($input['query']);
            $customer = $this->customers_model->checkAlias($alias);

            if (!empty($customer)) {
                $return = true;
            }
        }
        $this->data = $return;

        $this->render(null, 'json');
    }

    private function getCustomersCount()
    {
        $this->getCustomers();

        $return = [];
        $return['count'] = count($this->data);

        $this->data = $return;
    }

    private function getCustomers($group_id = null)
    {
        if (intval($group_id) > 0) {
            $customers = $this->customers_model->getCustomersByGroups($group_id);
        } else {
            $customers = $this->customers_model->getCustomers(0);
        }

        $this->data = $this->formatCustomers($customers);
    }

    private function getByGroups($groups)
    {
        $split = explode('_', $groups);

        $customers = $this->customers_model->getCustomersByGroups($split);
        $this->data = $this->formatCustomers($customers);
    }

    private function getByProvinces($provinces)
    {
        $split = explode('_', $provinces);

        $customers = $this->customers_model->getCustomersByProvinces($split);
        $this->data = $this->formatCustomers($customers);
    }

    private function formatCustomers($customers = array())
    {

        $c = count($customers);
        for ($i = 0; $i < $c; $i++) {
            $a_id = $customers[$i]->addressId;
            $c_id = $customers[$i]->id;

            $address = $this->addresses_model->getAddress($a_id);

            $customers[$i]->address = $address;
            $customers[$i]->branches = $this->customers_model->getCustomers($c_id);
        }
        return $customers;
    }

    private function getCustomer($customer_id)
    {
        $customer = $this->customers_model->getCustomer($customer_id);

        if ($customer) {
            if ($customer->parentId != 0) {
                $customer = $this->customers_model->getCustomer($customer->parentId);
            }
            $customer = $this->getCustomerData($customer);
            $customer->branches = $this->getBranches($customer->id);
            $customer->devicesCount = count($customer->devices);
            $customer->services = $this->refactorServices($customer->services, $customer->branches);
            $customer->income = $this->calculateIncome($customer->devices, $customer->services);

            $this->data = $customer;
        }
    }

    private function getBranches($customer_id)
    {
        $branches = $this->customers_model->getCustomers($customer_id);

        $c = count($branches);
        for ($i = 0; $i < $c; $i++) {
            $branches[$i] = $this->getCustomerData($branches[$i]);
        }

        return $branches;
    }

    private function getCustomerData($customer)
    {

        $customer->address = $this->addresses_model->getAddress($customer->addressId);
        $customer->contact = $this->contacts_model->getContact($customer->id, 'customer');
        $customer->groups = $this->groups_model->getCustomerGroups($customer->id);
        $customer->devices = $this->devices_model->getCustomerDevices($customer->id);
        $customer->notes = $this->notes_model->getNotes($customer->id, 'customer');
        $customer->services = $this->services_model->getServices($customer->id, 'customer');
        $customer->tasks = $this->tasks_model->getTasks($customer->id, 'customer');
        $customer->events = $this->events_model->getEvents($customer->id, 'customer');

        return $customer;
    }

    private function createCustomer()
    {

        $input = json_decode(trim(file_get_contents('php://input')), true);

        $input_groups = $input['groups'];
        $input_branches = $input['branches'];

        $customer_id = $this->addCustomer($input, $input_groups);

        $this->addBranches($customer_id, $input_branches, $input_groups);

        $output = [];
        $output['customer'] = $this->customers_model->getCustomer($customer_id);
        $output['status'] = 'OK';
        $output['id'] = intval($customer_id);

        $this->data = $output;
    }

    private function createBranch($customer_id)
    {

        $input = json_decode(trim(file_get_contents('php://input')), true);
        $groups = [];

        $this->addCustomer($input, $groups, $customer_id);
    }

    //TODO wyciagnac wspolny kod do jednej funkcji
    private function updateCustomer($customer_id)
    {

        $input = json_decode(trim(file_get_contents('php://input')), true);

        $previous = $this->customers_model->getCustomer($customer_id);
        $previous->address = $this->addresses_model->getAddress($previous->addressId);
        $previous->contact = $this->contacts_model->getContact($previous->id, 'customer');

        $addr = $input['address'];
        $contact = $input['contact'];

        $address = $this->address->prepareAddress($addr, true);
        if ($address) {
            $this->addresses_model->updateAddress($previous->addressId, $address);
        }

        $this->updateContact($customer_id, $contact);

        $this->customer->prepareCustomer($input);
        $customer = $this->customer->get();

        $this->customers_model->updateCustomer($customer_id, $customer);

        $params = json_encode($this->compare($customer_id, $previous));

        if ($previous->parentId == 0) {
            $groups = $input['groups'];
            $gr = $this->customer->prepareToAssignGroups($customer_id, $groups);
            $this->groups_model->clearGroups($customer_id, 'customers');
            $this->groups_model->assignToGroups($gr);

            $this->event->create($customer_id, 'customer', 'customer', 'updated', CUSTOMER_UPDATED, $params);
        } else {
            $this->event->create($customer_id, 'branch', 'customer', 'updated', BRANCH_UPDATED, $params);
            $this->event->create($previous->parentId, 'branch', 'customer', 'updated', BRANCH_UPDATED, $params);
        }
        $this->index->update($customer_id, $customer['name']);
    }

    private function addCustomer($customer = array(), $groups = array(), $parent_id = 0)
    {

        if (strlen($customer['name']) == 0 || is_null($customer['name'])) {
            return false;
        }

        $customer_id = 0;
        $list_id = 0;
        $address_id = 0;

        $addr = $customer['address'];
        $contact = $customer['contact'];

        $address = $this->address->prepareAddress($addr, true);
        if ($address) {
            $address_id = $this->addresses_model->addAddress($address);
        }

        $list_id = $this->products_model->createProductsList();

        $this->customer->prepareCustomer($customer);
        $this->customer->assignParent($parent_id);
        $this->customer->assignAddress($address_id);
        $this->customer->assignProductsList($list_id);
        $customer = $this->customer->get();

        if ($customer) {
            $customer_id = $this->customers_model->addCustomer($customer);

            $gr = $this->customer->prepareToAssignGroups($customer_id, $groups);
            $this->groups_model->clearGroups($customer_id, 'customers');
            $this->groups_model->assignToGroups($gr);

            $this->addContact($customer_id, $contact);
            $this->createIndex($customer_id, $customer);

            return $customer_id;
        }

        return false;
    }

    //also creates event
    private function createIndex($customer_id, $customer = array())
    {
        $name = $customer['name'];

        $params = json_encode($customer);

        if ($customer['parent_id'] != 0) {
            $parent = $this->customers_model->getCustomer($customer['parent_id']);

            $name = $parent->name . ' - ' . $customer['name'];

            $this->event->create($customer_id, 'branch', 'customer', 'created', BRANCH_CREATED, $params);
            $this->event->create($customer['parent_id'], 'branch', 'customer', 'created', BRANCH_CREATED, $params);
        } else {
            $this->event->create($customer_id, 'customer', 'customer', 'created', CUSTOMER_CREATED, $params);
        }
        $this->index->create($customer_id, $name);
    }

    private function deleteCustomer($customer_id)
    {
        $customer = $this->customers_model->getCustomer($customer_id);

        if ($customer->parentId == 0) {
            $branches = $this->customers_model->getCustomers($customer_id);
            $this->event->create($customer_id, 'customer', 'customer', 'deleted', CUSTOMER_DELETED, json_encode($customer));

            $c = count($branches);
            for ($i = 0; $i < $c; $i++) {
                $this->customers_model->deleteCustomer($branches[$i]->id);
            }
        } else {
            $this->event->create($customer_id, 'branch', 'customer', 'deleted', BRANCH_DELETED, json_encode($customer));
            $this->event->create($customer->parentId, 'branch', 'customer', 'deleted', BRANCH_DELETED, json_encode($customer));
        }
        $this->customers_model->deleteCustomer($customer_id);
        $this->index->delete($customer->id);
    }

    private function calculateIncome($devices = array(), $services = array())
    {
        $return = 0;

        $cd = count($devices);
        for ($i = 0; $i < $cd; $i++) {
            $return += $devices[$i]->price;
        }

        $cs = count($services);
        for ($i = 0; $i < $cs; $i++) {
            $return += $services[$i]->price;
        }

        return $return;
    }

    private function refactorDevices($c_devices = array(), $branches = array())
    {
        $return = $c_devices;

        $c = count($branches);
        for ($i = 0; $i < $c; $i++) {
            $return = array_merge($return, $branches[$i]->devices);
        }
        return $return;
    }

    private function refactorServices($c_services = array(), $branches = array())
    {
        $return = $c_services;

        $c = count($branches);
        for ($i = 0; $i < $c; $i++) {
            $return = array_merge($return, $branches[$i]->services);
        }
        return $return;
    }

    private function addBranches($customer_id, $branches = array(), $groups)
    {
        $c = count($branches);
        for ($i = 0; $i < $c; $i++) {
            $this->addCustomer($branches[$i], $groups, $customer_id);
        }
    }

    private function addContact($customer_id, $contact = array())
    {
        if ($this->contact->prepareContact($contact)) {
            $this->contact->assignCustomer($customer_id);
            $contact = $this->contact->get();
            $this->contacts_model->addContact($contact);
        }
    }

    private function updateContact($customer_id, $contact = array())
    {

        $previous = $this->contacts_model->getContact($customer_id, 'customer');
        if (!$previous) {
            $this->addContact($customer_id, $contact);
            return null;
        }

        if ($this->contact->prepareContact($contact)) {
            $this->contact->assignCustomer($customer_id);
            $contact = $this->contact->get();

            $this->contacts_model->updateContact($previous->id, $contact);
        } else {
            $this->contacts_model->deleteContact($previous->id);
        }
    }

    private function compare($id, $previous)
    {

        $previous = (array) $previous;
        $prev_address = (array) $previous['address'];
        unset($previous['address']);

        $prev_contact = (array) $previous['contact'];
        unset($previous['contact']);

        $current = (array) $this->customers_model->getCustomer($id);
        $curr_address = (array) $this->addresses_model->getAddress($current['addressId']);
        unset($current['address']);

        $curr_contact = (array) $this->contacts_model->getContact($current['id'], 'customer');
        unset($current['address']);

        $differents = [];

        $this->lang->load('compare_customer', 'polish');
        foreach ($current as $key => $value) {
            if ($value != $previous[$key] && $this->lang->line($key)) {
                $differents[] = ['name' => $this->lang->line($key), 'before' => $previous[$key], 'after' => $value];
            }
        }

        $this->lang->load('compare_address', 'polish');
        foreach ($curr_address as $key => $value) {
            if ($value != $prev_address[$key] && $this->lang->line($key)) {
                $differents[] = [$key, 'name' => $this->lang->line($key), 'before' => $prev_address[$key], 'after' => $value];
            }
        }

        $this->lang->load('compare_contact', 'polish');
        foreach ($curr_contact as $key => $value) {
            if ($value != $prev_contact[$key] && $this->lang->line($key)) {
                $differents[] = ['name' => $this->lang->line($key), 'before' => $prev_contact[$key], 'after' => $value];
            }
        }

        return $differents;
    }

    //pseudolock
    public function ind($key)
    {
        if ($key === '03412Kazik123') {

            $customers = $this->customers_model->getCustomers();
            $c = count($customers);
            for ($i = 0; $i < $c; $i++) {

                $res = $this->index->search($customers[$i]->name);
                if (count($res) === 0) {
                    $this->index->create($customers[$i]->id, $customers[$i]->name);
                }

                $branches = $this->customers_model->getCustomers($customers[$i]->id);
                $b = count($branches);
                for ($j = 0; $j < $b; $j++) {
                    $bres = $this->index->search($branches[$j]->name);
                    if (count($bres) === 0) {
                        $this->index->create($branches[$j]->id, $customers[$i]->name . ' - ' . $branches[$j]->name);
                    }
                }
            }
        }
    }

    public function evs($key)
    {
        if ($key === '03412Kazik123') {

            $customers = $this->customers_model->getAllCustomers();
            $c = count($customers);
            for ($i = 0; $i < $c; $i++) {
                $this->event->setDate($customers[$i]->date_add);
                $this->event->setUser($customers[$i]->userId);
                $this->event->setIp('79.189.115.213');

                if ($customers[$i]->parentId == 0) {
                    $this->event->create($customers[$i]->id, 'customer', 'customer', 'created', CUSTOMER_CREATED, json_encode($customers[$i]));
                } else {
                    $this->event->create($customers[$i]->id, 'branch', 'customer', 'created', BRANCH_CREATED, json_encode($customers[$i]));
                    $this->event->create($customers[$i]->parentId, 'branch', 'customer', 'created', BRANCH_CREATED, json_encode($customers[$i]));
                }
            }
        }
    }
}
