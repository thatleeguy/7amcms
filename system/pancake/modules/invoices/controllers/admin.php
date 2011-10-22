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
 * The admin controller for invoices
 *
 * @subpackage	Controllers
 * @category	Dashboard
 */
class Admin extends Admin_Controller
{
	/**
	 * Load in the payments model
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->load->model('invoice_m');
		$this->load->model('partial_payments_m', 'ppm');
		require_once APPPATH.'modules/gateways/gateway.php';
	}

	// ------------------------------------------------------------------------

	/**
	 * Creates a new invoice
	 *
	 * @access	public
	 * @return	void
	 */
	public function create($project_id = NULL, $client_id = 0)
	{            
        if ($project_id == 'iframe')
		{
        	$iframe = true;
			$project_id = null;
		}
		elseif ($project_id == 'client')
		{
			$iframe = false;
			$project_id = null;
		} 
		else 
		{
			$iframe = false;
		}
        
		// Passed in the URL
        if ($this->input->get('client'))
		{
            $client_id = $this->input->get('client');
        }
        else if (isset($_POST['client_id']))
		{
            $client_id = $_POST['client_id'];
        }
	
		$this->template->client_id = (int) $client_id;
        
		$items = array();

		// Prepopulate items based on Project ID
		if ($project_id !== NULL)
		{
			$this->load->model('projects/project_m');
			$this->load->model('projects/project_task_m');
			$this->load->model('projects/project_time_m');

			$project = $this->project_m->get_project_by_id($project_id)->row();

			$this->template->project = $project;

			if ($tasks = $this->project_task_m->get_tasks_by_project($project_id))
			{
				foreach ($tasks as $task)
				{
					if (($task['hours'] + $task['tracked_hours']) == 0) {continue;}
					$items[] = array(
						'name' => $task['name'],
						'description' => $task['notes'],
						'qty' => ($task['hours'] + $task['tracked_hours']),
						'rate' => $task['rate'],
						'tax_id' => 1,
						'total' => ($task['hours'] + $task['tracked_hours']) * $task['rate'],
					);
				}
			}

			// If its not attached as a task, add a new entry anyway
			if ($extra_times = $this->project_time_m->get_extras_by_project($project_id))
			{
				foreach ($extra_times as $time)
				{
					$hours = round($time->minutes / 60, 2);
					if ($hours == 0) {continue;}
					$items[] = array(
						'name' => $time->note ? $time->note : 'Unknown time',
						'description' => '',
						'qty' => $hours,
						'rate' => $project->rate,
						'tax_id' => 1,
						'total' => $hours * $project->rate,
					);
				}
			}
		}

		else if ($post_items = $this->input->post('invoice_item'))
		{
			for ($i = 0; $i < count($post_items['description']); $i++)
			{
				$items[] = array(
					'name' => $post_items['name'][$i],
					'description' => $post_items['description'][$i],
					'qty' => isset($post_items['qty'][$i]) ? $post_items['qty'][$i] : 0,
					'rate' => $post_items['rate'][$i],
					'tax_id' => isset($post_items['tax_id'][$i]) ? $post_items['tax_id'][$i] : 0,
					'total' => isset($post_items['cost'][$i]) ? $post_items['cost'][$i] : 0,
				);
			}
		}
		
		// No post items? Let's make a default one!
		else
		{
			$items[] = array(
				'name' => '',
				'description' => '',
				'qty' => 0,
				'rate' => '0.00',
				'tax_id' => 0,
				'total' => 0,
			);
		}

		$this->template->items = $items;

		$base_currency = Currency::get();
		$currencies = array(__('currencies:default', array(__($base_currency['name']))));
		foreach (Settings::all_currencies() as $currency)
		{
			$currencies[$currency['code']] = $currency['name'];
		}
		$this->template->currencies = $currencies;

		$this->load->model('clients/clients_m');
                
		$this->template->iframe = $iframe;

		// Build the client dropdown array for the form
		$this->template->clients_dropdown = $this->clients_m->build_client_dropdown();
		$this->template->gateways = Gateway::get_enabled_gateways();

		if ($_POST)
		{
			$postBuffer = $_POST;
			$buffer = isset($_POST['gateways']) ? $_POST['gateways'] : array();
			unset($postBuffer['gateways']);
			if ($result = $this->invoice_m->insert($postBuffer))
			{
               	$id = $this->invoice_m->getIdByUniqueId($result);
	            require_once APPPATH.'modules/gateways/gateway.php';
            	Gateway::processItemInput('INVOICE', $id, $buffer);
				
				$this->load->model('files/files_m');
				$this->files_m->upload($_FILES['invoice_files'], $result);

				$this->session->set_flashdata('success', 'The invoice has been added!');
              
  				if (!$iframe)
				{
                    redirect('admin/invoices/created/'.$result);
                }
				else
				{
                    $this->template->id = $id;
                    return $this->template->build('close_facebox');
                }
			}
		}

		$this->template->invoice_number = $this->invoice_m->_generate_invoice_number();
		$this->template->unique_id = $this->invoice_m->_generate_unique_id();
		$this->template->build('create');
	}

