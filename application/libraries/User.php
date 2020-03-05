<?php

class User {

    protected $_user;

    public function __construct($user) {
        $this->_user = $user;
    }

    public function sanitize() {
        $user = array();
        $user['id'] = $this->_user['id'];
        $user['first_name'] = $this->_user->first_name;
        $user['last_name'] = $this->user->last_name;
        $user['email'] = $this->_user->email;
        $user['gender'] = $this->gender($user->first_name);

        $this->_user = $user;
    }

    public function user() {
        return $this->_user;
    }

    //simple gender check. If first name ends on 'a' it means it's a woman
    private function gender($name) {
        $return = 'mr';

        $last = strtolower(substr($name, count($name) - 1, 1));
        if ($last == 'a') {
            $return = 'mrs';
        }
        return $return;
    }

}
