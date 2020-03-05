<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Search extends Platform
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('index_model');
    }

    public function index()
    {

        $input = json_decode(trim(file_get_contents('php://input')), true);
        $query = $input['query'];

        $results = [];

        if (strlen($query) > 2) {
            $results = $this->index_model->search($query);
        }
        $this->data = $results;
        $this->render(null, 'json');

    }

}
