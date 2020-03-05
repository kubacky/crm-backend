<?php
setlocale(LC_MONETARY, 'pl_PL');

defined('BASEPATH') or exit('No direct script access allowed');

class Platform extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->ion_auth->login('admin', 'Suszarka2017', true);
    }

    protected function getInput() {

        return json_decode(trim(file_get_contents('php://input')), true);
    }
}
