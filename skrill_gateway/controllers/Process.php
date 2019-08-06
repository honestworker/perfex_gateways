<?php
defined('BASEPATH') or exit('No direct script access allowed');

use zvook\Skrill\Models\QuickCheckout;
use zvook\Skrill\Forms\QuickCheckoutForm;

use zvook\Skrill\Models\SkrillStatusResponse;
use zvook\Skrill\Components\SkrillException;

class Process extends App_Controller
{
    public function complete_purchase($amount = 0)
    {
        try {
            $response = new SkrillStatusResponse($_POST);
            
            if ($response->verifySignature($this->skrill_gateway->merchant_secret_word()) && $response->isProcessed()) {
                $invoiceid = substr(str_replace("Invoice-ID-", "", $response->getTransactionId()), 15);
                if ($response->getStatus() == 2) {
                    $success = $this->skrill_gateway->addPayment(
                        [
                          'amount'        => $response->getAmount(),
                          'invoiceid'     => $invoiceid,
                          'transactionid' => $response->getTransactionId()
                        ]
                    );
                    log_activity('Skrill Payment [InvoiceID: ' . $invoiceid . ' TransactionID: ' . $response->getTransactionId() . ' Amount: ' . $response->getAmount() . ' Status: PAID' . ']');
                } else if ($response->getStatus() == -1) {
                    log_activity('Skrill Payment [InvoiceID: ' . $invoiceid . ' TransactionID: ' . $response->getTransactionId() . ' Amount: ' . $response->getAmount() . ' Status: CANCELLED' . ']');
                } else if ($response->getStatus() == -2) {
                    log_activity('Skrill Payment [InvoiceID: ' . $invoiceid . ' TransactionID: ' . $response->getTransactionId() . ' Amount: ' . $response->getAmount() . ' Status: FAILED' . ']');
                }
                $this->load->model('invoices_model');
                $invoice = $this->invoices_model->get($invoiceid);
                echo "Ok";
            }
        } catch (SkrillException $e) {
            if (isset($_GET['salt']) && isset($_GET['transaction_id']) && $amount) {
                $invoiceid = substr(str_replace("Invoice-ID-", "", $_GET['transaction_id']), 15);
                $this->load->model('invoices_model');
                $invoice = $this->invoices_model->get($invoiceid);
                if ($_GET['salt'] == md5($this->skrill_gateway->merchant_secret_salt() . $invoiceid)) {
                    if ($invoice) {
                        log_activity('Skrill Payment [InvoiceID: ' . $invoiceid . ' TransactionID: ' .  $_GET['transaction_id'] . ' Amount: ' . $amount . ' Status: PROCESSED' . ']');
                        redirect(site_url('invoice/' . $invoiceid . '/' . $invoice->hash));
                    } else {
                        redirect(site_url('/'));
                    }
                } else {
                    if ($invoice) {
                        redirect(site_url('invoice/' . $invoiceid . '/' . $invoice->hash));
                    } else {
                        redirect(site_url('/'));
                    }
                }
            }
        }
    }
    
    public function make_payment()
    {
        check_invoice_restrictions($this->input->get('invoiceid'), $this->input->get('hash'));
        $this->load->model('invoices_model');
        $invoice = $this->invoices_model->get($this->input->get('invoiceid'));
        load_client_language($invoice->clientid);
        $data['invoice']      = $invoice;
        $billing_country      = get_country($invoice->billing_country);
        $data['total']        = $this->session->userdata('total_authorize');
        $data['billing_name'] = '';
        if (is_client_logged_in()) {
            $contact              = $this->clients_model->get_contact(get_contact_user_id());
            $data['billing_name'] = $contact->firstname . ' ' . $contact->lastname;
        } else {
            if (total_rows(db_prefix().'contacts', ['userid' => $invoice->clientid]) == 1) {
                $contact = $this->clients_model->get_contact(get_primary_contact_user_id($invoice->clientid));
                if ($contact) {
                    $data['billing_name'] = $contact->firstname . ' ' . $contact->lastname;
                }
            }
        }
        echo $this->get_html($data);
    }

