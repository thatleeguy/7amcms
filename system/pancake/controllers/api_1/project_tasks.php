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

class Project_tasks extends REST_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->model('projects/project_task_m');
	}
	
	// /api/v1/projects
	// limit = 5
	// start = 0
	// sort_by = email (default: id)
	// sort_dir = asc (default: asc)
	public function index_get()
	{
		if ( ! $this->get('id'))
		{
			$this->response(array('status' => false, 'error_message' => 'No project id was provided.'), 400);
		}
		
		if ($this->get('limit') or $this->get('start'))
		{
			$this->project_task_m->limit($this->get('limit'), $this->get('start'));
		}

		$sort_by = $this->get('sort_by') ? $this->get('sort_by') : 'id';
		$sort_dir = $this->get('sort_dir') ? $this->get('sort_dir') : 'asc';
		
		$this->project_task_m->order_by($sort_by, $sort_dir);
		
		$tasks = $this->project_task_m
			->select('id, name, rate, hours, IF(due_date > 0, FROM_UNIXTIME(due_date), NULL) as due_date, completed', FALSE)
			->get_many_by('project_id', $this->get('id'));

		foreach ($tasks as &$task)
		{
			$task->id = (int) $task->id;
			$task->rate = (float) $task->rate;
			$task->hours = (int) $task->hours;
			$task->completed = (bool) $task->completed;
		}
		
		$this->response(array(
			'tasks' => $tasks,
			'count' => count($tasks),
		), 200);
	}
	
	public function show_get()
	{
		if ( ! $this->get('id'))
		{
			$this->response(array('status' => false, 'error_message' => 'No id was provided.'), 400);
		}
		
		$data['task'] = $this->project_task_m
			->select('id, name, rate, hours, IF(due_date > 0, FROM_UNIXTIME(due_date), NULL) as due_date, project_id, completed', FALSE)
			->get($this->get('id'));

		$this->response($data, 200);
	}
	
	
	// /api/v1/projects/new
	public function new_post()
	{
		if (empty($_POST))
		{
			$this->response(array('status' => false, 'error_message' => 'No details were provided.'), 400);
		}
		
		if ($id = $this->project_task_m->insert($this->input->post()))
		{
			$this->response(array('status' => true, 'task_id' => $id, 'message' => sprintf('Task #%s has been created.', $id)), 200);
		}
		else
		{
			$this->response(array('status' => false, 'error_message' => current($this->validation_errors())), 400);
		}
	}

	
	public function delete_post($id = null)
	{
		$id OR $id = $this->post('id');
		
		$task = $this->project_task_m->get($id);

		if (empty($task))
		{
			$this->response(array('status' => false, 'error_message' => 'This task does not exist!'), 404);
		}
		
		$this->project_task_m->delete_task($id);
		
		$this->response(array(
			'status' => true,
			'message' => sprintf('Task #%d has been deleted.', $task->id),
		), 200);
		
	}

	public function log_time_post($id = null)
	{
		$id OR $id = $this->post('id');

		$task = $this->project_task_m->get($id);

		if ( ! $this->post('minutes'))
		{
			$this->response(array('status' => false, 'error_message' => 'Please provide an number of \'minutes\', positive or negative.'), 400);
		}

		if (empty($task))
		{
			$this->response(array('status' => false, 'error_message' => 'This task does not exist!'), 404);
		}
		
		$this->project_task_m->add_time($id, $this->post('minutes'));

		$this->response(array('status' => true, 'message' => 'Time logged.'), 200);
	}

	public function complete_post($id = null)
	{
		$id OR $id = $this->post('id');

		// toggle completion status. Ajax Only
		$task = $this->project_task_m->where('completed', 0)->get_task_by_id($id);

		if (empty($task))
		{
			$this->response(array('status' => false, 'error_message' => 'There is no open task with the \'id\' '.$id.'!'), 404);
		}

		// Complete
		$task['completed'] = 1;

		// update the model
		$this->project_task_m->update_task($task);

		$this->response(array('status' => true, 'message' => 'Task marked as complete.'), 200);
	}
	

	public function reopen_post($id = null)
	{
		$id OR $id = $this->post('id');

		// toggle completion status. Ajax Only
		$task = $this->project_task_m->where('completed', 1)->get_task_by_id($id)->row_array();

		if (empty($task))
		{
			$this->response(array('status' => false, 'error_message' => 'There is no completed task with the \'id\' '.$id.'!'), 404);
		}

		// Complete
		$task['completed'] = 0;

		// update the model
		$this->project_task_m->update_task($task);

		$this->response(array('status' => true, 'message' => 'Task marked as open.'), 200);
	}

}