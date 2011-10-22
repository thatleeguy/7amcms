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
 * The Invoice API controller
 *
 * @subpackage	Controllers
 * @category	API
 */

class Invoices extends REST_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->model('invoices/invoice_m');
	}

	// /api/v1/invoices
	// limit = 5
	// start = 0
	// sort_by = email (default: id)
	// sort_dir = asc (default: asc)
	// client_d = asc (default: asc)
	public function index_get($type = null)
	{
		$this->list_get($type);
	}
	
	public function paid_get()
	{
		$this->list_get('paid');
	}

	public function unpaid_get()
	{
		$this->list_get('unpaid');
	}

	public function overdue_get()
	{
		$this->list_get('unpaid');
	}
	
	public function list_get($type = null)
	{
		if ($this->get('limit') or $this->get('start'))
		{
			$this->invoice_m->limit($this->get('limit'), $this->get('start'));
		}
		
		if ($this->get('client_id'))
		{
			$this->invoice_m->where('client_id', $this->get('client_id'));
		}
		
		switch ($type)
		{
			case 'paid':
				$this->invoice_m->where('is_paid', 1);
			break;

			case 'unpaid':
				$this->invoice_m->where('is_paid', 0);
			break;

			case 'overdue':
				$this->invoice_m->where(array('invoices.is_paid' => 0,'due_date <' => time()));
			break;
		}
		
		$sort_by = $this->get('sort_by') ? $this->get('sort_by') : 'invoices.id';
		$sort_dir = $this->get('sort_dir') ? $this->get('sort_dir') : 'asc';

		$invoices = $this->invoice_m->order_by($sort_by, $sort_dir)->get_all_for_api();

		$this->response(array(
			'invoices' => $invoices,
			'count' => count($invoices),
		), 200);
	}

	
	public function show_get()
	{
		$this->load->model('invoices/invoice_task_m');
		
		if ( ! $this->get('id'))
		{
			$this->response(array('status' => false, 'error_message' => 'No id was provided.'), 400);
		}
		
		$invoice = $this->invoice_m->get($this->get('id'));
		
		$tasks = $this->invoice_task_m
			->select('id, name, rate, hours, IF(due_date > 0, FROM_UNIXTIME(due_date), NULL) as due_date, completed', FALSE)
			->get_many_by('invoice_id', $this->get('id'));

		foreach ($tasks as &$task)
		{
			$task->id = (int) $task->id;
			$task->rate = (float) $task->rate;
			$task->hours = (int) $task->hours;
			$task->completed = (bool) $task->completed;
		}
		
		$this->response(array(
			'invoice' => $invoice,
			'tasks' => $tasks
		), 200);
	}
	
	
	// /api/v1/invoices/new
	public function new_post()
	{
		if (empty($_POST))
		{
			$this->response(array('status' => false, 'error_message' => 'No details were provided.'), 400);
		}
		
		$items = array();

		if ($this->post('project_id'))
		{
			$this->load->model('projects/project_m');
			$this->load->model('projects/project_task_m');

			if ( ! $project = $this->project_m->get_project_by_id($this->post('project_id')))
			{
				$this->response(array('status' => false, 'error_message' => 'This project could not be found.'), 404);
			}
			
			// Dan likes weird returns
			$project = $project->row();

			if ($tasks = $this->project_task_m->get_tasks_by_project($project->id))
			{
				foreach ($tasks->result() as $task)
				{
					$items[] = array(
						'description' => $task->name,
						'qty' => $task->hours,
						'rate' => $task->rate,
						'tax_id' => 0,
						'total' => $task->hours * $task->rate,
					);
				}
			}
			
			else
			{
				$this->response(array('status' => false, 'error_message' => 'This project has no tasks, so no invoice can be made.'), 400);
			}
		}

		if ($post_items = $this->input->post('invoice_item'))
		{
			for ($i = 0; $i < count($post_items['description']); $i++)
			{
				$items[] = array(
					'description' => $post_items['description'][$i],
					'qty' => $post_items['quantity'][$i],
					'rate' => $post_items['rate'][$i],
					'tax_id' => isset($post_items['tax_id'][$i]) ? $post_items['tax_id'][$i] : 0,
					'total' => $post_items['cost'][$i],
				);
			}
		}
		
		$input = array(
			'client_id' => $this->post('client_id'),
			'type' => $this->post('type'),
			'amount' => $this->post('amount'),
			'description' => $this->post('description'),
			'notes' => $this->post('notes'),
			'is_paid' => $this->post('is_paid'),
			'due_date' => $this->post('due_date'),
			'is_recurring' => $this->post('is_recurring'),
			'frequency' => $this->post('frequency'),
			'auto_send' => $this->post('auto_send'),
			'currency' => $this->post('currency'),
			
			'items' => $items
		);

		if ($unique_id = $this->invoice_m->insert($input))
		{
			$this->response(array('status' => true, 'unique_id' => $unique_id, 'message' => sprintf('Invoice #%s has been created.', $unique_id)), 200);
		}
		
		else
		{
			$this->response(array('status' => false, 'error_message' => current($this->validation_errors())), 400);
		}
	}

	public function update_post()
	{
		if ( ! $unique_id = $this->post('unique_id'))
		{
			$this->response(array('status' => false, 'error_message' => 'No id was provided.'), 400);
		}
		
		$invoice = $this->invoice_m->get($unique_id);

		if (empty($invoice))
		{
			$this->response(array('status' => false, 'error_message' => 'This invoice does not exist!'), 404);
		}
		
		if ($this->invoice_m->update($unique_id, $this->input->post()))
		{
			$this->response(array('status' => true, 'message' => sprintf('Project #%d has been updated.', $unique_id)), 200);
		}
		else
		{
			$this->response(array('status' => false, 'error_message' => current($this->validation_errors())), 400);
		}
	}
	
	public function delete_post($unique_id = null)
	{
		$unique_id OR $unique_id = $this->post('unique_id');
		
		$invoice = $this->invoice_m->get($unique_id);

		if (empty($invoice))
		{
			$this->response(array('status' => false, 'error_message' => 'This invoice does not exist!'), 404);
		}
		
		$this->invoice_m->delete($unique_id);
		
		$this->response(array('status' => true, 'message' => sprintf('Invoice #%d has been deleted.', $unique_id)), 200);
		
	}

}