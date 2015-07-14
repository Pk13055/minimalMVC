<?php
error_reporting(E_ALL);

if (!defined('APPPATH')) {
    define('APPPATH', realpath(dirname(__FILE__) . '/../app' ). '/');
}
define('SYSPATH', realpath(dirname(__FILE__) . '/') . '/');

/*
 * Require app config
 */
require(APPPATH . 'config.php');

/*
 * Set unset configs
 */
if (empty($_base_url)) {
    $_REQUEST_SCHEME = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
    $_PATH = dirname($_SERVER['SCRIPT_NAME']);
    if (substr($_PATH, -1) != '/') $_PATH .= '/';
    $_base_url = $_REQUEST_SCHEME.'://'.$_SERVER['HTTP_HOST'].$_PATH;
}

/*
 * Helper implementation
 */
function load_helper($helper) {
    $helper_file = APPPATH . 'helpers/'.$helper.'.php';
    if (is_file($helper_file)) {
        include_once($helper_file);
    }
    else {
        include_once(SYSPATH . 'helpers/'.$helper.'.php');
    }
}

/*
 * Load common helpers
 */
load_helper('base_url');

/*
 * Load system
 */
require(SYSPATH . 'router.php');
require(SYSPATH . 'controller.php');
require(SYSPATH . 'model.php');
require(SYSPATH . 'library.php');

/*
 * Understand URL and decompose route
 */
$router = new Router(empty($_SERVER['PATH_INFO']) ? '' : $_SERVER['PATH_INFO']);
$controller = $router->get_controller();
$method = $router->get_method();
$args = $router->get_args();

/*
 * Load requested controller
 */
$not_found = true;

$controller_file = APPPATH . 'controllers/'.$controller.'.php';
if (file_exists($controller_file) && is_readable($controller_file)) {
    include($controller_file);
    $a=new $controller;

    if (method_exists($a, $method)) {
        $not_found = false;
        call_user_func_array( array( $a, $method ), $args );
    }
}

if ($not_found) {
    header('HTTP/1.0 404 Not Found');
    new View('404');
    exit();
}
