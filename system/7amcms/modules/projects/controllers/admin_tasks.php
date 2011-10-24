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
 * @since		Version 1.1
 */

/**
 * The admin controller for tasks
 *
 * @subpackage	Controllers
 * @category	Projects
 */
class Admin_Tasks extends Admin_Controller {

	/**
	 * Load in the dependencies
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('project_m');
		$this->load->model('project_task_m');
	}

	public function index()
	{
		access_denied();
	}

	public function create($project_id = NULL)
	{
		IS_AJAX OR access_denied();

		if ($_POST)
		{
			// All form validation is handled in the model, so lets just throw it the data
			if ($result = $this->project_task_m->insert_task($_POST))
			{
				$message = array('success' => __('tasks:create_succeeded'));
			}
			else
			{
				if ($errors = validation_errors('<p>', '</p>'))
				{
					$message = array('error' => $errors);
				}
				else
				{
					$message = array('error' => __('tasks:create_failed'));
				}
			}

			output_json($message);
		}

		$this->load->view('task_form', array(
			'project' => $this->project_m->get_project_by_id($project_id)->row()
		));
	}
        
	public function get_delete_form($task_id)
	{
		$this->load->view('delete_task', array('task_id' => $task_id));
	}

	public function delete($task_id)
	{
		// delete task. Ajax Only.
		$task = $this->project_task_m->get_task_by_id($task_id);

		if ($task->num_rows() == 0)
		{
			$message = array('error' => 'Invalid Object');
		}
		else
		{
			$message = array('success' => 'Deleted Object');
			$this->project_task_m->delete_task($task_id);
		}

		output_json($message);
	}

	public function toggle_status($task_id)
	{
		// toggle completion status. Ajax Only
		$task = $this->project_task_m->get_task_by_id($task_id);
		$task = $task->row_array();

		// uggle check to toggle the value
		$task['completed'] = empty($task['completed']);

		// update the model
		$this->project_task_m->update_task($task);

		# Redirect. jQuery.load() will take care of fetching the updated row. Hacky, but it fixes the bug. - Bruno.
		redirect('admin/projects/view/'.$task['project_id']);
	}
}
