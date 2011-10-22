<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Pancake
 *
 * A simple, fast, self-hosted invoicing application
 *
 * @package		Pancake
 * @author		Pancake Dev Team
 * @copyright	Copyright (c) 2010, Pancake Payments
 * @license		http://pancakeapp.com/license
 * @link		http://pancakeapp.com
 * @since		Version 1.0
 */

// ------------------------------------------------------------------------

/**
 * The Transaction Controller
 *
 * @subpackage	Controllers
 * @category	Payments
 */
class Transaction extends Public_Controller {

	/**
	 * Set the layout.
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct()
	{
            parent::Public_Controller();
            require_once APPPATH.'modules/gateways/gateway.php';
            $this->template->set_layout('transaction');
	}

	// ------------------------------------------------------------------------

	/**
	 * Sets up the payment gateway form then outputs it with an auto-submit.
	 *
	 * @access	public
	 * @param	string	The unique id of the payment.
	 * @return	void
	 */
	public function process($unique_id, $gateway = null) {
            $this->load->model('invoices/invoice_m');
            $this->load->model('invoices/partial_payments_m', 'ppm');

            $part = $this->ppm->getPartialPayment($unique_id);
            $invoice = $part['invoice'];
            unset($part['invoice']);
            
            if (!isset($part['id']) or count(Gateway::get_frontend_gateways($invoice['real_invoice_id'])) == 0) {
                redirect('');
                return;
            }
            
            $gateways = Gateway::get_frontend_gateways($invoice['id']);
            
            if (!$gateway and count($gateways) == 1) {
                foreach ($gateways as $key => $value) {
                    $gateway = $key;
                }
            }
            
            $item_name = 'Invoice #' . $invoice['invoice_number'];
            $amount = $part['billableAmount'];
            $success = site_url('transaction/success/' . $unique_id . '/' . $gateway);
            $cancel = site_url('transaction/cancel/' . $unique_id . '/' . $gateway);
            $notify = site_url('transaction/ipn/' . $unique_id . '/' . $gateway);
            $currency_code = $invoice['currency_code'] ? $invoice['currency_code'] : Currency::code();
            
            if ($gateway) {
                $this->load->model('gateways/'.$gateway, 'gateway');
                $this->template->gateway = $this->gateway->title;
                $this->template->form = $this->gateway->generate_payment_form($unique_id, $item_name, $amount, $success, $cancel, $notify, $currency_code);
                $this->template->build('transaction');
            } else {
                $this->template->set_layout('simple');
                $this->template->gateways = $gateways;
                $this->template->payment_url = $part['payment_url'];
		$this->template->build('select_payment_method');
            }
        }

	// ------------------------------------------------------------------------

	/**
	 * Displays a basic "payment cancelled" page
	 *
	 * @access	public
	 * @return	void
	 */
	public function cancel($gateway)
	{
		$this->load->model('gateways/'.$gateway, 'gateway');
		$this->gateway->process_cancel($unique_id);
		$this->template->build('cancel');
	}

	// ------------------------------------------------------------------------

	/**
	 * Displays a basic "payment success" page
	 *
	 * @access	public
	 * @return	void
	 */
	public function success($unique_id, $gateway)
	{
		$this->load->model('invoices/invoice_m');
		$this->load->model('files/files_m');
		
		$this->load->model('gateways/'.$gateway, 'gateway');
		$data = $this->gateway->process_success($unique_id);
		
		if (isset($data['txn_id'])) {
		    # This gateway uses the success page as the IPN. We need to treat it as such.
		    Gateway::complete_payment($unique_id, $gateway, $data);
		}

		$this->template->set_theme(PAN::setting('theme'));
		$this->template->invoice = (array) $this->invoice_m->get_by_unique_id($unique_id);
		$this->template->files = (array) $this->files_m->get_by_unique_id($unique_id);
		$this->template->unique_id = $unique_id;

		$this->template->build('success');
	}
	// ------------------------------------------------------------------------

	/**
	 * Processes the payment notification info from the payment gateway used. 
         * It then sends out the receipt and notification emails.
	 *
	 * @access	public
	 * @param	string	The unique id of the payment.
	 * @return	void
	 */
	public function ipn($unique_id, $gateway) {
            
            $this->load->model('gateways/'.$gateway, 'gateway');
            $this->load->model('invoices/partial_payments_m', 'ppm');
            $part = $this->ppm->getPartialPayment($unique_id);
            $data = $this->gateway->process_notification($unique_id);

            Gateway::complete_payment($unique_id, $gateway, $data);
    }
}