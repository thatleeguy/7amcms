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
 * The Authorize.net Gateway
 *
 * @subpackage	Gateway
 * @category	Payments
 */
class Authorizenet_m extends Gateway {

    public $title = 'Authorize.net';
    public $frontend_title = 'Credit Card (via Authorize.net)';
    public $version = '0.1';
    public $api_key;
    public $transaction_key;

    public function __construct() {
	parent::__construct(__CLASS__);
	$this->fields = array('api_key' => __('global:api_key'), 'transaction_key' => lang('authorize:transaction_key'));
    }

    public function generate_payment_form($unique_id, $item_name, $amount, $success, $cancel, $notify, $currency_code) {

	require_once APPPATH . 'libraries/authorize/AuthorizeNet.php';
	$this->api_key = $this->get_field('api_key');
	$this->transaction_key = $this->get_field('transaction_key');

	$CI = &get_instance();
	$CI->load->model('invoices/partial_payments_m', 'ppm');
	
	# Convert $amount to USD if $currency_code is NOT USD
	if ($currency_code != 'USD') {
	   $amount = $CI->ppm->getUsdAmountByAmountAndUniqueId($amount, $unique_id);
	}
	
	# Let's round the amount.
	$amount = round($amount, 2);

	$click_here = lang('paypal:clickhere');
	$fp_timestamp = time();
	$fp_sequence = $CI->ppm->getIdByUniqueId($unique_id); // Enter an invoice or other unique number.
	$fingerprint = AuthorizeNetSIM_Form::getFingerprint($this->api_key, $this->transaction_key, $amount, $fp_sequence, $fp_timestamp);
	
	$form = '<form method="post" action="https://secure.authorize.net/gateway/transact.dll">';
	$sim = new AuthorizeNetSIM_Form(
			array(
			    'x_amount' => $amount,
			    'x_fp_sequence' => $fp_sequence,
			    'x_fp_hash' => $fingerprint,
			    'x_fp_timestamp' => $fp_timestamp,
			    'x_receipt_link_method' => 'POST',
			    'x_receipt_link_text' => __('gateways:returntowebsite', array(Settings::get('site_name'))),
			    'x_receipt_link_url' => $success,
			    'x_login' => $this->api_key,
			    'x_method' => 'cc',
			    'x_show_form' => 'payment_form'
			)
	);
	$form .= $sim->getHiddenFieldString();
	$form .= '<input type="submit" value="' . $click_here . '"></form>';
	return $form;
    }

    public function process_cancel($unique_id) {
	
    }

    public function process_success($unique_id) {
	return $this->process_notification($unique_id);
    }

    public function process_notification($unique_id) {

	define('AUTHORIZENET_API_LOGIN_ID', $this->get_field('api_key'));
	define('AUTHORIZENET_MD5_SETTING', $this->get_field('api_key'));
	require_once APPPATH . 'libraries/authorize/AuthorizeNet.php';
	$response = new AuthorizeNetSIM;
	if ($response->isAuthorizeNet()) {
	    if ($response->approved) {
		return array(
		    'txn_id' => $response->transaction_id,
		    'payment_gross' => $response->amount,
		    'payment_date' => time(),
		    'payment_type' => 'instant',
		    'payer_status' => 'verified',
		    'payment_status' => 'Completed',
		    'item_name' => $unique_id,
		    'is_paid' => 1,
		);
	    } else {
		return false;
	    }
	} else {
	    return false;
	}
    }

}