<?php

if (!function_exists('active_menu')) {

    function active_menu($controller = null, $classname = 'menu-open') {
        // Getting CI class instance.
        $CI = get_instance();

        // Getting router class to active.
        $uri = $CI->uri->segment(1);
        $class = $CI->router->fetch_class();

        if ($controller == null && $class == 'index') {
            return $classname;
        }
        return ($uri == $controller) ? $classname : '';
    }

}

if (!function_exists('active_uri')) {

    function active_uri($active, $classname = 'active') {
        // Getting CI class instance.
        $CI = get_instance();

        $uri = $CI->uri->uri_string();
        return ($uri == $active) ? $classname : '';
    }

}

if (!function_exists('active_child')) {

    function active_child($children = array(), $classname = 'active') {
        // Getting CI class instance.
        $CI = get_instance();

        $uri = $CI->uri->uri_string();

        $c = count($children);
        for ($i = 0; $i < $c; $i++) {
            $url = 'admin/' . $children[$i]->controller . '/index/' . $children[$i]->id;
            if ($uri == $url) {
                return $classname;
            }
        }
    }

}

if (!function_exists('active_parent')) {

    function active_parent($parent_name, $children = array(), $classname = 'active') {
        // Getting CI class instance.
        $CI = get_instance();

        $uri = $CI->uri->uri_string();

        $c = count($children);
        for ($i = 0; $i < $c; $i++) {

            $substr = strpos($uri, $parent_name . '/' . $children[$i]->alias);
            if ($substr !== false) {
                echo $classname;
            }
        }
    }

}

if (!function_exists('active_method')) {

    function active_method($method) {
        // Getting CI class instance.
        $CI = get_instance();
        // Getting router class to active.
        $class = $CI->router->fetch_method();
        return ($class == $method) ? 'class="active"' : '';
    }

}

if (!function_exists('is_local')) {

    function is_local() {
        $parts = explode('.', $_SERVER['SERVER_NAME']);
        if (end($parts) == 'lc') {
            return true;
        }
        return false;
    }

}