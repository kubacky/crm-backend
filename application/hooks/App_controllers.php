<?php

defined('DS') || define('DS', DIRECTORY_SEPARATOR);

function load_app_controllers() {
    spl_autoload_register('my_own_controllers');
}

function my_own_controllers($class) {
    if (strpos($class, 'CI_') !== 0) {
        if (is_readable(APPPATH . 'core/' . $class . '.php')) {
            require_once(APPPATH . 'core/' . $class . '.php');
        }
    }
}

if (!function_exists('loadController')) {

    function loadController($controller) {
        if (is_readable(APPPATH . 'controllers' . DS . $controller . '.php')) {
            require_once(APPPATH . 'controllers' . DS . $controller . '.php');
        }
    }

}