    public function get_html($data = [])
    {
        $quickCheckout = new QuickCheckOut([
            'pay_to_email' => $this->skrill_gateway->merchant_email(),
            'amount' => $data['total'],
            'currency' => $data['invoice']->currency_name,
        ]);
        
        $amount2 = 0;
        $description2 = '';
        $amount3 = 0;
        $description3 = '';
        foreach ($data['invoice']->items as $item) {
            $rate = str_replace(",", "", $item['rate']) * (float)$item['qty'];
            $amount2 += $rate;
            if ($description2) $description2 .= ", ";
            $description2 .= $item['description'];
            
            $tax_data = get_invoice_item_taxes($item['id']);
            if (is_array($tax_data)) {
                if (count($tax_data)) {
                    foreach ($tax_data as $tax) {
                        $amount3 += $rate * $tax['taxrate'] / 100;
                        if ($description3) $description3 .= ", ";
                        $description3 .= $tax['taxname'];
                    }
                }
            }
        }
        
        $quickCheckout->setAmount2($amount2);
        $quickCheckout->setAmount2Description($description2);
        if ($amount3) {
            $quickCheckout->setAmount3($amount3);
            $quickCheckout->setAmount3Description($description3);
        }
        
        $amount4 = 0;
        $description4 = '';
        if (isset($data['invoice']->payments)) {
            foreach ($data['invoice']->payments as $payment) {
                $amount4 -= (float)$payment['amount'];
                if ($description4) $description4 .= ", ";
                $description4 .=$payment['name'];
            }
        }
        if ($amount4) {
            if ($amount3) {
                $quickCheckout->setAmount4($amount4);
                $quickCheckout->setAmount4Description($description4);
            } else {
                $quickCheckout->setAmount3($amount4);
                $quickCheckout->setAmount3Description($description4);
            }
        }
        
        if (is_client_logged_in()) {
            $contact    = $this->clients_model->get_contact(get_contact_user_id());
            if ($contact->firstname) {
                $quickCheckout->setFirstname($contact->firstname);
            } else {
                $quickCheckout->setFirstname('John Santamaria');
            }
            if ($contact->lastname) {
                $quickCheckout->setLastname($contact->lastname);
            } else {
                $quickCheckout->setLastname('Smith');
            }
        } else {
            $contacts = $this->clients_model->get_contacts($data['invoice']->clientid);
            if (count($contacts) == 1) {
                $contact    = $contacts[0];
                if ($contact['firstname']) {
                    $quickCheckout->setFirstname($contact['firstname']);
                } else {
                    $quickCheckout->setFirstname('John Santamaria');
                }
                if ($contact['lastname']) {
                    $quickCheckout->setLastname($contact['lastname']);
                } else {
                    $quickCheckout->setLastname('Smith');
                }
            } else {
                if ($data['invoice']->client->company) {
                    $quickCheckout->setFirstname($data['invoice']->client->company);
                    $quickCheckout->setLastname($data['invoice']->client->company);
                } else {
                    $quickCheckout->setFirstname('John Santamaria');
                    $quickCheckout->setLastname('Smith');
                }
            }
        }
        
        $phone = '0207123456';
        if (isset($data['invoice']->client->phonenumber)) {
            if ($data['invoice']->client->phonenumber) {
                $phone = preg_replace('/\s+/', '', $data['invoice']->client->phonenumber);
            }
        }
        $quickCheckout->setPhoneNumber($phone);
        $country = 'GBR';
        if (isset($data['invoice']->client->country)) {
            if ($data['invoice']->client->country) {
                $ccountry = get_country($data['invoice']->client->country);
                $country = $ccountry->iso3;
            }
        }
        $quickCheckout->setCountry($country);
        $address = 'Payer street';
        if (isset($data['invoice']->client->address)) {
            if ($data['invoice']->client->address) {
                $address = $data['invoice']->client->address;
            }
        }
        $quickCheckout->setAddress($address);
        $city = 'London';
        if (isset($data['invoice']->client->city)) {
            if ($data['invoice']->client->city) {
                $city = $data['invoice']->client->city;
            }
        }
        $quickCheckout->setCity($city);
        $state = 'Central London';
        if (isset($data['invoice']->client->state)) {
            if ($data['invoice']->client->state) {
                $state = $data['invoice']->client->state;
            }
        }
        $quickCheckout->setState($state);
        $postal_code = 'EC45MQ';
        if (isset($data['invoice']->client->zip)) {
            if ($data['invoice']->client->zip) {
                $postal_code = $data['invoice']->client->zip;
            }
        }
        $quickCheckout->setPostalCode($postal_code);
        
        $quickCheckout->setRecipientDescription($data['invoice']->clientnote);
        $quickCheckout->setTransactionId('Invoice-ID-' . date("YmdHis") . "-". $data['invoice']->id);
        $quickCheckout->setStatusUrl(site_url('skrill_gateway/process/complete_purchase/'));
        $quickCheckout->setReturnUrl(site_url('skrill_gateway/process/complete_purchase/' . $data['total'] . '?salt=' . md5($this->skrill_gateway->merchant_secret_salt() . $data['invoice']->id)));
        $quickCheckout->setReturnUrlTarget(QuickCheckout::URL_TARGET_SELF);
        
        $form = new QuickCheckoutForm($quickCheckout);
        
        $contents = $form->open([
            'class' => 'skrill-form',
            'target' => '_self'
        ]);
        
        $excludes = [];
        $contents .= $form->renderHidden($excludes);
        $contents .= $form->renderSubmit('Pay', ['class' => 'btn']);
        $contents .= $form->close();
        $contents .= "<script type='text/javascript'>
                        window.addEventListener('load', function () {
                            document.getElementsByClassName('skrill-form')[0].submit();
                        }, false);
                    </script>";
        return $contents;
    }
}