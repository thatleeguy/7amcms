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
 * The admin controller for times
 *
 * @subpackage	Controllers
 * @category	Projects
 */
class Admin_Times extends Admin_Controller {

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
		$this->load->model('project_time_m');
		$this->lang->load('times', Settings::get('language'));
	}

	public function index()
	{
		access_denied();
	}

	public function create($project_id = NULL)
	{
		// IS_AJAX OR access_denied();
        
		if ($_POST)
		{
			$minutes = $this->_start_end_date_to_minutes(
				$this->input->post('start_time'), 
				$this->input->post('end_time'), 
				$this->input->post('date')
			);

			$result = $this->project_time_m->insert(array(
				'project_id'	=> $this->input->post('project_id'),
				'start_time'	=> $this->input->post('start_time'),
				'end_time'		=> $this->input->post('end_time'),
				'minutes'		=> $minutes,
				'date'			=> $this->input->post('date'),
				'note'			=> $this->input->post('note'),
				'task_id'		=> $this->input->post('task_id'),
				'user_id'		=> $this->current_user->id,
			));

			// All form validation is handled in the model, so lets just throw it the data
			if ($result)
			{
				$message = array('success' => $this->lang->line('times.create.succeeded'));
			}
			else
			{
				if ($errors = validation_errors('<p>', '</p>'))
				{
					$message = array('error' => $errors);
				}
				else
				{
					$message = array('error' => $this->lang->line('times.create.failed'));
				}
			}

			output_json($message);
		}

		$tasks = $this->project_task_m->where('project_id', $project_id)->order_by('name')->get_all();
		$tasks_select = array('' => '-- Not related to a task --');		
		foreach ($tasks as $task)
		{
			$tasks_select[$task->id] = $task->name;
		}

		$this->load->view('time_form', array(
			'project' => $this->project_m->get_project_by_id($project_id)->row(),
			'tasks_select' => $tasks_select,
		));
	}

	public function delete($time_id)
	{
		// delete time. Ajax Only.
		$time = $this->project_time_m->get_time_by_id($time_id);

		if ($time->num_rows() == 0)
		{
			$message = array('error' => 'Invalid Object');
		}
		else
		{
			$message = array('success' => 'Deleted Object');
			$this->project_time_m->delete_time($time_id);
		}

		output_json($message);
	}
	
	
	public function view_entries($type, $id)
	{
		switch ($type)
		{
			case 'project':
				$entries = $this->project_time_m->where('task_id', 0)->get_times_by_project($id);
			break;

			case 'task':
				$entries = $this->project_time_m->get_many_by(array(
					'task_id' => $id,
					'end_time !=' => "",
				));
			break;
		}
		
		$this->load->view('view_entries', array(
			'entries' => $entries,
		));
	}
	
	public function ajax_set_entry()
	{
		$post = $this->input->post();
		
		$date = read_date_picker($post['date']);
		
		$minutes = $this->_start_end_date_to_minutes($post['start_time'], $post['end_time'], $date);
		
		$this->db
			->where('id', $post['id'])
			->update('project_times', array(
				'start_time' => $post['start_time'],
				'end_time' => $post['end_time'],
				'date' => $date,
				'minutes' => $minutes,
			)
		);
		
		echo json_encode(array('new_duration' => format_seconds($minutes * 60)));
	}

	public function ajax_delete_entry()
	{
		$this->db
			->where('id', $this->input->post('id'))
			->delete('project_times');
	}

	public function ajax_start_timer()
	{
		$this->db
			->set('date', time())
			->set('start_time', date('H:i'))
			->set('task_id', $this->input->post('task_id'))
			->set('project_id', $this->input->post('project_id'))
			->set('user_id', $this->current_user->id)
			->insert('project_times');
			
		echo $this->db->last_query();
	}
	
	public function ajax_stop_timer()
	{
		$task_id = $this->input->post('task_id');
		$project_id = $this->input->post('project_id');
		
		$criteria = array(
			'task_id' => $task_id,
			'project_id' => $project_id,
			'end_time' => '',
			'user_id' => $this->current_user->id,
		);
		
		$time = $this->db->get_where('project_times', $criteria)->row();
		
		if ( ! $time)
		{
			// Pfft
			set_status_header(400);
			exit;
		}
		
		$end_time = date('H:i');
		
		if ($time->start_time == $end_time) {
		    # What are the odds that someone worked EXACTLY 24 hours in one go? That's the only way this'd break.
		    # Otherwise, I'm using this to avoid a bug with duplicating times when there is no logged time.
		    $this->db->where($criteria)->update('project_times', array('end_time' => $end_time, 'minutes' => 0.05));
		} else {
		    $minutes = $this->_start_end_date_to_minutes($time->start_time, $end_time, $time->date);
		
		    $this->db
			->where($criteria)
			->update('project_times', array(
				'end_time' => $end_time,
				'minutes' => $minutes,
			)
		    );
		}
		
		$tracked = $this->project_time_m->get_tracked_task_time($project_id, $task_id);

		echo json_encode(array('new_total_time' => substr(format_seconds($tracked['time'] * 60, true), 0, 5)));
	}
	
	private function _start_end_date_to_minutes($start_time, $end_time, $date)
	{
		$start_date = new DateTime(date('Y-m-d', $date));
		$end_date = new DateTime(date('Y-m-d', $date));
		
		// Set the time, as accurate as they gave
		call_user_func_array(array($start_date, 'setTime'), explode(':', trim($start_time)));
		call_user_func_array(array($end_date, 'setTime'), explode(':', trim($end_time)));
		
		($end_time < $start_time) and $end_date->modify('+1 day');
		
		return ($end_date->format('U') - $start_date->format('U')) / 60;
	}
}