	// ------------------------------------------------------------------------

	/**
	 * Creates a new estimate
	 *
	 * @access	public
	 * @return	void
	 */
	public function create_estimate($iframe = '', $client_id = '')
	{
        $iframe = !empty($iframe);
	$items = array();
	if ($post_items = $this->input->post('invoice_item')) {
	    for ($i = 0; $i < count($post_items['description']); $i++) {
		$items[] = array(
		    'name' => $post_items['name'][$i],
		    'description' => $post_items['description'][$i],
		    'qty' => isset($post_items['qty'][$i]) ? $post_items['qty'][$i] : 0,
		    'rate' => $post_items['rate'][$i],
		    'tax_id' => isset($post_items['tax_id'][$i]) ? $post_items['tax_id'][$i] : 0,
		    'total' => isset($post_items['cost'][$i]) ? $post_items['cost'][$i] : 0,
		);
	    }
	}

	// No post items? Let's make a default one!
	else {
	    $items[] = array(
		'name' => '',
		'description' => '',
		'qty' => 0,
		'rate' => '0.00',
		'tax_id' => 0,
		'total' => 0,
	    );
	}

        if (isset($_POST['client_id'])) {$client_id = $_POST['client_id'];}
        
        $this->template->client_id = (int) $client_id;
		$this->template->estimate = true;
		$this->template->items = $items;
        $this->template->iframe = $iframe;
		$this->load->model('clients/clients_m');

		// Build the client dropdown array for the form
		$this->template->clients_dropdown = $this->clients_m->build_client_dropdown();

		$base_currency = Currency::get();
		$currencies = array(__('currencies:default', array(__($base_currency['name']))));
		foreach (Settings::all_currencies() as $currency)
		{
			$currencies[$currency['code']] = $currency['name'];
		}
		$this->template->currencies = $currencies;
		$this->template->gateways = Gateway::get_enabled_gateways();
		$this->template->unique_id = $this->invoice_m->_generate_unique_id();

		$this->template->build('create');
	}
	// ------------------------------------------------------------------------

	function fix_partial_payments() {$this->invoice_m->upgradeToPartialPayments();redirect('admin');}
	public function created($unique_id)
	{
		$this->load->model('clients/clients_m');

		$invoice = (object) $this->invoice_m->get_by_unique_id($unique_id);
                
        if (!isset($invoice->id) or (isset($invoice->id) and empty($invoice->id)))
		{
            redirect('admin/invoices/all');
        }

		$this->template->invoice = $invoice;
		$this->template->unique_id = $unique_id;

		$this->template->build('created');
	}

   /**
    * Page to send out an invoice notification email to a client.
    * 
    * @param string $unique_id
    */
	public function send($unique_id)
	{
     	$result = $this->invoice_m->sendNotificationEmail($unique_id, $this->input->post('message'), $this->input->post('subject'));

		if (!$result)
		{
		    $this->session->set_flashdata('error', lang('global:couldnotsendemail'));
		}
		else
		{
		    $this->session->set_flashdata('success', lang('global:emailsent'));
		}

		redirect('admin/invoices/created/' . $unique_id, 'refresh');
	}

	// ------------------------------------------------------------------------

