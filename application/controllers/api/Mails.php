<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Mails extends Platform
{

    public function __construct()
    {
        parent::__construct();

        $this->load->model('mails_model');
    }

    public function get($action = 'mails', $p_1 = null)
    {
        switch ($action) {
            case 'mail':
                $this->data = $this->mails_model->getMail($p_1);
                break;
            case 'mails':
            default:
                $this->data = $this->mails_model->getMails($p_1);
        }
        $this->render(null, 'json');
    }

    private function create($mails) {

    }

    public function update($mail_id)
    {
        
    }

    public function delete($mail_id)
    {
       
    }
}
