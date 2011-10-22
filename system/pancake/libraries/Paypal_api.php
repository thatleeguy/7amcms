<?php
class Paypal_api {
	private $_curl;
	private $_username;
	private $_password;
	private $_api_signature;
	private $_live = TRUE;
	public function __construct($username = '', $password = '', $api_signature = '', $live = TRUE)
	{
		$this->_username = $username;
		$this->_password = $password;
		$this->_api_signature = $api_signature;
		$this->_live = $live;
		$this->_curl = curl_init();
	}
	public function __call($name, $params)
	{
		$function = implode(array_map('ucfirst', explode('_', $name)));
		return $this->_api_call($function, isset($params[0]) ? $params[0] : array());
	}
	private function _api_call($function, $params)
	{
	    curl_setopt($this->_curl, CURLOPT_USERAGENT, "CI_PayPal_API_Lib");
	    curl_setopt($this->_curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($this->_curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, TRUE);
	    curl_setopt($this->_curl, CURLOPT_POST, TRUE);
		$params = array_merge(array(
			'USER'		=> $this->_username,
			'PWD'		=> $this->_password,
			'VERSION'	=> '64.0',
			'SIGNATURE'	=> $this->_api_signature,
			'METHOD'	=> $function,
		), $params);
		curl_setopt($this->_curl, CURLOPT_URL, 'https://api-3t.'.($this->_live ? '' : 'sandbox.').'paypal.com/nvp');
	    curl_setopt($this->_curl, CURLOPT_POSTFIELDS, http_build_query($params));
		parse_str(curl_exec($this->_curl), $response);
		return $response;
	}
}