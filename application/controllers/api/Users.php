<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Users extends Platform
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('users_model');
    }

    public function get($section, $user_id = null)
    {
        switch ($section) {
            case 'menu':
                $this->getUserModules();
                break;
            case 'modules':
                $this->getModules();
                break;
            case 'users':
                $this->getUsers();
                break;
            case 'current':
                $this->getCurrentUser();
                break;
            case 'user':
                $this->getUser($user_id);
                break;
            default:
                null;
        }
        $this->render(null, 'json');
    }

    private function getUserModules($user_id = null)
    {
        $this->data = $this->users_model->getUserMenu();
    }

    private function getModules()
    {
        $this->data = $this->users_model->getModules();
    }

    private function getUsers()
    {
        $users = $this->ion_auth->users()->result();
        for ($i = 0; $i < sizeof($users); $i++) {
            $users[$i]->user_locked = true;
            if ($users[$i]->id == $this->ion_auth->get_user_id()) {
                $users[$i]->user_locked = false;
            }
            if ($this->ion_auth->is_admin()) {
                $users[$i]->user_locked = false;
            }
        }
        $this->data = $this->formatUsers($users);
    }

    private function getUser($user_id)
    {
        $user = $this->ion_auth->user($user_id)->row();
        $user->user_locked = true;
        $user->new_password = '';
        $user->confirm_password = '';
        if ($user_id == $this->ion_auth->get_user_id()) {
            $user->user_locked = false;
        }
        $this->data = $user;
    }

    private function getCurrentUser()
    {
        $user = $this->ion_auth->user()->row();
        $this->data = ['user' => $user->first_name . ' ' . $user->last_name];
    }

    public function create()
    {
        $input = json_decode(trim(file_get_contents('php://input')), true);

        $email = $input['email'];
        $username = $input['username'];
        $user = array();
        $user['first_name'] = $input['first_name'];
        $user['last_name'] = $input['last_name'];
        $password = $input['new_password'];
        $confirm_password = $input['confirm_password'];
        $group = array('1');
        if ($password === $confirm_password) {
            $user_id = $this->ion_auth->register($username, $password, $email, $user, $group);
            var_dump($user_id);
            $this->users_model->assignModules($user_id, $input['modules']);
        }

        $this->data['status'] = 'ok';
        $this->render(null, 'json');
    }

    public function update($user_id)
    {
        $input = json_decode(trim(file_get_contents('php://input')), true);

        $user = array();
        $user['first_name'] = $input['first_name'];
        $user['last_name'] = $input['last_name'];
        $user['email'] = $input['email'];
        $user['username'] = $input['username'];
        $password = $input['new_password'];
        if (strlen($password) > 5 && $password == $input['confirm_password']) {
            $user['password'] = $password;
        }
        $this->ion_auth->update($user_id, $user);
        $this->users_model->assignModules($user_id, $input['modules']);

        $this->data['status'] = 'ok';
        $this->render(null, 'json');
    }

    public function checkMail()
    {
        $email = $this->input->post('email', 1);
        $user = $this->ion_auth->user()->row();
        $new = (boolean) $this->input->post('newUser', 1);
        $this->data = $this->ion_auth->email_check($email);
        if ($user->email == $email && $new == false) {
            $this->data = false;
        }
        $this->render(null, 'json');
    }

    public function checkUsername()
    {
        $username = $this->input->post('username', 1);
        $user = $this->ion_auth->user()->row();
        $new = (boolean) $this->input->post('newUser', 1);
        $this->data = $this->ion_auth->username_check($username);
        if ($user->username == $username && $new == false) {
            $this->data = false;
        }
        $this->render(null, 'json');
    }

    public function delete($user_id)
    {
        $this->ion_auth->delete_user($user_id);
        $this->users_model->clearModules($user_id);

        if ($this->ion_auth->get_user_id() == $user_id) {
            $this->ion_auth->logout();
            redirect('login', 'refresh');
        }
        $this->data['status'] = 'ok';
        $this->render(null, 'json');
    }

    public function formatUsers($users = array())
    {
        $return = [];

        $c = count($users);
        for ($i = 0; $i < $c; $i++) {
            $user = $users[$i];

            $return[$i] = [];
            $return[$i]['id'] = $user->id;
            $return[$i]['first_name'] = $user->first_name;
            $return[$i]['last_name'] = $user->last_name;
            $return[$i]['username'] = $user->username;
            $return[$i]['mail'] = $user->email;
            $return[$i]['modules'] = $this->formatUserModules($user->id);
        }
        return $return;
    }

    private function formatUserModules($user_id)
    {
        $mods = [];

        $modules = $this->users_model->getUserMenu($user_id);

        $c = count($modules);
        for ($i = 0; $i < $c; $i++) {
            $mods[] = intval($modules[$i]->id);
        }
        return $mods;
    }

}
