<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Index extends Platform {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->render('index/index', 'master');
    }

}
