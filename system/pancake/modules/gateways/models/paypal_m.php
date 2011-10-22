<?php

defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Pancake
 *
 * A simple, fast, self-hosted invoicing application
 *
 * @package		Pancake
 * @author		Pancake Dev Team
 * @copyright           Copyright (c) 2010, Pancake Payments
 * @license		http://pancakeapp.com/license
 * @link		http://pancakeapp.com
 * @since		Version 2.2.0
 */
// ------------------------------------------------------------------------

/**
 * The PayPal Gateway
 *
 * @subpackage	Gateway
 * @category	Payments
 */
class Paypal_m extends Gateway {

    public $title = 'PayPal';
    public $version = '0.1';

    public function __construct() {
        parent::__construct(__CLASS__);
        $this->fields = array('paypal_email' => lang('paypal:email'));
        $this->load->library('paypal_lib');
    }

    public function generate_payment_form($unique_id, $item_name, $amount, $success, $cancel, $notify, $currency_code) {

	# Let's round the amount.
	$amount = round($amount, 2);
	
        // Setup the paypal request
        $this->paypal_lib->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
        $this->paypal_lib->add_field('business', $this->get_field('paypal_email'));
        $this->paypal_lib->add_field('return', $success);
        $this->paypal_lib->add_field('cancel_return', $cancel);
        $this->paypal_lib->add_field('notify_url', $notify);
        $this->paypal_lib->add_field('item_name', $item_name);
        $this->paypal_lib->add_field('amount', $amount);
        $this->paypal_lib->add_field('currency_code', $currency_code);
        $this->paypal_lib->add_field('custom', $unique_id);
        $this->paypal_lib->button(lang('paypal:clickhere'));

        return $this->paypal_lib->paypal_form();
    }

    public function process_cancel($unique_id) {
        
    }

    public function process_success($unique_id) {
        
    }

    public function process_notification($unique_id) {
        if ($this->paypal_lib->validate_ipn()) {
            return array(
                'txn_id' => $this->paypal_lib->ipn_data['txn_id'],
                'payment_gross' => $this->paypal_lib->ipn_data['mc_gross'],
		'transaction_fee' => $this->paypal_lib->ipn_data['mc_fee'],
                'payment_date' => strtotime($this->paypal_lib->ipn_data['payment_date']),
                'payment_type' => $this->paypal_lib->ipn_data['payment_type'],
                'payer_status' => $this->paypal_lib->ipn_data['payer_status'],
                'payment_status' => $this->paypal_lib->ipn_data['payment_status'],
                'item_name' => $this->paypal_lib->ipn_data['item_name'],
                'is_paid' => 1,
            );
        }
    }

}