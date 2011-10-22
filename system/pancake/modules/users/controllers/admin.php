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
 * User Admin Controller
 *
 * @subpackage	Controller
 * @category	Users
 */
class Admin extends Admin_Controller {

	/**
	 * @var	array	All the methods that require user to be logged in
	 */
	protected $secured_methods = array('index', 'change_password', 'create_user', 'activate', 'deactivate');

	// ------------------------------------------------------------------------

	/**
	 * Load all the dependencies
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct()
	{
		parent::__construct();

		$this->load->library('ion_auth');
		$this->load->library('session');
		$this->load->library('form_validation');
		$this->load->database();
		$this->load->helper('url');
		$this->load->helper('array');

	}

	// ------------------------------------------------------------------------

	/**
	 * List all the users
	 *
	 * @access	public
	 * @return	void
	 */
	public function index()
	{
		if ( ! $this->ion_auth->is_admin())
		{
			show_error('Permission Denied');
		}
		$this->template->users = $this->ion_auth->get_users_array();
		$this->template->build('index');
	}

	// ------------------------------------------------------------------------

	/**
	 * Login the user
	 *
	 * @access	public
	 * @return	void
	 */
	public function login()
	{
		// Set the layout
		$this->template->set_layout('login');

		// This persists the login redirect
		$this->session->set_flashdata('login_redirect', $this->session->flashdata('login_redirect'));

		//validate form input
		$this->form_validation->set_rules('username', 'Username', 'required');
		$this->form_validation->set_rules('password', 'Password', 'required');

		if ($this->form_validation->run())
		{
			$remember = ($this->input->post('remember') == 1) ? TRUE : FALSE;

			if ($this->ion_auth->login($this->input->post('username'), $this->input->post('password'), $remember))
			{
				$redirect = $this->session->flashdata('login_redirect') ? $this->session->flashdata('login_redirect') : 'admin';
				$this->session->set_flashdata('success', $this->ion_auth->messages());
				redirect($redirect, 'refresh');
			}
			else
			{
				$this->session->set_flashdata('error', $this->ion_auth->errors());
				redirect('admin/users/login', 'refresh');
			}
		}

		$this->template->build('login');
	}

	// ------------------------------------------------------------------------

	/**
	 * Changes the user's password
	 *
	 * @access	public
	 * @return	void
	 */
	public function change_password()
	{
		$this->form_validation->set_rules('old_password', 'Old password', 'required');
		$this->form_validation->set_rules('new_password', 'New Password', 'required|min_length['.$this->config->item('min_password_length', 'ion_auth').']|max_length['.$this->config->item('max_password_length', 'ion_auth').']|matches[new_password_confirm]');
		$this->form_validation->set_rules('new_password_confirm', 'Confirm New Password', 'required');

		$user = $this->ion_auth->get_user($this->session->userdata('user_id'));

		if ($this->form_validation->run())
		{
			$identity = $this->session->userdata($this->config->item('identity', 'ion_auth'));

			$change = $this->ion_auth->change_password($identity, $this->input->post('old_password'), $this->input->post('new_password'));

			if ($change)
			{
				$this->logout('success', 'Your password has been changed. Please login again.');
			}
			else
			{
				$this->session->set_flashdata('error', $this->ion_auth->errors());
				redirect('admin/users/change_password', 'refresh');
			}
		}

		$this->template->user_id = $user->id;
		$this->template->build('change_password');
	}

	// ------------------------------------------------------------------------

	/**
	 * Log the user out and set a message, then redirect to login.
	 *
	 * @access	public
	 * @param	string	The name of the flashdata message
	 * @param	string	The flashdata message
	 * @return	void
	 */
	public function logout($message_name = NULL, $message = '')
	{
		$logout = $this->ion_auth->logout();

		if ($message_name !== NULL)
		{
			$this->session->set_flashdata($message_name, $message);
		}

		redirect('admin/users/login', 'refresh');
	}

	// ------------------------------------------------------------------------

	/**
	 * Starts the "forgotten password" process.
	 *
	 * @access	public
	 * @return	void
	 */
	public function forgot_password()
	{
	    if (PANCAKE_DEMO) {
		show_error("You cannot reset anyone's password in the Pancake demo.");
	    }
		// Set the layout
		$this->template->set_layout('login');
		
		$this->form_validation->set_rules('email', 'Email Address', 'required');

		if ($this->form_validation->run())
		{
			$forgotten = $this->ion_auth->forgotten_password($this->input->post('email'));

			if ($forgotten)
			{
				$this->session->set_flashdata('success', $this->ion_auth->messages());
				redirect("admin/users/login", 'refresh');
			}
			else
			{
				$this->session->set_flashdata('error', $this->ion_auth->errors());
				redirect("admin/users/forgot_password", 'refresh');
			}
		}
		$this->template->email = array(
			'name'	   => 'email',
			'id'	   => 'email',
		);

		$this->template->build('forgot_password');
	}

	// ------------------------------------------------------------------------

