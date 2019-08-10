<?php

defined('BASEPATH') or exit('No direct script access allowed');

add_option('skrill_gateway', 'enable');

$config['csrf_protection'] = TRUE;
if(isset($_SERVER["PHP_SELF"])){
    $parts = explode("/", $_SERVER["PHP_SELF"]);
    $exclude_url_arr = array('login');
    if (!empty($exclude_url_arr[0])) {
        foreach($parts as $part) {
            if (in_array($part, $exclude_url_arr)) {
                $config['csrf_protection'] = FALSE;
                break;
            }
        }
    }
}