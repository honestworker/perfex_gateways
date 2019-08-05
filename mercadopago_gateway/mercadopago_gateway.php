<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Mercado Pago
Description: Mercado Pago Payment Gateway module
Version: 1.0.0
Requires at least: 1.0.*
*/

define('MERCADOPAGO_GATEWAY_MODULE_NAME', 'mercadopago_gateway');

$CI = &get_instance();

/**
 * Load the module helper
 */
$CI->load->helper(MERCADOPAGO_GATEWAY_MODULE_NAME . '/mercadopago_gateway');

/**
 * Register activation module hook
 */
register_activation_hook(MERCADOPAGO_GATEWAY_MODULE_NAME, 'mercadopago_gateway_activation_hook');

function mercadopago_gateway_activation_hook()
{
    require_once(__DIR__ . '/install.php');
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(MERCADOPAGO_GATEWAY_MODULE_NAME, [MERCADOPAGO_GATEWAY_MODULE_NAME]);

/**
 * Actions for inject the custom styles
 */
hooks()->add_filter('module_mercadopago_gateway_action_links', 'module_mercadopago_gateway_action_links');

/**
 * Add additional settings for this module in the module list area
 * @param  array $actions current actions
 * @return array
 */
function module_mercadopago_gateway_action_links($actions)
{
    $actions[] = '<a href="' . admin_url('settings?group=payment_gateways&tab=online_payments_mercadopago_tab') . '">' . _l('settings') . '</a>';

    return $actions;
}

register_payment_gateway('mercadopago_gateway', 'mercadopago_gateway');