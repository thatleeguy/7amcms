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

class Projects extends REST_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->model('projects/project_m');
	}
	
	// /api/v1/projects
	// limit = 5
	// start = 0
	// sort_by = email (default: id)
	// sort_dir = asc (default: asc)
	public function index_get()
	{
		if ($this->get('limit') or $this->get('start'))
		{
			$this->project_m->limit($this->get('limit'), $this->get('start'));
		}

		$sort_by = $this->get('sort_by') ? $this->get('sort_by') : 'id';
		$sort_dir = $this->get('sort_dir') ? $this->get('sort_dir') : 'asc';
		
		$projects = $this->project_m->->order_by($sort_by, $sort_dir)->get_all();

		$this->response(array(
			'projects' => $projects,
			'count' => count($projects),
		), 200);
	}
	
	public function show_get()
	{
		$this->load->model('projects/project_task_m');
		
		if ( ! $this->get('id'))
		{
			$this->response(array('status' => false, 'error_message' => 'No id was provided.'), 400);
		}
		
		if ( ! $project = $this->project_m->get($this->get('id')))
		{
			$this->response(array('status' => false, 'error_message' => 'This project could not be found.'), 404);
		}
		
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
			'project' => $project,
			'tasks' => $tasks
		), 200);
	}
	
	
	// /api/v1/projects/new
	public function new_post()
	{
		if (empty($_POST))
		{
			$this->response(array('status' => false, 'error_message' => 'No details were provided.'), 400);
		}
		
		if ($id = $this->project_m->insert($this->input->post()))
		{
			$this->response(array('status' => true, 'project_id' => $id, 'message' => sprintf('Project #%s has been created.', $id)), 200);
		}
		else
		{
			$this->response(array('status' => false, 'error_message' => current($this->validation_errors())), 400);
		}
	}

	public function update_post()
	{
		if ( ! $this->post('id'))
		{
			$this->response(array('status' => false, 'error_message' => 'No id was provided.'), 400);
		}
		
		$project = $this->project_m->get($this->post('id'));

		if (empty($project))
		{
			$this->response(array('status' => false, 'error_message' => 'This project does not exist!'), 404);
		}
		
		if ($this->project_m->update($this->input->post()))
		{
			$this->response(array(
				'status' => true,
				'message' => sprintf('Project #%d has been updated.', $project->id),
			), 200);
		}
		else
		{
			$this->response(array('status' => false, 'error_message' => current($this->validation_errors())), 400);
		}
	}
	
	public function delete_post($id = null)
	{
		$id OR $id = $this->post('id');
		
		$project = $this->project_m->get($id);

		if (empty($project))
		{
			$this->response(array('status' => false, 'error_message' => 'This project does not exist!'), 404);
		}
		
		$this->project_m->delete_project($id);
		
		$this->response(array(
			'status' => true,
			'message' => sprintf('Project #%d has been deleted.', $project->id),
		), 200);
		
	}

}