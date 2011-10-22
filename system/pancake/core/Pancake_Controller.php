<?php

defined('BASEPATH') OR exit('No direct script access allowed');
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
 * The admin and public base controllers extend this library
 *
 * @subpackage	Controllers
 */
class Pancake_Controller extends CI_Controller {

    /**
     * @var array	An array of methods to be secured by login
     */
    protected $secured_methods = array();

    // ------------------------------------------------------------------------

    /**
     * The construct loads sets up items needed application wide.
     *
     * @access	public
     * @return	void
     */
    public function __construct() {
	parent::__construct();
	
	define('PANCAKE_DEMO', (file_exists(FCPATH.'DEMO')));

	$this->method = $this->router->fetch_method();

	// Migrate DB to the latest version
	$this->load->library('migration');
	$this->load->model('upgrade/upgrade_m');
	
	# Get the latest version if it's been 12 hours since the last time.
	# Automatically update Pancake, if the settings are set to that.
	$this->load->model('upgrade/update_system_m', 'update');
	if ($this->method != 'no_internet_access') {
	    $this->update->get_latest_version();
	}
	# If Pancake was just automatically updated, the update system will force a refresh.
	# So by the time it gets here, the NEW Pancake will be running, and the migrations will run as expected.

	$versions_without_migrations = array('1.0', '1.1', '1.1.1', '1.1.2', '1.1.3', '1.1.4', '2.0', '2.0.1', '2.0.2', '2.0.3');
	# 2.1.0 does not have migrations but can be migrated.

	if (!in_array(PAN::setting('version'), $versions_without_migrations)) {
	    $this->migration->latest() or show_error($this->migration->error_string());
	} else {
	    $this->upgrade_m->start();
	}

	$this->output->enable_profiler(FALSE);
	Currency::set(PAN::setting('currency'));

	define('IS_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
	define('IS_CLI', defined('STDIN'));

	$this->lang->load('pancake', Settings::get('language'));

	$this->current_user = $this->template->current_user = $this->ion_auth->get_user();

	log_message('debug', "Pancake_Controller Class Initialized");
    }

}

/* End of file: Pancake_Controller.php */