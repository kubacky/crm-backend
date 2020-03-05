<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Event
{

    private $actions;

    private $types;

    private $titles;

    private $event;

    private $module;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('events_model');

        $this->event = [];
        $this->actions = ['created', 'updated', 'deleted'];
        $this->types = ['product', 'issuing', 'receive', 'set', 'device', 'service', 'customer', 'branch', 'note', 'mail', 'user', 'task'];

        $this->titles = [
            'warehouse' => [
                'product' => ['created' => WAREHOUSE_PRODUCT_CREATED, 'updated' => WAREHOUSE_PRODUCT_UPDATED, 'deleted' => WAREHOUSE_PRODUCT_DELETED],
                'receive' => ['created' => WAREHOUSE_RECEIVE_CREATED, 'updated' => WAREHOUSE_RECEIVE_UPDATED, 'deleted' => WAREHOUSE_RECEIVE_DELETED],
                'issuing' => ['created' => WAREHOUSE_ISSUING_CREATED, 'updated' => WAREHOUSE_ISSUING_UPDATED, 'deleted' => WAREHOUSE_ISSUING_DELETED],
            ],
            'devices' => [
                'device' => ['created' => DEVICE_CREATED, 'updated' => DEVICE_UPDATED, 'deleted' => DEVICE_DELETED],
            ],
            'services' => [
                'service' => ['created' => SERVICE_CREATED, 'updated' => SERVICE_UPDATED, 'deleted' => SERVICE_DELETED],
            ],
            'customers' => [
                'customer' => ['created' => CUSTOMER_CREATED, 'updated' => CUSTOMER_UPDATED, 'deleted' => CUSTOMER_DELETED],
                'branch' => ['created' => BRANCH_CREATED, 'updated' => BRANCH_UPDATED, 'deleted' => BRANCH_DELETED],
            ],
            'notes' => [
                'note' => ['created' => NOTE_CREATED, 'updated' => NOTE_UPDATED, 'deleted' => NOTE_DELETED],
            ],
            'tasks' => [
                'task' => ['created' => TASK_CREATED, 'updated' => TASK_UPDATED, 'deleted' => TASK_DELETED],
            ],
        ];
    }

    public function create($id, $type, $concerns, $action, $title, $params = null)
    {
        if (in_array($type, $this->types)
            && in_array($action, $this->actions)) {

            $this->event['event_type'] = $action;
            $this->event['source_type'] = $type;
            $this->event['concerns'] = $concerns;
            $this->event['title'] = $title;
            $this->event['source_id'] = $id;
            $this->event['params'] = $params;

            $this->CI->events_model->createEvent($this->event);
        }

        return false;
    }

    public function setDate($date) {
        $this->event['date_add'] = $date;
    }

    public function setUser($user_id) {
        $this->event['user_id'] = $user_id;
    }

    public function setIp($ip) {
        $this->event['ip'] = $ip;
    }
}
