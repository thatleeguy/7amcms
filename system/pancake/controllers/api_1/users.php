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
 * The User API controller
 *
 * @subpackage	Controllers
 * @category	API
 */

class Users extends REST_Controller {

	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('users/user_m');
		$this->load->model('ion_auth_model');
	}

	// /api/v1/users
	// limit = 5
	// start = 0
	// sort_by = email (default: id)
	// sort_dir = asc (default: asc)
	// user_d = asc (default: asc)
	public function index_get()
	{
		$sort_by = $this->get('sort_by') ? $this->get('sort_by') : 'id';
		$sort_dir = $this->get('sort_dir') ? $this->get('sort_dir') : 'asc';

		$users = $this->user_m
			 ->select('*, null as password, FROM_UNIXTIME(created_on) as created_on, FROM_UNIXTIME(last_login) as last_login', FALSE)
			->order_by($sort_by, $sort_dir)
			->limit($this->get('limit'), $this->get('start'))
			->get_all();
			
		foreach ($users as &$user)
		{
			$user->id = (int) $user->id;
			$user->group_id = (int) $user->group_id;
			$user->active = (bool) $user->active;
		}
			
		$this->response(array(
			'users' => $users,
			'count' => count($users),
		), 200);
	}

	
	public function show_get()
	{
		if ( ! $this->get('id'))
		{
			$this->response(array('status' => false, 'error_message' => 'No id was provided.'), 400);
		}
		
		$user = $this->ion_auth_model
			->sekect('*')
			// ->select('id, first_name, last_name, title, email, 
			// 	IF(company != "", company, NULL) as company,
			// 	IF(address != "", address, NULL) as address,
			// 	IF(phone != "", phone, NULL) as phone,
			// 	IF(fax != "", fax, NULL) as fax,
			// 	IF(mobile != "", mobile, NULL) as mobile,
			// 	IF(website != "", website, NULL) as website,
			// 	IF(profile != "", profile, NULL) as profile,
			// 	created, modified', FALSE)
			->get($this->get('id'));
			
		$user->id = (int) $user->id;
		
		if (empty($user))	
		{
			$this->response(array('status' => false, 'error_message' => 'This user could not be found.'), 404);
		}
		
		else
		{
			$this->response(array('user' => $user), 200);
		}
	}
	
	
	// /api/v1/users/new
	public function new_post()
	{
		if (empty($_POST))
		{
			$this->response(array('status' => false, 'error_message' => 'No details were provided.'), 400);
		}
		
		if ( ! $this->user_m->validate($this->input->post()))
		{
			$this->response(array('status' => false, 'error_message' => current($this->validation_errors())), 400);
		}
		
		$input = $this->input->post();
		
		if ($username = $this->post('username')) unset($input['username']);
		if ($email = $this->post('email')) unset($input['email']);
		if ($password = $this->post('password')) unset($input['password']);
		// if ($this->post('group_name')) unset($input['group_name']);
		$group_name = 1; // 1 = Admin
		
		// Register this user!
		$id = $this->ion_auth_model->register($username, $password, $email, $this->input->post(), $group_name);
		
		$this->response(array('status' => true, 'id' => $id, 'message' => sprintf('User #%s has been created.', $id)), 200);
	}

	public function update_post($id = null)
	{
		if ( ! ($id or $id = $this->post('id')))
		{
			$this->response(array('status' => false, 'error_message' => 'No id was provided.'), 400);
		}
		
		if ( ! $user = $this->user_m->get($id))
		{
			$this->response(array('status' => false, 'error_message' => 'This user does not exist!'), 404);
		}
		
		$input = $this->input->post();
		
		if ($this->ion_auth_model->update_user($id, $input))
		{
			$this->response(array('status' => true, 'message' => sprintf('User #%d has been updated.', $id)), 200);
		}
		else
		{
			$this->response(array('status' => false, 'error_message' => current($this->validation_errors())), 400);
		}
	}
	
	public function delete_post($id = null)
	{
		if ( ! ($id or $id = $this->post('id')))
		{
			$this->response(array('status' => false, 'error_message' => 'No id was provided.'), 400);
		}
		
		if ( ! $user = $this->user_m->get($id))
		{
			$this->response(array('status' => false, 'error_message' => 'This user does not exist!'), 404);
		}
		
		$this->user_m->delete($id);
		
		$this->response(array('status' => true, 'message' => sprintf('User #%d has been deleted.', $id)), 200);
	}

}