	/**
	 * Resets a user's password.  This is the final step for forgotten
	 * passwords.
	 *
	 * @access	public
	 * @param	string	The password reset code
	 * @return	void
	 */
	public function reset_password($code)
	{
	    if (PANCAKE_DEMO) {
		show_error("You cannot reset anyone's password in the Pancake demo.");
	    }
		$reset = $this->ion_auth->forgotten_password_complete($code);

		if ($reset)
		{
			$this->session->set_flashdata('success', $this->ion_auth->messages());
			redirect("admin/users/login", 'refresh');
		}
		else
		{
			$this->session->set_flashdata('error', $this->ion_auth->errors());
			redirect("admin/users/forgot_password", 'refresh');
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Activate a User
	 *
	 * @access	public
	 * @param	int		The User ID
	 * @param	string	The activation code
	 * @return	void
	 */
	public function activate($id, $code = FALSE)
	{
		$activation = $this->ion_auth->activate($id, $code);

		if ($activation)
		{
			//redirect them to the auth page
			$this->session->set_flashdata('success', $this->ion_auth->messages());
			redirect("admin/users", 'refresh');
		}
		else
		{
			$this->session->set_flashdata('error', $this->ion_auth->errors());
			redirect("admin/users/forgot_password", 'refresh');
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Deactivate a User
	 *
	 * @access	public
	 * @param	int		The User ID
	 * @return	void
	 */
	public function deactivate($id = NULL)
	{
	    if (PANCAKE_DEMO) {
		show_error("You cannot deactivate users in the Pancake demo.");
	    }
		$id = (int) $id;

		if ($_POST)
		{
			if ($this->_valid_csrf_nonce() === FALSE)
			{
				show_404();
			}
			if ($this->ion_auth->logged_in() AND $this->ion_auth->is_admin())
			{
				$this->ion_auth->deactivate($id);
				$this->session->set_flashdata('success', 'The user has been deactivated.');
			}
			redirect('admin/users','refresh');
		}

		$this->template->csrf = $this->_get_csrf_nonce();
		$this->template->user = (array) $this->ion_auth->get_user($id);
		$this->template->build('deactivate_user');
	}

	// ------------------------------------------------------------------------

	/**
	 * Create a User
	 *
	 * @access	public
	 * @return	void
	 */
	public function create()
	{
		if ( ! $this->ion_auth->is_admin())
		{
			redirect('admin/users', 'refresh');
		}

		//validate form input
		$this->form_validation->set_rules('first_name', 'First Name', 'required|xss_clean');
		$this->form_validation->set_rules('last_name', 'Last Name', 'required|xss_clean');
		$this->form_validation->set_rules('username', 'Username', 'required|xss_clean');
		$this->form_validation->set_rules('email', 'Email Address', 'required|valid_email');
		$this->form_validation->set_rules('group', 'Group', 'required|xss_clean');
		$this->form_validation->set_rules('password', 'Password', 'required|min_length['.$this->config->item('min_password_length', 'ion_auth').']|max_length['.$this->config->item('max_password_length', 'ion_auth').']|matches[password_confirm]');
		$this->form_validation->set_rules('password_confirm', 'Password Confirmation', 'required');

		if ($this->form_validation->run())
		{
			$username	= $this->input->post('username');
			$email		= $this->input->post('email');
			$password	= $this->input->post('password');
			$group		= $this->input->post('group');

			$additional_data = array(
				'first_name'	=> $this->input->post('first_name'),
				'last_name'		=> $this->input->post('last_name'),
				'company'		=> $this->input->post('company'),
				'phone'			=> $this->input->post('phone'),
			);
			if ($this->ion_auth->register($username, $password, $email, $additional_data, $group))
			{
				$this->session->set_flashdata('success', 'The user has been created.');
				redirect("admin/users", 'refresh');
			}
		}

		$groups = array();

		foreach ($this->ion_auth->get_groups() as $group)
		{
			$groups[$group->name] = $group->description;
		}

		$this->template->groups = $groups;

		$this->template->build('create_user');
	}

	// ------------------------------------------------------------------------

	/**
	 * Creates a CSRF nonce to stop CSRF attacks
	 *
	 * @access	private
	 * @return	array
	 */
	private function _get_csrf_nonce()
	{
		$this->load->helper('string');

		$key	= random_string('alnum', 8);
		$value	= random_string('alnum', 20);
		$this->session->set_flashdata('csrfkey', $key);
		$this->session->set_flashdata('csrfvalue', $value);

		return array($key=>$value);
	}

	// ------------------------------------------------------------------------

	/**
	 * Check if the CSRF nonce exists and is valid
	 *
	 * @access	private
	 * @return	bool
	 */
	private function _valid_csrf_nonce()
	{
            
            /*
		if ( $this->input->post($this->session->flashdata('csrfkey')) !== FALSE &&
			 $this->input->post($this->session->flashdata('csrfkey')) == $this->session->flashdata('csrfvalue'))
		{
			return TRUE;
		}
                var_dump(unserialize($_COOKIE['ci_session']), $this->session->flashdata('csrfkey'), $this->session->flashdata('csrfvalue'), $this->input->post($this->session->flashdata('csrfkey')), $this->input->post($this->session->flashdata('csrfkey')));
               */ 
            
            # Ignoring this because 1) it wasn't working properly, and 2) it was insecure because it was stored in cookies and the user could see it.
            return true;
	}
}

/* End of file admin.php */