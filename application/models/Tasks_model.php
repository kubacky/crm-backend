<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Tasks_model extends Platform_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function addTask($task = array())
    {
        $task['user_id'] = $this->u_id;
        $task['date_add'] = $this->date;
        $task['date_upd'] = $this->date;

        $this->db->insert('tasks', $task);
        return $this->db->insert_id('tasks');
    }

    public function getTasks($source_id, $source_type)
    {
        $date = Date('Y-m-d');

        $this->db->select('task_id as id, user_id as userId, class as class, icon, date_add as dateAdd, '
            . 'title, description, task_date as date, source_id as sourceId, source_type as sourceType, '
            . 'first_name as firstName, last_name as lastName, status');
        $this->db->from('tasks, users');
        $this->db->where('flag', 1);
        $this->db->where('users.id = user_id');
        $this->db->where('source_id', $source_id);
        $this->db->where('source_type', $source_type);
        $this->db->order_by('task_date', 'DESC');

        return $this->getCouple();
    }

    public function getNextTasks($count)
    {
        $date = Date('Y-m-d');

        $this->db->select('task_id as id, user_id as userId, class as class, icon, '
            . 'title, description, task_date as date, source_id as sourceId, status, '
            . 'source_type as sourceType, first_name as firstName, last_name as lastName');
        $this->db->from('tasks, users');
        $this->db->where('flag', 1);
        $this->db->where('users.id = user_id');
        $this->db->where('task_date >=', $date);
        $this->db->order_by('task_date', 'ASC');
        $this->db->limit($count);

        return $this->getCouple();
    }

    public function getTask($task_id)
    {
        $this->db->select('task_id as id, user_id as userId, class as class, icon, '
            . 'title, description, task_date as date, status, source_id as sourceId, '
            . 'source_type as sourceType, date_add as dateAdd');
        $this->db->from('tasks');
        $this->db->where('task_id', $task_id);
        $this->db->limit(1);

        $task = $this->getOnce();

        if ($task) {
            return $this->formatTask($task);
        }
        return false;
    }

    public function updateTask($task_id, $task = array())
    {
        $task['date_upd'] = $this->date;
        $this->db->set($task);
        $this->db->where('task_id', $task_id);
        $this->db->update('tasks');
    }

    public function deleteTask($task_id)
    {
        $upd = [];
        $upd['flag'] = 0;
        $this->updateTask($task_id, $upd);
    }

    private function getCouple()
    {
        $query = $this->db->get();
        $result = $query->result();

        $c = count($result);
        for ($i = 0; $i < $c; $i++) {
            $result[$i] = $this->formatTask($result[$i]);
        }
        return $result;
    }

    private function formatTask($task)
    {
        $dayLeft = false;
        $today = false;
        $date = Date('Y-m-d');

        $taskYMD = Date('Y-m-d', strtotime($task->date));
        $miliseconds = strtotime($taskYMD) - strtotime($date);

        if ($miliseconds >= 0) {
            $dayLeft = intval($miliseconds / (60 * 60 * 24));
        }

        if ($task->date == $date) {
            $today = true;
        }

        $task->dayLeft = $dayLeft;
        $task->status = intval($task->status);
        $task->timestamp = strtotime($task->date);
        $task->isoDate = Date('c', $task->timestamp);
        $task->day = Date('j', $task->timestamp);
        $task->dateAdd = formatDate($task->dateAdd, 'j n Y');
        $task->month = mb_substr(formatDate($task->date, 'n'), 0, 3);
        $task->date = formatDate($task->date, 'j n Y');

        $hour = Date('H', $task->timestamp);
        if ($hour != '00') {
            $task->time = Date('H:i', $task->timestamp);
        }

        $task->today = $today;

        return $task;
    }
}
