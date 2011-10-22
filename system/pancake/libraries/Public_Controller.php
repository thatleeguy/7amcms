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
 * All public controllers should extend this library
 *
 * @subpackage	Controllers
 */
class Public_Controller extends Pancake_Controller
{
	/**
	 * @var	array 	An array of methods to be secured by login
	 */
	protected $secured_methods = array();

	// ------------------------------------------------------------------------

	/**
	 * The named constructor
	 *
	 * @access	public
	 * @return	void
	 */
	public function Public_Controller()
	{
		self::__construct();
	}

	// ------------------------------------------------------------------------

	/**
	 * The construct checks for authorization then loads in settings for
	 * all of the admin controllers.
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct()
	{
		parent::__construct();

		$this->method = $this->router->fetch_method();
		if ( ! $this->ion_auth->logged_in()
		    && (in_array($this->method, $this->secured_methods)
		    || in_array('_all_', $this->secured_methods)))
		{
			$this->session->set_flashdata('login_redirect', $this->uri->uri_string());
			redirect('admin/users/login');
		}

		$this->template->set_theme(PAN::setting('theme'));
		$this->template->set_layout('index');

		// Add the theme path to the asset paths
		asset::add_path($this->template->get_theme_path());

		log_message('debug', "Public_Controller Class Initialized");
	}
}

/* End of file: Admin_Controller.php */