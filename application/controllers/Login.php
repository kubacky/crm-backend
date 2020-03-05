<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends My_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('ion_auth');
        $this->load->helper(array('url', 'language'));
        $this->load->library('form_validation');
        $this->lang->load('auth');
    }

    public function index() {
        $this->data['pagetitle'] = 'Logowanie';

        $this->form_validation->set_rules('login', 'login', 'required');
        $this->form_validation->set_rules('password', 'hasło', 'required');

        if ($this->form_validation->run() == true) {
            $remember = (bool) $this->input->post('remember');
            $login = $this->input->post('login');
            $password = $this->input->post('password');

            if ($this->ion_auth->login($login, $password, $remember)) {
                redirect('/', 'refresh');
            } else {
                $this->session->set_flashdata('message', $this->ion_auth->errors());
                redirect('login', 'refresh');
            }
        } else {
            $this->render('user/login', 'login');
        }
    }

    public function logout() {
        $this->ion_auth->logout();
        redirect('login', 'refresh');
    }

}