	/**
	 * Edits a new invoice
	 *
	 * @access	public
	 * @param	string	The unique id of the invoice to edit
	 * @return	void
	 */
	public function edit($unique_id)
	{
		$this->load->model('clients/clients_m');
		$this->load->model('files/files_m');
		$this->template->invoice = (object) $this->invoice_m->get_by_unique_id($unique_id);
		
		if (empty($this->template->invoice->items)) {
		    $this->template->invoice->items[] = array(
				'name' => '',
				'description' => '',
				'qty' => 0,
				'rate' => '0.00',
				'tax_id' => 0,
				'total' => 0,
			);
		}

		if (!isset($this->template->invoice->id) or (isset($this->template->invoice->id) and empty($this->template->invoice->id))) 
		{
		    redirect('admin/invoices/all');
		    return;
		}

		// Build the client dropdown array for the form
		$this->template->clients_dropdown = $this->clients_m->build_client_dropdown();

		if ($_POST)
		{
       		$postBuffer = $_POST;
			$buffer = isset($_POST['gateways']) ? $_POST['gateways'] : array();
			unset($postBuffer['gateways']);
	
			if ($result = $this->invoice_m->update($unique_id, $postBuffer))
			{
				$id = $this->invoice_m->getIdByUniqueId($unique_id);
				require_once APPPATH.'modules/gateways/gateway.php';
				Gateway::processItemInput('INVOICE', $id, $buffer);
				$delete_files = isset($_POST['remove_file']) ? $_POST['remove_file'] : array();
				foreach ($delete_files as $file_id)
				{
					$this->files_m->delete($file_id);
				}
				if (isset($_FILES['invoice_files']))
				{
					$this->files_m->upload($_FILES['invoice_files'], $unique_id);
				}

				$message = 'The invoice has been updated! &nbsp; &nbsp;'. anchor('admin/invoices/created/'.$unique_id, 'Resend Invoice');
                                
                $this->session->set_flashdata('success', $message);
				redirect('admin/invoices/edit/'.$unique_id, 'refresh');
                                
			}
		}
		$this->template->files = (array) $this->files_m->get_by_unique_id($unique_id);
/*
 * I've disabled currency changing on edit as they may just want to change the title and not have their exchange rate updated
 *	- Phil
 *
		$base_currency = Currency::get();

		$currencies = array('[Default] '.$base_currency['name']);
		foreach (Settings::all_currencies() as $currency_id => $currency)
		{
			$currencies[$currency_id] = $currency['name'];
		}

		$this->template->currencies = $currencies;
*/

		$this->template->gateways = Gateway::get_enabled_gateways();

		$this->template->build('edit');
	}

	// ------------------------------------------------------------------------

	/**
	 * Lists all the invoices
	 *
	 * @access	public
	 * @return	void
	 */
	public function all($offset = 0)
	{

		// Let's get the list and get this party started
		$this->_build_client_filter();
		$client_id = ($this->template->client_id != 0) ? $this->template->client_id : NULL;
                
 		$this->template->invoices = $this->invoice_m->flexible_get_all(array(
		    'client_id' => $client_id,
		    'offset' => $offset
		));
            
        // Start up the pagination 
        $this->load->library('pagination');
        $this->pagination_config['base_url'] = site_url('admin/invoices/all/');
        $this->pagination_config['uri_segment'] = 4;
        $buffer1 = $this->invoice_m->unpaid_totals($client_id);
	$buffer2 = $this->invoice_m->paid_totals($client_id);
        $this->pagination_config['total_rows'] = $buffer1['count'] + $buffer2['count'];
        $this->pagination->initialize($this->pagination_config);

		$this->template->build('all');
	}

	// ------------------------------------------------------------------------

	/**
	 * Lists all the estimates
	 *
	 * @access public
	 * @return void
	 */
	public function estimates($offset = 0)
	{
     	// Let's get the list and get this party started
		$this->_build_client_filter();
		$client_id = ($this->template->client_id != 0) ? $this->template->client_id : NULL;

		// Start up the pagination 
		$this->load->library('pagination');
		$this->pagination_config['base_url'] = site_url('admin/invoices/estimates/');
		$this->pagination_config['uri_segment'] = 4;
		$this->pagination_config['total_rows'] = $this->invoice_m->countEstimates($client_id);
		$this->pagination->initialize($this->pagination_config);

		$this->template->invoices = $this->invoice_m->get_all_estimates($client_id, $offset);

		$this->template->build('all_estimates');
	}

	// ------------------------------------------------------------------------

	/**
	 * Lists all the paid invoices (about time they paid up!)
	 *
	 * @access	public
	 * @return	void
	 */
	public function paid($offset = 0)
	{
		// Let's get the list and get this party started
		$this->_build_client_filter();
		$client_id = ($this->template->client_id != 0) ? $this->template->client_id : NULL;
		$this->template->invoices = $this->invoice_m->get_all_paid($client_id, null, $offset);
                
        // Start up the pagination 
        $this->load->library('pagination');
        $this->pagination_config['base_url'] = site_url('admin/invoices/paid/');
        $this->pagination_config['uri_segment'] = 4;
        $buffer = $this->invoice_m->paid_totals($client_id);
        $this->pagination_config['total_rows'] = $buffer['count'];
        $this->pagination->initialize($this->pagination_config);

		$this->template->list_title = "Paid Invoices";
		$this->template->build('list');
	}

