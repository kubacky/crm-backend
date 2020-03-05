<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
  | -------------------------------------------------------------------------
  | Hooks
  | -------------------------------------------------------------------------
  | This file lets you define "hooks" to extend CI without hacking the core
  | files.  Please see the user guide for info:
  |
  |	http://codeigniter.com/user_guide/general/hooks.html
  |
 */
$hook['pre_system'][] = array(
    'class' => '',
    'function' => 'load_app_controllers',
    'filename' => 'App_controllers.php',
    'filepath' => 'hooks'
);

$hook['pre_controller'][] = array(
    'class' => '',
    'function' => 'loadController',
    'filename' => 'App_controllers.php',
    'filepath' => 'hooks'
);

/*
$hook['post_controller_constructor'][] = array(
    'function' => 'redirect_ssl',
    'filename' => 'ssl.php',
    'filepath' => 'hooks'
);
 * 
 */