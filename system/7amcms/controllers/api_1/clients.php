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
 * The Client API controller
 *
 * @subpackage	Controllers
 * @category	API
 */

class Clients extends REST_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->model('clients/clients_m');
	}

	// /api/v1/clients
	// limit = 5
	// start = 0
	// sort_by = email (default: id)
	// sort_dir = asc (default: asc)
	// client_d = asc (default: asc)
	public function index_get()
	{
		$sort_by = $this->get('sort_by') ? $this->get('sort_by') : 'id';
		$sort_dir = $this->get('sort_dir') ? $this->get('sort_dir') : 'asc';

		$clients = $this->clients_m
			->select('id, first_name, last_name, title, email, 
				IF(company != "", company, NULL) as company,
				IF(address != "", address, NULL) as address,
				IF(phone != "", phone, NULL) as phone,
				IF(fax != "", fax, NULL) as fax,
				IF(mobile != "", mobile, NULL) as mobile,
				IF(website != "", website, NULL) as website,
				IF(profile != "", profile, NULL) as profile,
				created, modified', FALSE)
			->order_by($sort_by, $sort_dir)
			->limit($this->get('limit'), $this->get('start'))
			->get_all();
			
		foreach ($clients as &$client)
		{
			$client->id = (int) $client->id;
		}
			
		$this->response(array(
			'clients' => $clients,
			'count' => count($clients),
		), 200);
	}

	
	public function show_get()
	{
		if ( ! $this->get('id'))
		{
			$this->response(array('status' => false, 'error_message' => 'No id was provided.'), 400);
		}
		
		$client = $this->clients_m
			->select('id, first_name, last_name, title, email, 
				IF(company != "", company, NULL) as company,
				IF(address != "", address, NULL) as address,
				IF(phone != "", phone, NULL) as phone,
				IF(fax != "", fax, NULL) as fax,
				IF(mobile != "", mobile, NULL) as mobile,
				IF(website != "", website, NULL) as website,
				IF(profile != "", profile, NULL) as profile,
				created, modified', FALSE)
			->get($this->get('id'));
			
		$client->id = (int) $client->id;
		
		if (empty($client))	
		{
			$this->response(array('status' => false, 'error_message' => 'This client could not be found.'), 404);
		}
		
		else
		{
			$this->response(array('client' => $client), 200);
		}
	}
	
	
	// /api/v1/clients/new
	public function new_post()
	{
		if (empty($_POST))
		{
			$this->response(array('status' => false, 'error_message' => 'No details were provided.'), 400);
		}
		
		if ($this->clients_m->validate($this->input->post()))
		{
			$this->response(array('status' => false, 'error_message' => current($this->validation_errors())), 400);
		}
		
		$input = array();
		
		$input['first_name'] = $this->post('first_name');
		$input['last_name'] = $this->post('last_name');
		$input['email'] = $this->post('email');
		$this->post('company') and $input['company'] = $this->post('company');
		$this->post('address') and $input['address'] = $this->post('address');
		$this->post('phone') and $input['phone'] = $this->post('phone');
		$this->post('fax') and $input['fax'] = $this->post('fax');
		$this->post('mobile') and $input['mobile'] = $this->post('mobile');
		$this->post('website') and $input['website'] = $this->post('website');
		$this->post('profile') and $input['profile'] = $this->post('profile');

		// Insert an	 skip validation as we've already done it
		$id = $this->clients_m->insert($input, TRUE);
		
		$this->response(array('status' => true, 'id' => $id, 'message' => sprintf('Client #%s has been created.', $id)), 200);
	}

	public function update_post($id = null)
	{
		if ( ! ($id or $id = $this->post('id')))
		{
			$this->response(array('status' => false, 'error_message' => 'No id was provided.'), 400);
		}
		
		if ( ! $client = $this->clients_m->get($id))
		{
			$this->response(array('status' => false, 'error_message' => 'This client does not exist!'), 404);
		}
		
		if ($this->clients_m->update($id, $this->input->post()))
		{
			$this->response(array('status' => true, 'message' => sprintf('Project #%d has been updated.', $id)), 200);
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
		
		if ( ! $client = $this->clients_m->get($id))
		{
			$this->response(array('status' => false, 'error_message' => 'This client does not exist!'), 404);
		}
		
		$this->clients_m->delete($id);
		
		$this->response(array('status' => true, 'message' => sprintf('Client #%d has been deleted.', $id)), 200);
		
	}

}