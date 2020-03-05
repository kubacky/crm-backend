<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Task
{

    //user_id*
    //source_id*
    //source_type*
    //class
    //icon
    //task_date*
    //title*
    //description

    private $task;

    public function __construct()
    {
        $this->task = [];
    }

    public function prepareTask($input = array(), $return = false)
    {
        $task = [];
        $task['source_id'] = $input['sourceId'];
        $task['source_type'] = $input['sourceType'];
        $task['class'] = $input['class'];
        $task['icon'] = $input['icon'];
        $task['title'] = $input['title'];
        $task['description'] = $input['description'];

        $td = strtotime($input['date']);
        $task['task_date'] = Date('Y-m-d', $td);

        if(array_key_exists('time', $input) && strlen($input['time']) > 3) {
          $task['task_date'] .= ' ' . $input['time'] . ':00';
        }

        $this->task = $task;

        if ($return) {
            return $this->task;
        }
    }

    public function get()
    {
        return $this->task;
    }

}
