<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    protected $data = array();

    public function __construct() {
        parent::__construct();
    }

    protected function render($view = null, $template = 'master') {
        if ($template == 'json' || $this->input->is_ajax_request()) {
            header('Content-Type: application/json');
            echo json_encode($this->data, JSON_UNESCAPED_SLASHES);
        } elseif (is_null($template)) {
            $this->load->view($view, $this->data);
        } else {
            $this->data['view_content'] = (is_null($view)) ? '' : $this->load->view($view, $this->data, true);
            $this->load->view('templates/' . $template, $this->data);
        }
    }

}
