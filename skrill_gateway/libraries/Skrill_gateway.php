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
        $this->setName('Skril');

        /**
         * Add gateway settings
         */
        $this->setSettings([
        ]);
    }
        
    public function process_payment($data)
    {
    }

    public function get_action_url()
    {
    }

    public function finish_payment($post_data)
    {
    }
}