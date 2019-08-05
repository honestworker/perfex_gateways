<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Process extends App_Controller
{
    public function complete_purchase()
    {
        $oResponse = $this->mercadopago_gateway->finish_payment($this->input->post());
        
        $invoiceid = $oResponse['ORDERID'];
        
        $this->load->model('invoices_model');
        $invoice = $this->invoices_model->get($oResponse['ORDERID']);
        
        load_client_language($invoice->clientid);
        
        if ($oResponse['IPCMETHOD'] == 'IPCPurchaseCancel') {
            redirect(site_url('invoice/' . $invoiceid . '/' . $invoice->hash));
        } else if ($oResponse['IPCMETHOD'] == 'IPCPurchaseOK') {
            log_activity('Mercadopago Payment [InvoiceID: ' . $invoiceid . ' TransactionID: ' . $oResponse['IPC_TRNREF'] . ' Amount: ' . $oResponse['AMOUNT'] . ' Status: UNPAID' . ']');
            redirect(site_url('invoice/' . $invoiceid . '/' . $invoice->hash));
        } else if ($oResponse['IPCMETHOD'] == 'IPCPurchaseNotify') {
            log_activity('Mercadopago Payment [InvoiceID: ' . $invoiceid . ' TransactionID: ' . $oResponse['IPC_TRNREF'] . ' Amount: ' . $oResponse['AMOUNT'] . ' Status: PAID' . ']');
            $success = $this->mercadopago_gateway->addPayment(
                [
                  'amount'        => $oResponse['AMOUNT'],
                  'invoiceid'     => $invoiceid,
                  'transactionid' => $oResponse['IPC_TRNREF']
                ]
            );
            echo "OK";
        } else if ($oResponse['IPCMETHOD'] == 'IPCPurchaseRollback') {
            log_activity('Mercadopago Payment [InvoiceID: ' . $invoiceid . ' Amount: ' . $oResponse['AMOUNT'] . ' Status: CANCELLED' . ']');
        }
    }
}