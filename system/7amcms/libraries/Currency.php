<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Currency Library
 */
class Currency {

	private static $_current = 'USD';

	// A default set of currencies, to add more use the config file
	private static $_currencies;

	public function __construct($params = array())
	{
		if (isset($params['currencies']))
		{
            foreach ($params['currencies'] as $code => $array)
			{
                $params['currencies'][$code]['name'] = $array['name'];
            }
			self::$_currencies = $params['currencies'];
		}
	}

	public static function set($currency)
	{
		self::$_current = $currency;
	}

	public static function get()
	{
		return self::$_currencies[self::$_current];
	}

	public static function currencies()
	{
		return self::$_currencies;
	}

	public static function symbol($code = null)
	{
		$code or $code = self::$_current;
		if (is_array($code) and isset($code['code'])) {$code = $code['code'];}
        return self::$_currencies[$code]['symbol'];
    }
 
	public static function code($currency_id = 0)
	{
	    if ($currency_id != 0) {
		# A currency from the DB.
		$CI = &get_instance();
		$code = $CI->db->get_where('currencies', array('id' => $currency_id))->row_array();
		return!empty($code) ? $code : self::$_current;
	    } else {
		# Default currency.
		return self::$_current;
	    }
	}

	public static function format($amount, $code = null)
	{
		$formatted = $code ? $code.' ' : self::$_currencies[self::$_current]['symbol'];

		if (strncmp($amount, '-', 1) === 0)
		{
			$amount = substr($amount, 1);
			$formatted = '-'.$formatted;
		}
		$formatted .= number_format($amount, 2);

		return $formatted;
	}
}
