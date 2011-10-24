<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Paypal_ipn {

	const BUYNOW = '_xclick';
	const SUBSCRIPTION = '_xclick-subscriptions';
	const DONATE = '_donations';
	const GIFTCERT = '_oe-gift-certificate';
	const CART = '_cart';

	private $_fields = array();
	private $_response = array();
	private $_paypal_url = '';

	public function __construct()
	{
		$this->set_field('rm','2');
		$this->set_type(Paypal_ipn::BUYNOW);
		$this->set_url('https://www.paypal.com/cgi-bin/webscr');
	}

	public function set_type($type = Paypal_ipn::BUYNOW)
	{
		$this->set_field('cmd', $type);
	}

	public function set_field($field, $value)
	{
		$this->_fields[$field] = $value;
	}

	public function set_url($url)
	{
		$this->_paypal_url = $url;
	}

	public function form($name = 'paypal_form', $submit = 'default')
	{
		$CI = & get_instance();
		$CI->load->helper('form');

		if ($submit = 'default')
		{
			$submit = array(
				'name'	=> 'pp_submit',
				'id'	=> 'pp_submit',
				'value'	=> 'Continue to Paypal...',
			);
		}
		$form = '';
		$form .= '<form action="'.$this->_paypal_url.'" method="post" name="'.$name.'" id="'.$name.'">'.PHP_EOL;

		foreach ($this->_fields as $key => $value)
		{
			$form .= form_hidden($key, $value).PHP_EOL;
		}

		$form .= form_submit($submit).PHP_EOL;
		$form .= form_close().PHP_EOL;
		return $form;
	}

	public function validate()
	{
		$url_parsed = parse_url($this->_paypal_url);

		$post_string = '';
		if ($_POST)
		{
			foreach ($_POST as $key => $val)
			{
				$this->_response[$key] = $val;
				$post_string .= $key.'='.urlencode(stripslashes($val)).'&';
			}
		}

		$post_string .= "cmd=_notify-validate";

		if ($this->_response['payment_status'] !== 'Completed')
		{
			return FALSE;
		}

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_HEADER , 0);
		curl_setopt($curl, CURLOPT_VERBOSE, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_URL, $this->_paypal_url);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post_string);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded", "Content-Length: " . strlen($post_string)));
		$result = curl_exec($curl);

		if ($result === "VERIFIED")
		{
			return $this->_response;
		}
		else
		{
			return FALSE;
		}
	}


	public function debug_fields()
	{
		ksort($this->_fields);
		echo '<h3>Paypal Fields</h3>'.PHP_EOL;
		echo '<pre>'.PHP_EOL;
		print_r($this->_fields);
		echo "</pre>\n";
		return;
	}

	public function debug_response()
	{
		ksort($this->_response);
		echo '<h3>Paypal Reponse</h3>'.PHP_EOL;
		echo '<pre>'.PHP_EOL;
		print_r($this->_response);
		echo "</pre>\n";
		return;
	}

}

/* End of file Paypal_ipn.php */