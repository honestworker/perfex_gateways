<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Skrill
Description: Skrill Payment Gateway module
Version: 1.0.0
Requires at least: 1.0.*
*/

define('SKRILL_GATEWAY_MODULE_NAME', 'skrill_gateway');

$CI = &get_instance();

/**
 * Load the module helper
 */
$CI->load->helper(SKRILL_GATEWAY_MODULE_NAME . '/skrill_gateway');

/**
 * Register activation module hook
 */
register_activation_hook(SKRILL_GATEWAY_MODULE_NAME, 'skrill_gateway_activation_hook');

function skrill_gateway_activation_hook()
{
    require_once(__DIR__ . '/install.php');
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(SKRILL_GATEWAY_MODULE_NAME, [SKRILL_GATEWAY_MODULE_NAME]);

/**
 * Actions for inject the custom styles
 */
hooks()->add_filter('module_skrill_gateway_action_links', 'module_skrill_gateway_action_links');

/**
 * Add additional settings for this module in the module list area
 * @param  array $actions current actions
 * @return array
 */
function module_skrill_gateway_action_links($actions)
{
    $actions[] = '<a href="' . admin_url('settings?group=payment_gateways&tab=online_payments_skrill_tab') . '">' . _l('settings') . '</a>';

    return $actions;
}

register_payment_gateway('skrill_gateway', 'skrill_gateway');