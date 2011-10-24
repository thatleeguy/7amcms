<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * PROJECT
 *
 * DESCRIPTION
 *
 * @package		PACKAGE
 * @author		Dan Horrigan <http://dhorrigan.com>
 * @license		Apache License v2.0
 */

/**
 * Driver Library
 *
 * @subpackage	Driver_library
 */
class Driver_Library
{
	protected $ci;

	protected $valid_drivers = array();

	protected $drivers_path;

	protected $driver;

	public function Driver_Library()
	{
		self::__construct();
	}


	public function __construct()
	{
		$this->ci =& get_instance();
	}

	public function load_driver($name, $class_name = NULL)
	{
		if ( ! is_file($this->drivers_path.$name.EXT))
		{
			show_error('Could not load driver "'.$name.'"');
		}

		include_once $this->drivers_path.$name.EXT;

		if ($class_name === NULL)
		{
			$class_name = $name;
		}

		$this->driver = new $class_name($this->config);
	}

	public function __call($name, $params)
	{
		if (is_callable(array($this->driver, $name)))
		{
			return call_user_func_array(array($this->driver, $name), $params);
		}
		else
		{
			show_error('Invalid driver function ".$name."');
		}
	}

}

/* End of file: Driver.php */