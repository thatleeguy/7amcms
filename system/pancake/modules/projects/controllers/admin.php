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
 * The admin controller for projects
 *
 * @subpackage	Controllers
 * @category	Projects
 */
class Admin extends Admin_Controller
{

	/**
	 * Load in the dependencies
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('project_m', 'project_task_m', 'project_time_m'));
		$this->lang->load('projects', Settings::get('language'));
	}

	// --------------------------------------------------------------------

	/**
	 * Lists all the projects
	 *
	 * @access	public
	 * @param	int		The offset to start at
	 * @return	void
	 */
	public function index($offset = 0)
	{		
		$data = array('projects' => $this->project_m->get_projects($this->pagination_config['per_page'], $offset));
                
        // Start up the pagination 
        $this->load->library('pagination');
        $this->pagination_config['base_url'] = site_url('admin/projects/index/');
        $this->pagination_config['uri_segment'] = 4;
        $this->pagination_config['total_rows'] = $this->project_m->count_all();
        $this->pagination->initialize($this->pagination_config);

		if (IS_AJAX)
		{
			$this->load->view('index', $data);
		}
		
		else
		{
			$this->template->build('index', $data);
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * View details of a project
	 *
	 * @access	public
	 * @return	void
	 */
    public function view($project_id, $offset = 0)
    {
		$this->load->helper('typography');
		
        if ( ! ($project = $this->project_m->get_project_by_id($project_id)->row()))
        {
            $this->session->set_flashdata('error', 'Invalid Project!');
            redirect('admin/projects');
        }

        // Start up the pagination 
        $this->load->library('pagination');
        $this->pagination_config['base_url'] = site_url('admin/projects/view/'.$project_id);
        $this->pagination_config['uri_segment'] = 5;
        $this->pagination_config['total_rows'] = $this->project_task_m->count_all_tasks($project_id);
        $this->pagination->initialize($this->pagination_config);
        
        $this->template->build('view', array(
			'project' => $project,
			'tasks' => $this->project_task_m->get_tasks_and_times_by_project($project_id, $this->pagination_config['per_page'], $offset),
		));
        
    }

	// --------------------------------------------------------------------

	/**
	 * Creates a new project
	 *
	 * @access	public
	 * @return	void
	 */
	public function create()
	{
		IS_AJAX OR access_denied();

		if ($_POST)
		{
			// All form validation is handled in the model, so lets just throw it the data
			if ($result = $this->project_m->insert($_POST))
			{
				$message = array('success' => $this->lang->line('projects.create.succeeded'));
			}
			else
			{
				if ($errors = validation_errors('<p>', '</p>'))
				{
					$message = array('error' => $errors);
				}
				else
				{
					$message = array('error' => $this->lang->line('projects.create.failed'));
				}
			
			}

			output_json($message);
		}

		$base_currency = Currency::get();
		$currencies = array('[Default] '.$base_currency['name']);
		foreach (Settings::all_currencies() as $currency)
		{
			$currencies[$currency['code']] = $currency['name'];
		}

		$this->load->model('clients/clients_m');
		
		$this->load->view('form', array(
			'clients_dropdown' => $this->clients_m->build_client_dropdown(),
			'action' => 'create',
			'currencies' => $currencies
		));
	}
	
	// --------------------------------------------------------------------

	/**
	 * Edit a new project
	 *
	 * @access	public
	 * @return	void
	 */
	public function edit($project_id = null)
	{
		IS_AJAX || access_denied();
		
		$project = $this->project_m->get_project_by_id($project_id);
		
		if ($_POST)
		{
			// All form validation is handled in the model, so lets just throw it the data
			if ($result = $this->project_m->update($_POST))
			{
				$message = array('success' => $this->lang->line('projects.update.succeeded'));
			}
			else
			{
				if ($errors = validation_errors('<p>', '</p>'))
				{
					$message = array('error' => $errors);
				}
				else
				{
					$message = array('error' => $this->lang->line('projects.update.failed'));
				}
			
			}

			output_json($message);
		}
		else
		{
			foreach ((array) $project as $key => $val)
			{
				$_POST[$key] = $val;
			}
		}

		$this->load->model('clients/clients_m');

		$this->load->view('form', array(
			'clients_dropdown' => $this->clients_m->build_client_dropdown(),
			'action' => 'edit',
			'project' => $project->row()
		));
		
	}
	
	public function delete($project_id = null)
	{
		IS_AJAX || access_denied();
		
		if ($_POST)
		{
            $this->project_m->delete_project($this->input->post('id'));
            exit(json_encode(array('success' => 'true')));
		}
		
		$project = $this->project_m->get_project_by_id($project_id);
		
		$data = array(
            'project' => $project->row()
		);
		
		echo $this->load->view('delete', $data, true);
	}
	
}

/* End of file admin.php */