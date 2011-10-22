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
 * The Project Time Model
 *
 * @subpackage	Models
 * @category	Payments
 */
class Project_time_m extends Pancake_Model
{
	/**
	 * @var	string	The projects table name
	 */
	protected $projects_table = 'projects';

	/**
	 * @var string	The times table
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
			'field'	  => 'start_time',
			'label'	  => 'times.label.start_time',
			'rules'	  => 'required|max_length[8]'
		),
		array(
			'field'	  => 'end_time',
			'label'	  => 'times.label.end_time',
			'rules'	  => 'required|max_length[8]'
		),
		array(
			'field'	  => 'date',
			'label'	  => 'times.label.date',
			'rules'	  => ''
		),
		array(
			'field'	  => 'task_id',
			'label'	  => 'times.label.task_id',
			'rules'	  => ''
		),
		array(
			'field'	  => 'note',
			'label'	  => 'times.label.note',
			'rules'	  => ''
		),
	);

	// --------------------------------------------------------------------

	/**
	 * Retrieves the time sub-times with a given taks id
	 *
	 * @access	public
	 * @param	int		The amount of results to return
	 * @param	int		The offset to start from
	 * @return	object	The result object
	 */
	public function get_times_by_project($project_id = null, $limit = '*', $offset = '*')
	{
		$limit == '*' or $this->db->limit($limit, $offset);
		
		return $this->db
			->where('project_id', $project_id)
			->where('end_time !=', '')
			->get($this->times_table)
			->result();
	}

	/**
	 * Retrieves the times that have no task assigned
	 *
	 * @access	public
	 * @param	int		The amount of results to return
	 * @param	int		The offset to start from
	 * @return	object	The result object
	 */
	public function get_extras_by_project($project_id = null, $limit = '*', $offset = '*')
	{
		$this->db->where('task_id', 0);
		return $this->get_times_by_project($project_id, $limit, $offset);
	}

	// --------------------------------------------------------------------

	/**
	 * Inserts a new time
	 *
	 * @access	public
	 * @param	array 	The time array
	 * @return	int
	 */
	public function insert($input)
	{
		if ( ! $this->validate($input))
		{
			return FALSE;
		}
		
		return parent::insert(array(
			'project_id'	=> $input['project_id'],
			'start_time'	=> ! empty($input['start_time']) ? $input['start_time'] : '',
			'end_time'		=> ! empty($input['end_time']) ? $input['end_time'] : '',
			'date'			=> ! empty($input['date']) ? read_date_picker($input['date']) : time(),
			'note'			=> ! empty($input['note']) ? $input['note'] : '',
			'task_id'		=> ! empty($input['task_id']) ? $input['task_id'] : 0,
			'user_id'		=> $input['user_id'],
			'minutes' => isset($input['minutes']) ? $input['minutes'] : ceil((strtotime($input['end_time']) - strtotime($input['start_time'])) / 60)
		));
	}
        
	/**
	 * Get the logged time for any given task.
	 * 
	 * If $hours, the number is in hours. Otherwise, it's in minutes.
	 * 
	 * @param integer $project_id
	 * @param integer $task_id
	 * @param boolean $hours
	 * @return double 
	 */
	public function get_tracked_task_time($project_id, $task_id, $hours = false)
	{
	    if ($project_id > 0)
		{
	        $this->db->where('project_id', $project_id);
	    }
	
	    $task_time = $this->db
			->select('sum(minutes) as tracked_task_time')
			->where('task_id', $task_id)
			->where('end_time !=', '')
			->get($this->times_table)
			->row_array();
	    
		$total_time = $hours ? round($task_time['tracked_task_time'] / 60, 2) : $task_time['tracked_task_time'];
	    
		$task_time = $this->db
			->select("{$this->times_table}.*, users.username, meta.first_name, meta.last_name")
			->join('users', 'users.ID = project_times.user_id')
			->join('meta', 'meta.user_id = project_times.user_id')
			->where('end_time !=', '')
			->where('project_id', $project_id)
			->where('task_id', $task_id)
			->get($this->times_table)
			->result_array();
	
	    return array(
	        'records' => $task_time,
	        'time' => $total_time
	    );
	}
}

/* End of file: project_time_m.php */