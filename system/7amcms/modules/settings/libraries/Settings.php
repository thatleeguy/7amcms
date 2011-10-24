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
		if (!isset(Settings::$_currencies[$id]))
		{
			return FALSE;
		}
		return Settings::$_currencies[$id];
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
		Settings::$_settings = array();
		foreach ($this->_ci->settings_m->get_all() as $setting)
		{
			Settings::$_settings[$setting->slug] = $setting->value;
		}
	}

}

/* End of file: Settings.php */
