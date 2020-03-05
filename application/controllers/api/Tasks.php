<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Tasks extends Platform
{

    public function __construct()
    {
        parent::__construct();

        $this->load->model('tasks_model');
        $this->load->model('customers_model');
        $this->load->model('devices_model');
        $this->load->model('contacts_model');
        $this->load->library('task');
        $this->load->library('event');
    }

    public function get($action, $id, $type = null)
    {
        switch ($action) {
            case 'task':
                $this->data = $this->tasks_model->getTask($id);
                break;
            case 'next':
                $this->data = $this->getNextTasks($id);
                break;
            default:
                $this->data = $this->tasks_model->getTasks($id, $action);
                break;
        }
        $this->render(null, 'json');
    }

    public function save()
    {
        $input = $this->getInput();

        if (array_key_exists('id', $input)) {
            $this->update($input['id']);
        } else {
            $this->create();
        }

        $this->data['status'] = 'OK';
        $this->render(null, 'json');
    }

    public function create()
    {
        $input = $this->getInput();

        $task = $this->task->prepareTask($input, true);

        $id = $this->tasks_model->addTask($task);

        $task = $this->tasks_model->getTask($id);
        $this->event->create($task->sourceId, 'task', $task->sourceType, 'created', TASK_CREATED, json_encode($task));

        $this->data['id'] = $id;
    }

    public function update($task_id)
    {
        $input = $this->getInput();

        $params = [];
        $params['before'] = $this->tasks_model->getTask($task_id);

        $task = $this->task->prepareTask($input, true);
        $this->tasks_model->updateTask($task_id, $task);

        $params['after'] = $this->tasks_model->getTask($task_id);
        $this->event->create($params['before']->sourceId, 'task', $params['before']->sourceType, 'updated', TASK_UPDATED, json_encode($params));
    }

    public function toggle($task_id, $status)
    {
        $upd = ['status' => $status];

        $this->tasks_model->updateTask($task_id, $upd);

        $this->data['status'] = 'OK';
        $this->render(null, 'json');

    }

    public function delete($task_id)
    {
        $task = $this->tasks_model->getTask($task_id);
        $this->tasks_model->deleteTask($task_id);

        $this->event->create($task->sourceId, 'task', $task->sourceType, 'deleted', TASK_DELETED, json_encode($task));

        $this->data['status'] = 'OK';
        $this->render(null, 'json');
    }

    private function getNextTasks($count)
    {
        $tasks = $this->tasks_model->getNextTasks($count);

        $c = count($tasks);
        for ($i = 0; $i < $c; $i++) {
            $s_id = $tasks[$i]->sourceId;
            if ($tasks[$i]->sourceType == 'customer') {
                $tasks[$i]->customer = $this->customers_model->getCustomer($s_id);
                $tasks[$i]->contact = $this->contacts_model->getContact($s_id);
            }
            if ($tasks[$i]->sourceType == 'device') {
                $tasks[$i]->device = $this->devices_model->getDevice($s_id);

                $c_id = $tasks[$i]->device->customerId;
                $tasks[$i]->customer = $this->customers_model->getCustomer($c_id);
                $tasks[$i]->contact = $this->contacts_model->getContact($c_id);
            }
        }
        return $tasks;
    }
}
