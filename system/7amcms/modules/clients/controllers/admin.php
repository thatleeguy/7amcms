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
 * The admin controller for Clients
 *
 * @subpackage	Controllers
 * @category	Clients
 */
class Admin extends Admin_Controller
{
	/**
	 * The construct doesn't do anything useful right now.
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->load->model('clients_m');
	}

	// ------------------------------------------------------------------------

	/**
	 * Loads all the clients in and sends then to be outputted
	 *
	 * @access	public
	 * @return	void
	 */
	public function index($offset = 0)
	{
		$this->load->model('invoices/invoice_m');
        $count = $this->clients_m->count_all();
		$clients = $this->clients_m->order_by('first_name')->limit($this->pagination_config['per_page'], $offset)->get_all();
                
        // Start up the pagination 
		$this->load->library('pagination');
        $this->pagination_config['base_url'] = site_url('admin/clients/index/');
		$this->pagination_config['uri_segment'] = 4;
		$this->pagination_config['total_rows'] = $count;
		$this->pagination->initialize($this->pagination_config);

		foreach ($clients as & $client)
		{
            $client->health = $this->clients_m->health($client->id);
			$paid_total = $this->invoice_m->paid_totals($client->id);
			$unpaid_total = $this->invoice_m->unpaid_totals($client->id);
			$client->paid_total = $paid_total['total'];
			$client->unpaid_total = $unpaid_total['total'];
		}
		$this->template->clients = $clients;

		$this->template->build('list');
	}

	// ------------------------------------------------------------------------

	/**
	 * Creates a client
	 *
	 * @access	public
	 * @return	void
	 */
	public function create()
	{
		if ($_POST)
		{
			$_POST['created'] = time();
			
            $postBuffer = $_POST;
            $buffer = isset($_POST['gateways']) ? $_POST['gateways'] : array();
            unset($postBuffer['gateways']);
			
			if ($result = $this->clients_m->insert($postBuffer))
			{
            	require_once APPPATH.'modules/gateways/gateway.php';
				Gateway::processItemInput('CLIENT', $result, $buffer);
				$this->session->set_flashdata('success', lang('clients:added'));
				redirect('admin/clients');
			}
			else
			{
				$this->template->error = validation_errors();
			}
		}
		$this->template->action_type = 'add';
		$this->template->action = 'create';
		$this->template->build('form');
	}

	// ------------------------------------------------------------------------

	/**
	 * Edits a client
	 *
	 * @access	public
	 * @return	void
	 */
	public function edit($client_id)
	{
		$this->load->model('clients_m');

		$client = $this->clients_m->get($client_id);
		if (empty($client))
		{
			$this->session->set_flashdata('error', lang('clients:does_not_exist'));
			redirect('admin/clients');
		}

		if ($_POST)
		{
			$postBuffer = $_POST;
			$buffer = isset($_POST['gateways']) ? $_POST['gateways'] : array();
            unset($postBuffer['gateways']);
			
			if ($result = $this->clients_m->update($client_id, $postBuffer))
			{
                require_once APPPATH.'modules/gateways/gateway.php';
                Gateway::processItemInput('CLIENT', $client_id, $buffer);
				$this->session->set_flashdata('success', lang('clients:edited'));
				redirect('admin/clients');
			}
			else
			{
				$this->template->error = validation_errors();
			}
		}
		else
		{
			foreach ((array) $client as $key => $val)
			{
				$_POST[$key] = $val;
			}
		}
		$this->template->action_type = 'edit';
        $this->template->client_id = $client_id;
		$this->template->action = 'edit/'.$client_id;
		$this->template->build('form');
	}

	// ------------------------------------------------------------------------

	/**
	 * Edits a client
	 *
	 * @access	public
	 * @return	void
	 */
	public function delete($client_id)
	{
		if ($_POST)
		{
			// Check to make sure the action hash matches, if not kick em' to the curb
			if ($this->input->post('action_hash') !== $this->session->userdata('action_hash'))
			{
				$this->session->set_flashdata('error', lang('global:insecure_action'));
				redirect('admin/dashboard');
			}
			
            # This deletes all invoices, projects and proposals related to the client.
			$this->clients_m->delete($client_id);
			$this->session->set_flashdata('success', lang('clients:deleted'));
			redirect('admin/clients');
		}

		// We set a unique action hash here to stop CSRF attacks (hacker's beware)
		$action_hash = md5(time().$client_id);
		$this->session->set_userdata('action_hash', $action_hash);
		$this->template->action_hash = $action_hash;

		// Lets make sure before we just go killing stuff like Rambo
		$this->template->client_id = $client_id;
		$this->template->build('are_you_sure');
	}


	// ------------------------------------------------------------------------

	/**
	 * SHows the clients info
	 *
	 * @access	public
	 * @param	string	The client id
	 * @return	void
	 */
	public function view($client_id)
	{
		if ( ! ($client = $this->clients_m->get($client_id)))
		{
			$this->session->set_flashdata('error', lang('clients:does_not_exist'));
			redirect('admin/clients');
		}
		$this->load->model('invoices/invoice_m');
		
		$totals['paid'] = $this->invoice_m->paid_totals($client_id);
		$totals['unpaid'] = $this->invoice_m->unpaid_totals($client_id);
		$totals['overdue'] = $this->invoice_m->overdue_totals($client_id);
        $totals['count'] = $totals['paid']['count'] + $totals['unpaid']['count'];

		$invoices['paid'] = $this->invoice_m->get_all_paid($client_id);
		$invoices['unpaid'] = $this->invoice_m->get_all_unpaid($client_id);
		$invoices['overdue'] = $this->invoice_m->get_all_overdue($client_id);

        $client->health = $this->clients_m->health($client_id);

		$this->template->build('view', array(
			'totals' => $totals,
			'invoices' => $invoices,
			'client' => (array) $client,
		));
	}
}

/* End of file: admin.php */