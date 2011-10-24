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
 * The Project Model
 *
 * @subpackage	Models
 * @category	Payments
 */
class Project_m extends Breakfast_Model
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
	protected $time_table = 'project_times';

	/**
	 * @var	array	The array of validation rules
	 */
	protected $validate = array(
		array(
			'field'	  => 'client_id',
			'label'	  => 'Client',
			'rules'	  => 'required'
		),
		array(
			'field'	  => 'name',
			'label'	  => 'Name',
			'rules'	  => 'required'
		),
		array(
			'field'	  => 'description',
			'label'	  => 'Description',
			'rules'	  => 'xss_clean'
		),
	);

	// --------------------------------------------------------------------

	/**
	 * Retrieves the projects, optionally limited and offset
	 *
	 * @access	public
	 * @param	int		The amount of results to return
	 * @param	int		The offset to start from
	 * @return	object	The result object
	 */
	public function get_projects($limit = '*', $offset = '*')
	{
		if ($limit !== '*')
		{
			$this->db->limit($limit, $offset);
		}

		$query = $this->db
			->select('projects.*, clients.first_name, clients.last_name, clients.email, clients.company, clients.phone, currencies.code as currency_code')
		    ->order_by('date_entered', 'DESC')
			->join('clients', 'projects.client_id = clients.id')
			->join('currencies', 'projects.currency_id = currencies.id', 'left')
			->get($this->projects_table);

		if ($query->num_rows() > 0)
		{
            $this->load->model('project_task_m', 'tasks');
            $results = $query->result();
            
            foreach($results as &$row)
            {
                $row->total_tasks = $this->tasks->count_all_tasks($row->id);
                $row->incomplete_tasks = $this->tasks->count_all_incomplete_tasks($row->id);
                $row->complete_tasks = $row->total_tasks - $row->incomplete_tasks;
            }
            
            return $results;
		}
		
		return false;
	}
        
        /**
         * Get a project and all the necessary information to display it in the timesheet.
         * 
         * @param string $unique_id
         * @return array 
         */
        public function getForTimesheet($unique_id) {
            $buffer = $this->db->select('due_date, client_id, id, name')->where('unique_id', $unique_id)->get($this->projects_table)->row_array();
            if (isset($buffer['id']) and !empty($buffer['id'])) {
                $CI = &get_instance();
                $client = (array) $CI->load->model('clients/clients_m')->get_by(array('id' => $buffer['client_id']));
                $buffer['client'] = $client;
                $tasks = $this->project_task_m->get_tasks_and_times_by_project($buffer['id'], '*', null, false);
                $users = array();
                $minutes = 0;
                foreach ($tasks as &$task) {
                    foreach ($task['time_items'] as &$item) {
                        $users[$item['user_id']] = true;
                        
                        $minutes = $minutes + $item['minutes'];
                    }
                }
                $buffer['total_hours'] = round($minutes / 60, 2);
                $buffer['user_count'] = count($users);
                $buffer['tasks'] = $tasks;
                return $buffer;
            } else {
                return false;
            }
        }
        
        function getTotalHoursForProject($project_id, $formatted = false) {
            $buffer = $this->db->select('id')->where('id', $project_id)->get($this->projects_table)->row_array();
            if (isset($buffer['id']) and !empty($buffer['id'])) {
                $tasks = $this->project_task_m->get_tasks_and_times_by_project($buffer['id']);
                $minutes = 0;
                foreach ($tasks as $task) {
                    foreach ($task['time_items'] as $item) {
                        $minutes = $minutes + $item['minutes'];
                    }
                }
                $hours = round($minutes / 60, 2);
                if ($formatted) {
                    $buffer = $hours;
                    $buffer = explode('.', $buffer);
                    $buffer_hours = ($buffer[0] > 10) ? $buffer[0] : '0'.$buffer[0];
                    if (isset($buffer[1])) {
                        $buffer[1] = round(($buffer[1] * 60) / 100);
                        $buffer_minutes = ($buffer[1] > 10) ? $buffer[1] : '0'.$buffer[1];
                    } else {
                        $buffer_minutes = '00';
                    }
                    return $buffer_hours.':'.$buffer_minutes;
                } else {
                    return $hours;
                }
            } else {
                return 0;
            }
        }

	// --------------------------------------------------------------------

	/**
	 * Retrieves a project by its ID
	 *
	 * @access	public
	 * @param	int		The project id
	 * @return	object	The result object
	 */
	public function get_project_by_id($project_id)
	{
		$this->db->select('projects.*, clients.first_name, clients.last_name, clients.email, clients.company, clients.phone, currencies.code as currency_code')
		         ->where($this->projects_table . '.id', $project_id)
		         ->join('clients', 'projects.client_id = clients.id')
			->join('currencies', 'projects.currency_id = currencies.id', 'left');
        
		$query = $this->db->get($this->projects_table);

		if ($query->num_rows() > 0)
		{
			return $query;
		}
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns a count of all the projects
	 *
	 * @access	public
	 * @return	int
	 */
	public function count_all_projects()
	{
		return $this->db->count_all($this->projects_table);
	}

	// --------------------------------------------------------------------

	/**
	 * Inserts a new project
	 *
	 * @access	public
	 * @param	array 	The image array
	 * @return	int
	 */
	public function insert($input)
	{
		if ( ! $this->validate($input))
		{
			return FALSE;
		}

		// Get currency rate for historically accurate invoicing
		if ( ! empty($input['currency']))
		{
			$currency = $this->db
				->select('id, rate')
				->where('code', $input['currency'])
				->get('currencies')
				->row() OR show_error('This currency does not exist.');
		}

		return parent::insert(array(
			'client_id'		=> $input['client_id'],
			'name'			=> $input['name'],
			'due_date'		=> isset($input['due_date']) ? read_date_picker($input['due_date']) : 0,
			'rate'			=> isset($input['rate']) ? $input['rate'] : 0,
			'description'	=> isset($input['description']) ? $input['description'] : '',
			'currency_id'   => ! empty($currency) ? $currency->id : 0,
			'exchange_rate' => ! empty($currency) ? $currency->rate : 0,
			'date_entered'	=> time(),
			'date_updated'	=> 0,
			'completed'		=> 0,
            'unique_id' => $this->_generate_unique_id()
		));
	}
        
        /**
         * Generates the unique id for a partial payment
         * 
         * @access	public
         * @return	string
         */
        public function _generate_unique_id() {
            $this->load->helper('string');

            $valid = FALSE;
            while ($valid === FALSE) {
                $unique_id = random_string('alnum', 8);
                $results = $this->db->where('unique_id', $unique_id)->get($this->projects_table)->result();
                if (empty($results)) {
                    $valid = TRUE;
                }
            }

            return $unique_id;
        }

	// --------------------------------------------------------------------

	/**
	 * Updates a project
	 *
	 * @access	public
	 * @param	array 	The project array
	 * @return	int
	 */
	public function update($project)
	{
		if ( ! isset($project['id']))
		{
			return FALSE;
		}
		$this->db->where('id', $project['id']);
		unset($project['id']);

		$project['due_date'] = read_date_picker($project['due_date']);
		$project['date_updated'] = time();

		return $this->db->update($this->projects_table, $project);
	}

	// --------------------------------------------------------------------

	/**
	 * Deletes a project by its ID
	 *
	 * @access	public
	 * @param	int		The project id
	 * @return	object	The result object
	 */
	public function delete_project($project_id) {
            // delete all tasks and the project

            $this->db->where('project_id', $row['id'])->delete($this->tasks_table);
            $this->db->where('project_id', $row['id'])->delete($this->time_table);
            return $this->db->where('id', $project_id)->delete($this->projects_table);
        }
        
        public function delete_by_client($client_id) {
            $buffer = $this->db->select('id')->where('client_id', $client_id)->get($this->projects_table)->result_array();
            foreach ($buffer as $row) {
                $this->db->where('project_id', $row['id'])->delete($this->tasks_table);
                $this->db->where('project_id', $row['id'])->delete($this->time_table);
            }
            return $this->db->where('client_id', $client_id)->delete($this->projects_table);
        }
}

/* End of file: project_m.php */
