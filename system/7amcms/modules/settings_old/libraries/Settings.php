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
 * Settings library for easily accessing them
 *
 * @subpackage	Libraries
 * @category	Settings
 */
class Settings {

	/**
	 * @var	object	The CI global object
	 */
	private $_ci;
	/**
	 * @var	array	Holds the settings from the db
	 */
	private static $_settings = array();
	/**
	 * @var	array	Holds the taxes from the db
	 */
	private static $_taxes = array();
	/**
	 * @var	array	Holds the taxes from the db
	 */
	private static $_currencies = array();

	// ------------------------------------------------------------------------

	/**
	 * Loads in the CI global object and loads the settings module
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct()
	{
		$this->_ci = & get_instance();
		$this->_ci->load->model('settings/settings_m');
		$this->_ci->load->model('settings/tax_m');
		$this->_ci->load->model('settings/currency_m');

		$this->reload();
	}

	// ------------------------------------------------------------------------

	/**
	 * This allows you to get the settings like this:
	 *
	 * $this->settings->setting_name
	 *
	 * @access	public
	 * @param	string	The name of the setting
	 * @return	string	The setting value
	 */
	public function __get($name)
	{
		return Settings::get($name);
	}
    
	public static function setVersion($version)
	{
		return self::set('version', $version);
	}

	public static function set($name, $value)
	{
		$CI = &get_instance();
		$CI->db->where('slug', $name)->update('settings', array('value' => $value));
		$CI->settings->reload();
	}

	public function __set($name, $value)
	{
		self::set($name, $value);
	}

	// ------------------------------------------------------------------------

	/**
	 * This allows you to get the settings like this, which is ideal for
	 * use in views:
	 *
	 * Settings::get('setting_name');
	 *
	 * @static
	 * @access	public
	 * @param	string	The name of the setting
	 * @return	string	The setting value
	 */
	public static function get($name)
	{   
		if (!isset(Settings::$_settings[$name]))
		{
			return FALSE;
		}
		return Settings::$_settings[$name];
	}
	
	public static function create($name, $value)
	{
	    $CI = &get_instance();
	    if ($CI->db->where('slug', $name)->count_all_results('settings') == 0) {
		$result = $CI->db->insert('settings', array('slug' => $name, 'value' => $value));
		$CI->settings->reload();
		return $result;
	    } else {
		return true;
	    }
	}
	
	public static function delete($name)
	{
	    $CI = &get_instance();
	    $result = $CI->db->where('slug', $name)->delete('settings');
	    $CI->settings->reload();
	    return $result;
	}

	// ------------------------------------------------------------------------

	/**
	 * Returns all of the settings
	 *
	 * @access	public
	 * @return	array	An array containing all the settings
	 */
	public function get_all()
	{
		return Settings::$_settings;
	}

	// ------------------------------------------------------------------------

	/**
	 * Returns all of the taxes
	 *
	 * @access	public
	 * @return	array	An array containing all the settings
	 */
	public function all_taxes()
	{
		return Settings::$_taxes;
	}

	// ------------------------------------------------------------------------

	/**
	 * Returns all of the taxes
	 *
	 * @access	public
	 * @return	array	An array containing all the currencies
	 */
	public function all_currencies()
	{
		return Settings::$_currencies;
	}

	// ------------------------------------------------------------------------

	/**
	 * This allows you to get the currency like this, which is ideal for
	 * use in views:
	 *
	 * Settings::currency(1);
	 *
	 * @static
	 * @access	public
	 * @param	string	The id of the tax
	 * @return	string	The tax
	 */
	public static function currency($id)
	{
		if ( ! isset(Settings::$_currencies[$id]))
		{
			return FALSE;
		}
		return Settings::$_currencies[$id];
	}

	// ------------------------------------------------------------------------

	/**
	 * This allows you to get the tax like this, which is ideal for
	 * use in views:
	 *
	 * Settings::tax(1);
	 *
	 * @static
	 * @access	public
	 * @param	string	The id of the tax
	 * @return	string	The tax
	 */
	public static function tax($id)
	{
		if ( ! isset(Settings::$_taxes[$id]))
		{
			return FALSE;
		}
		return Settings::$_taxes[$id];
	}

	// ------------------------------------------------------------------------

	/**
	 * Gets the dropdown for all the taxes
	 *
	 * @static
	 * @access	public
	 * @return	array	The tax dropdown array
	 */
	public static function tax_dropdown()
	{
		$return = array(0 => 'No Tax');

		foreach (Settings::$_taxes as $id => $tax)
		{
			$return[$id] = $tax['name'] . ' (' . $tax['value'] . '%)';
		}
		return $return;
	}

	// ------------------------------------------------------------------------

	/**
	 * This reloads the settings in from the database.
	 *
	 * @access	public
	 * @return	void
	 */
	public function reload()
	{
		Settings::$_taxes = array();
		Settings::$_currencies = array();
		Settings::$_settings = array();
		
		foreach ($this->_ci->settings_m->get_all() as $setting)
		{
			Settings::$_settings[$setting->slug] = $setting->value;
		}

		foreach ($this->_ci->tax_m->get_all() as $tax)
		{
			Settings::$_taxes[$tax->id] = array(
				'name' => $tax->name,
				'value' => $tax->value
			);
			
			if (isset($tax->reg)) {
			    Settings::$_taxes[$tax->id]['reg'] = $tax->reg;
			}
		}

		$versions_without_currencies = array('1.0', '1.1', '1.1.1', '1.1.2', '1.1.3', '1.1.4', '2.0', '2.0.1', '2.0.2');
		
		if (in_array(Settings::$_settings['version'], $versions_without_currencies)) {
			# This version does not have currencies, but needs to work till the upgrade is over.
			$currencies = array();
		} else {
			$currencies = @$this->_ci->currency_m->get_all();
		}
		
		foreach ($currencies as $currency)
		{
			Settings::$_currencies[$currency->id] = array(
				'name' => $currency->name,
				'code' => $currency->code,
				'rate' => $currency->rate,
			);
		}

		$tz = Settings::get('timezone');
		if (empty($tz))
		{
		    $tz = @date_default_timezone_get();
		}
		date_default_timezone_set($tz);
	}

}

/* End of file: Settings.php */