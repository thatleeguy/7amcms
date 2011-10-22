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

// ------------------------------------------------------------------------

/**
 * The Project Task Model
 *
 * @subpackage	Models
 * @category	Payments
 */
class Project_task_m extends Pancake_Model
{
	/**
	 * @var	string	The projects table name
	 */
	protected $projects_table = 'projects';

	/**
	 * @var string	The tasks table
	 */
	protected $tasks_table = 'project_tasks';

	/**
	 * @var string	The tasks table
	 */
	protected $times_table = 'project_times';

	/**
	 * @var	array	The array of validation rules
	 */
	protected $validate = array(
		array(
			'field'	  => 'project_id',
			'label'	  => 'Project',
			'rules'	  => 'required'
		),
		array(
			'field'	  => 'name',
			'label'	  => 'Name',
			'rules'	  => 'required'
		),
		array(
			'field'	  => 'due_date',
			'label'	  => 'Due Date',
			'rules'	  => ''
		),
	);

	// --------------------------------------------------------------------

	/**
	 * Retrieves the task sub-tasks with a given task id
	 *
	 * @access	public
	 * @param	int		The amount of results to return
	 * @param	int		The offset to start from
	 * @return	object	The result object
	 */
	public function get_tasks_by_project($project_id = null, $limit = '*', $offset = '*')
	{
		$limit == '*' or $this->db->limit($limit, $offset);

		return $this->db
			->select($this->tasks_table.'.*')
			->select('(
					SELECT ROUND(SUM(`minutes`) / 60, 2) FROM '.$this->db->dbprefix($this->times_table).' AS `times`
					WHERE task_id = '.$this->db->dbprefix($this->tasks_table).'.id
				) as tracked_hours', false)
			->where($this->db->dbprefix($this->tasks_table).'.project_id', $project_id, false)
			->order_by('completed ASC')
			->get($this->tasks_table)
			->result_array();
	}
        
	public function get_processed_task_hours($task_id) {
	    $time = $this->project_time_m->get_tracked_task_time(null, $task_id, true);
	    $task['tracked_hours'] = $time['time'];
	    $buffer = $task['tracked_hours'];
	    $buffer = explode('.', $buffer);
	    $buffer_hours = ($buffer[0] > 10) ? $buffer[0] : '0' . $buffer[0];
	    if (isset($buffer[1])) {
		$buffer[1] = '0.' . $buffer[1];
		$buffer[1] = (float) $buffer[1];
		$buffer[1] = round($buffer[1] * 60);

		$buffer_minutes = ($buffer[1] > 9) ? $buffer[1] : '0' . $buffer[1];
	    } else {
		$buffer_minutes = '00';
	    }
	    return $buffer_hours . ':' . $buffer_minutes;
	}
	
	public function get_tasks_and_times_by_project($project_id, $per_page = 10, $offset = 0, $get_time_stuff = true)
	{
		$project = $this->project_m->get_project_by_id($project_id)->row();
		$noTask = $this->project_time_m->get_tracked_task_time($project_id, 0, true);
		
		// This will grab some time data for backend type stuff
		if ($get_time_stuff)
		{
			$this->db
				->select('IF(start_time, 1, 0) as entry_started, start_time as entry_started_time, '.$this->times_table.'.date as entry_started_date', false)
				->join($this->times_table, $this->times_table.'.task_id = '.$this->tasks_table.'.id AND end_time = "" AND '.$this->db->dbprefix($this->times_table).'.user_id = "'.$this->current_user->id.'"', 'left');
		}
		
		if ($noTask['time'] > 0) {
		    $tasks = $this->project_task_m->get_tasks_by_project($project_id, $per_page - 1, $offset - (1 * ($offset/$per_page)));
		} else {
		    $tasks = $this->project_task_m->get_tasks_by_project($project_id, $per_page, $offset);
		}

		$buffer = $tasks;
		$tasks = array();
		
		if ($noTask['time'] > 0) {
		    $data = array(
		        'tracked_hours' => $noTask['time'],
		        'time_items' => $noTask['records'],
		        'not_a_task' => true,
		        'id' => 0,
		        'completed' => 0,
		        'due_date' => 0,
		        'rate' => $project->rate,
		        'name' => 'No Task',
		        'hours' => 0
		    );
    
		    $buffer2 = $data['tracked_hours'];
		    $buffer2 = explode('.', $buffer2);
		    $buffer_hours = ($buffer2[0] > 10) ? $buffer2[0] : '0'.$buffer2[0];
		    if (isset($buffer2[1])) {
			$buffer2[1] = '0.' . $buffer2[1];
			$buffer2[1] = (float) $buffer2[1];
			$buffer2[1] = round($buffer2[1] * 60);

			$buffer_minutes = ($buffer2[1] > 9) ? $buffer2[1] : '0' . $buffer2[1];
		    } else {
			$buffer_minutes = '00';
		    }
		    $data['processed_tracked_hours'] = $buffer_hours.':'.$buffer_minutes;
		    $tasks[] = $data;
		}

		foreach ($buffer as $task) {
		    $records = $this->project_time_m->get_tracked_task_time($project_id, $task['id'], true);
		    $task['tracked_hours'] = $records['time'];
		    $buffer = $task['tracked_hours'];
		    $buffer = explode('.', $buffer);
		    $buffer_hours = ($buffer[0] > 9) ? $buffer[0] : '0'.$buffer[0];
		    if (isset($buffer[1])) {
			$buffer[1] = '0.'.$buffer[1];
			$buffer[1] = (float) $buffer[1];
			$buffer[1] = round($buffer[1] * 60);
			
		        $buffer_minutes = ($buffer[1] > 9) ? $buffer[1] : '0'.$buffer[1];
		    } else {
		        $buffer_minutes = '00';
		    }
		    $task['processed_tracked_hours'] = $buffer_hours.':'.$buffer_minutes;
		    $task['time_items'] = $records['records'];
		    $tasks[$task['id']] = $task;
		}

		return $tasks;
    }

