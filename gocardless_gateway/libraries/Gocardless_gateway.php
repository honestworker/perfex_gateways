<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Gocardless_gateway extends App_gateway
{
    protected $sandbox_url = 'https://api-sandbox.gocardless.com/';
    protected $production_url = 'https://api.gocardless.com/';

    protected $support_currency = ["AUD", "CAD", "DKK", "EUR", "GBP", "NZD", "SEK", "USD" ];

    public function __construct()
    {
        /**
        * Call App_gateway __construct function
        */
        parent::__construct();
        
        $this->ci = & get_instance();
        
        /**
         * Gateway unique id - REQUIRED
         */
        $this->setId('gocardless');

        /**
         * REQUIRED
         * Gateway name
         */
        $this->setName('GoCardless');

        /**
         * Add gateway settings
         */
        $this->setSettings([
            [
                'name'              => 'api_top_secret_access_token',
                'label'             => 'Secret Access Token',
                'type'              => 'input'
            ],
            [
                'name'              => 'api_version',
                'label'             => 'API version',
                'type'              => 'input',
                'default_value'     => '2015-07-06'
            ],
            [
                'name'              => 'test_mode_enabled',
                'type'              => 'yes_no',
                'default_value'     => 0,
                'label'             => 'settings_paymentmethod_testing_mode',
            ],
        ]);
    }
    
    public function process_payment($data)
    {
        if (in_array($data['invoice']->currency_name, $this->support_currency)) {
            if ($this->getSetting('test_mode_enabled')) {
                $client = new \GoCardlessPro\Client(array(
                    'access_token' => $this->getSetting('api_top_secret_access_token'),
                    'environment'  => \GoCardlessPro\Environment::SANDBOX
                ));
            } else {
                $client = new \GoCardlessPro\Client(array(
                    'access_token' => $this->getSetting('api_top_secret_access_token'),
                    'environment'  => \GoCardlessPro\Environment::LIVE
                ));
            }
            
            $client->payments()->create([
              "params" => [
                    "amount" => $data['invoice']->total * 100,
                    "currency" => $data['invoice']->currency_name,
                    "description" => $data['invoice']->clientnote,
                    "metadata" => [
                        "invoice_number" => 'Invoice-ID-' . date("YmdHis") . "-". $data['invoice']->id
                    ],
                    "links" => [
                        "mandate" => "MD123"
                    ],
                ]
            ]);
        }
    }

    public function get_action_url()
    {
    }

    public function finish_payment($post_data)
    {
    }
}