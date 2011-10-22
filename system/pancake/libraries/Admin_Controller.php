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
 * All admin controllers should extend this library
 *
 * @subpackage	Controllers
 */
class Admin_Controller extends Pancake_Controller
{
	/**
	 * @var	array 	An array of methods to be secured by login
	 */
	protected $secured_methods = array('_all_');

	/**
	 * @var	array	The pagination class config array
	 */
	protected $pagination_config = array();

	// ------------------------------------------------------------------------

	/**
	 * The named constructor
	 *
	 * @access	public
	 * @return	void
	 */
	public function Admin_Controller()
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

		if (! $this->ion_auth->logged_in() and $this->method != 'no_internet_access') {
		    if ((in_array($this->method, $this->secured_methods) || in_array('_all_', $this->secured_methods))) {
			$this->session->set_flashdata('login_redirect', $this->uri->uri_string());
			redirect('admin/users/login');
		    }
		}

		$this->load->library('form_validation');
		$this->template->set_theme('admin/'.PAN::setting('admin_theme'));
		$this->template->set_layout('index');
		$this->template->set_partial('notifications', 'partials/notifications');

		$this->template->module = $this->router->fetch_method() == 'estimates' ? 'estimates' : $this->router->fetch_module();

		// Setting up the base pagination config
		$this->pagination_config['per_page'] = 10;
		$this->pagination_config['num_links'] = 5;
		$this->pagination_config['full_tag_open'] = '<ul>';
		$this->pagination_config['full_tag_close'] = '</ul>';
		$this->pagination_config['first_tag_open'] = '<li class="first">';
		$this->pagination_config['first_tag_close'] = '</li>';
		$this->pagination_config['last_tag_open'] = '<li class="last">';
		$this->pagination_config['last_tag_close'] = '</li>';
		$this->pagination_config['prev_tag_open'] = '<li class="prev">';
		$this->pagination_config['prev_tag_close'] = '</li>';
		$this->pagination_config['next_tag_open'] = '<li class="next">';
		$this->pagination_config['next_tag_close'] = '</li>';
		$this->pagination_config['cur_tag_open'] = '<li class="num"><strong>';
		$this->pagination_config['cur_tag_close'] = '</strong></li>';
		$this->pagination_config['num_tag_open'] = '<li class="num">';
		$this->pagination_config['num_tag_close'] = '</li>';

		// Try to determine the pagination base_url
		$segments = $this->uri->segment_array();

		if ($this->uri->total_segments() >= 4)
		{
			array_pop($segments);
		}

		$this->pagination_config['base_url'] = site_url(implode('/', $segments));
		$this->pagination_config['uri_segment'] = 4;

		// Add the theme path to the asset paths
		asset::add_path($this->template->get_theme_path());
		
		if ($this->current_user) {
		    # Update the last visited version for this user.
		    $last_visited_version = $this->db->where('user_id', $this->current_user->id)->get('meta')->row_array();
		    define('LAST_VISITED_VERSION', $last_visited_version['last_visited_version']);
		    $this->db->where('user_id', $this->current_user->id)->update('meta', array('last_visited_version' => Settings::get('version')));
		}

		log_message('debug', "Admin_Controller Class Initialized");
	}
}

/* End of file: Admin_Controller.php */