	// ------------------------------------------------------------------------

	/**
	 * Lists all the unpaid invoices (just waiting for the money...)
	 *
	 * @access	public
	 * @return	void
	 */
	public function unpaid($offset = 0)
	{
		// Let's get the list and get this party started
		$this->_build_client_filter();
		$client_id = ($this->template->client_id != 0) ? $this->template->client_id : NULL;
		$this->template->invoices = $this->invoice_m->get_all_unpaid($client_id, $offset);
                
        // Start up the pagination 
        $this->load->library('pagination');
        $this->pagination_config['base_url'] = site_url('admin/invoices/unpaid/');
        $this->pagination_config['uri_segment'] = 4;
	$buffer = $this->invoice_m->unpaid_totals($client_id);
        $this->pagination_config['total_rows'] = $buffer['count'];
        $this->pagination->initialize($this->pagination_config);

		$this->template->list_title = "Unpaid Invoices";
		$this->template->build('list');
	}

	// ------------------------------------------------------------------------

	/**
	 * Lists all the overdue invoices (hope you didn't give them anything!)
	 *
	 * @access	public
	 * @return	void
	 */
	public function overdue($offset = 0)
	{
		// Let's get the list and get this party started
		$this->_build_client_filter();
		$client_id = ($this->template->client_id != 0) ? $this->template->client_id : NULL;
		$this->template->invoices = $this->invoice_m->get_all_overdue($client_id, $offset);
        
        // Start up the pagination 
        $this->load->library('pagination');
        $this->pagination_config['base_url'] = site_url('admin/invoices/overdue/');
        $this->pagination_config['uri_segment'] = 4;
	$buffer = $this->invoice_m->overdue_totals($client_id);
        $this->pagination_config['total_rows'] = $buffer['count'];
        $this->pagination->initialize($this->pagination_config);

		$this->template->list_title = "Overdue Invoices";
		$this->template->build('list');
	}

	// ------------------------------------------------------------------------

	/**
	 * Deletes an invoice (it was nice while it lasted)
	 *
	 * @access	public
	 * @param	string	The unique id of the invoice to delete
	 * @return	void
	 */
	public function delete($unique_id)
	{
	    
	    $estimate = $this->invoice_m->is_estimate($unique_id);
	    
	    if ($estimate) {
		$this->template->module = 'estimates';
	    }
	    
		if ($_POST)
		{
			// Check to make sure the action hash matches, if not kick em' to the curb
			if ($this->input->post('action_hash') !== $this->session->userdata('action_hash'))
			{
				$this->session->set_flashdata('error', 'Insecure action was attempted but caught');
				redirect('admin/dashboard');
			}
			$this->invoice_m->delete($unique_id);
			$this->session->set_flashdata('success', __(((isset($estimate) and $estimate) ? 'estimates' : 'invoices').':deleted'));
			redirect('admin/dashboard');
		}

		// We set a unique action hash here to stop CSRF attacks (hacker's beware)
		$action_hash = md5(time().$unique_id);
		$this->session->set_userdata('action_hash', $action_hash);
		$this->template->action_hash = $action_hash;
		$this->template->estimate = $estimate;
		// Lets make sure before we just go killing stuff like Rambo
		$this->template->unique_id = $unique_id;
		$this->template->build('are_you_sure');
	}

	// ------------------------------------------------------------------------

	/**
	 * Builds the client dropdown array and sets the current client id
	 *
	 * @access	public
	 * @return	void
	 */
	private function _build_client_filter()
	{
		$this->load->model('clients/clients_m');

		$dropdown_array = array('0' => 'All') + $this->clients_m->build_client_dropdown();

		$this->template->clients_dropdown = $dropdown_array;

		if ( ! $this->session->userdata('client_filter'))
		{
			$client_id = 0;
		}
		else
		{
			$client_id = $this->session->userdata('client_filter');
		}

		$client_id = isset($_POST['client_id']) ? $_POST['client_id'] : $client_id;
		$this->session->set_userdata('client_filter', $client_id);

		$this->template->client_id = $client_id;
	}

}

/* End of file: admin.php */