	// --------------------------------------------------------------------

	/**
	 * Retrieves the project tasks, given a project, optionally limited and offset
	 *
	 * @access	public
	 * @param	int		The amount of results to return
	 * @param	int		The offset to start from
	 * @return	object	The result object
	 */
    public function get_tasks_by_parent($task_id = null, $limit = '*', $offset = '*')
    {
        $limit == '*' or $this->db->limit($limit, $offset);
        
        $this->db->where('task_id', $task_id);
        $this->db->order_by('completed ASC');
        $query = $this->db->get($this->tasks_table);
        
        if ($query->num_rows() > 0)
        {
            return $query;
        }
        return false;
    }

	// --------------------------------------------------------------------

	/**
	 * Retrieves a certain number of upcoming tasks
	 *
	 * @access	public
	 * @param	int		The tast id
	 * @return	object	The result object
	 */
	public function get_upcoming_tasks($count = 5)
	{
		$this->db
			->select('project_tasks.*, projects.name as project_name')
			->join('projects', 'project_tasks.project_id = projects.id')
			->where('project_tasks.completed', 0)
			->limit($count)
        	->order_by('due_date DESC');

		$query = $this->db->get($this->tasks_table);

		if ($query->num_rows() > 0)
		{
			return $query->result_array();
		}
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Retrieves a single tast by its ID
	 *
	 * @access	public
	 * @param	int		The tast id
	 * @return	object	The result object
	 */
	public function get_task_by_id($task_id)
	{
		$this->db->where('id', $task_id);
		$this->db->limit(1);

		$query = $this->db->get($this->tasks_table);

		if ($query->num_rows() > 0)
		{
			return $query;
		}
		return FALSE;
	}
        
        function getProjectIdById($id) {
            $row = $this->db->select('project_id')->where('id', $id)->get($this->tasks_table)->row_array();
            if (isset($row['project_id'])) {
                return $row['project_id'];
            } else {
                return 0;
            }
        }

	// --------------------------------------------------------------------

	/**
	 * Returns a count of all tasks belonging to a projects
	 *
	 * @access	public
	 * @param   int    The id of the project
	 * @return	int
	 */
	public function count_all_tasks($project_id)
	{
		return $this->db
			->where('project_id', $project_id)
			->count_all_results($this->tasks_table);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Returns a count of all incomplete tasks belonging to a project
	 *
	 * @access	public
	 * @param   int    The id of the project
	 * @return	int
	 */
	public function count_all_incomplete_tasks($project_id)
	{
		return $this->db
			->where('project_id', $project_id)
		    ->where('completed', 0)
            ->count_all_results($this->tasks_table);
	}

	// --------------------------------------------------------------------

	/**
	 * Inserts a new task
	 *
	 * @access	public
	 * @param	array 	The task array
	 * @return	int
	 */
	public function insert_task($input)
	{
		if ( ! $this->validate($input))
		{
			return FALSE;
		}

		return $this->db->set(array(
			'project_id'	=> $input['project_id'],
			'name'			=> $input['name'],
			'due_date'		=> ! empty($input['due_date']) ? read_date_picker($input['due_date']) : '',
			'notes'			=> ! empty($input['notes']) ? ($input['notes']) : '',
			'rate'			=> $input['rate'],
			'completed'		=> 0,
		))->insert($this->tasks_table);
	}

	// --------------------------------------------------------------------

	/**
	 * Updates a task
	 *
	 * @access	public
	 * @param	array 	The task array
	 * @return	int
	 */
	public function update_task($task)
	{
		if ( ! isset($task['id']))
		{
			return false;
		}
		$this->db->where('id', $task['id']);
		
		unset($task['id']);

		!empty($task['due_date']) AND $task['due_date'] = read_date_picker($task['due_date']);

		return $this->db->update($this->tasks_table, $task);
	}

	// --------------------------------------------------------------------

	/**
	 * Deletes a task by its ID
	 *
	 * @access	public
	 * @param	int		The task id
	 * @return	object	The result object
	 */
	public function delete_task($task_id)
	{
		$this->db->where('id', $task_id);

		return $this->db->delete($this->tasks_table);
	}
}

/* End of file: project_task_m.php */
