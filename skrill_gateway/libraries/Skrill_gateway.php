<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Skrill_gateway extends App_gateway
{    
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
        $this->setId('skrill');

        /**
         * REQUIRED
         * Gateway name
         */
        $this->setName('Skrill');

        /**
         * Add gateway settings
         */
        $this->setSettings([
            [
                'name'              => 'api_merchant_email',
                'label'             => 'Merchant Email',
                'type'              => 'input'
            ],
            [
                'name'              => 'api_merchant_secret_word',
                'label'             => 'Merchant Secret Word',
                'type'              => 'input'
            ],
            [
                'name'              => 'api_merchant_secret_salt',
                'label'             => 'Merchant Secret Salt',
                'type'              => 'input',
                'default_value'     => 'skrill123',
            ]
        ]);
    }
        
    public function process_payment($data)
    {
        $this->ci->session->set_userdata([
            'total_authorize' => $data['amount'],
        ]);

        redirect(site_url('skrill_gateway/process/make_payment?invoiceid=' . $data['invoiceid'] . '&total=' . $data['amount'] . '&hash=' . $data['invoice']->hash));
    }
    
    public function merchant_email() {
        return $this->getSetting('api_merchant_email');
    }
    
    public function merchant_secret_word() {
        return $this->getSetting('api_merchant_secret_word');
    }

    public function merchant_secret_salt() {
        return $this->getSetting('api_merchant_secret_salt');
    }
    
    public function finish_payment($post_data)
    {
    }
}