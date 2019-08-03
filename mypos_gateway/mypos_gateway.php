<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: myPOS.eu
Description: myPOS.eu Payment Gateway module
Version: 1.0.0
Requires at least: 1.0.*
*/

define('MYPOS_GATEWAY_MODULE_NAME', 'mypos_gateway');

$CI = &get_instance();

/**
 * Load the module helper
 */
$CI->load->helper(MYPOS_GATEWAY_MODULE_NAME . '/mypos_gateway');

/**
 * Register activation module hook
 */
register_activation_hook(MYPOS_GATEWAY_MODULE_NAME, 'mypos_gateway_activation_hook');

function mypos_gateway_activation_hook()
{
    require_once(__DIR__ . '/install.php');
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(MYPOS_GATEWAY_MODULE_NAME, [MYPOS_GATEWAY_MODULE_NAME]);

/**
 * Actions for inject the custom styles
 */
hooks()->add_filter('module_mypos_gateway_action_links', 'module_mypos_gateway_action_links');

/**
 * Add additional settings for this module in the module list area
 * @param  array $actions current actions
 * @return array
 */
function module_mypos_gateway_action_links($actions)
{
    $actions[] = '<a href="' . admin_url('settings?group=payment_gateways&tab=online_payments_mypos_tab') . '">' . _l('settings') . '</a>';

    return $actions;
}

register_payment_gateway('mypos_gateway', 'mypos_gateway');