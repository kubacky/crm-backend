<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Index
{

    // class
    // module
    // icon
    // title
    // link
    // source_type
    // source_id

    private $index;

    private $modules;

    private $index_model;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('index_model');
        
        $this->modules = [
            'devices' => ['name' => 'produkty', 'icon' => 'fa-suitcase', 'class' => 'bg-info text-white'],
            'customers' => ['name' => 'klienci', 'icon' => 'fa-user', 'class' => 'bg-success text-white'],
        ];
        $this->index = [];
    }

    public function search($query)
    {
        return $this->CI->index_model->search($query);
    }

    public function create($id, $title)
    {
        $this->index['source_id'] = $id;
        $this->index['title'] = $title;
        $this->index['link'] = $this->buildLink($id);

        $this->CI->index_model->createIndex($this->index);
    }

    public function update($id, $title)
    {
        $this->index['title'] = $title;

        $this->CI->index_model->updateIndex($id, $this->index);

    }

    public function delete($id)
    {
        $this->CI->index_model->deleteIndex($id, $this->index['source_type']);
    }

    public function setModule($module)
    {
        $this->index['source_type'] = $module;
        $this->index['module'] = $this->modules[$module]['name'];
        $this->index['icon'] = $this->modules[$module]['icon'];
        $this->index['class'] = $this->modules[$module]['class'];
    }

    private function buildLink($id)
    {
        return $this->index['source_type'] . '/view/' . $id;
    }
}
