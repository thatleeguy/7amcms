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
 * The admin controller for Clients
 *
 * @subpackage	Controllers
 * @category	Clients
 */
class Admin extends Admin_Controller
{
	/**
	 * The construct doesn't do anything useful right now.
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->load->model('item_m');
	}

	// ------------------------------------------------------------------------

	/**
	 * Loads all the items in and sends then to be outputted
	 *
	 * @access	public
	 * @return	void
	 */
	public function index($offset = 0)
	{
        $count = $this->item_m->count_all();
		$items = $this->item_m->order_by('name')->limit($this->pagination_config['per_page'], $offset)->get_all();
        
        // Start up the pagination 
		$this->load->library('pagination');
        $this->pagination_config['base_url'] = site_url('admin/items/index/');
		$this->pagination_config['uri_segment'] = 4;
		$this->pagination_config['total_rows'] = $count;
		$this->pagination->initialize($this->pagination_config);

		$this->template->items = $items;

		$this->template->build('list');
	}

	// ------------------------------------------------------------------------

	/**
	 * Creates a item
	 *
	 * @access	public
	 * @return	void
	 */
	public function create()
	{
		if ($_POST)
		{
			$result = $this->item_m->insert(array(
				'name' => $this->input->post('name'),
				'description' => $this->input->post('description'),
				'rate'=> $this->input->post('rate'), 
				'qty' => $this->input->post('qty'), 
				'tax_id' => $this->input->post('tax_id'),
			));
			
			if ($result)
			{
				$this->session->set_flashdata('success', lang('items:added'));
				redirect('admin/items');
			}
			else
			{
				$this->template->error = validation_errors();
			}
		}
		
		$this->template->action_type = 'add';
		$this->template->action = 'create';
		$this->template->build('form');
	}

	// ------------------------------------------------------------------------

	/**
	 * Edits a item
	 *
	 * @access	public
	 * @return	void
	 */
	public function edit($item_id)
	{
		$this->load->model('item_m');

		$item = $this->item_m->get($item_id);
		if (empty($item))
		{
			$this->session->set_flashdata('error', lang('items:does_not_exist'));
			redirect('admin/items');
		}

		if ($_POST)
		{
			$result = $this->item_m->update($item_id, array(
				'name' => $this->input->post('name'),
				'description' => $this->input->post('description'),
				'rate'=> $this->input->post('rate'), 
				'qty' => $this->input->post('qty'), 
				'tax_id' => $this->input->post('tax_id'),
			));
			
			if ($result)
			{
				$this->session->set_flashdata('success', lang('items:edited'));
				redirect('admin/items');
			}
			else
			{
				$this->template->error = validation_errors();
			}
		}
		else
		{
			foreach ((array) $item as $key => $val)
			{
				$_POST[$key] = $val;
			}
		}
		$this->template->action_type = 'edit';
        $this->template->item_id = $item_id;
		$this->template->action = 'edit/'.$item_id;
		$this->template->build('form');
	}

	// ------------------------------------------------------------------------

	/**
	 * Edits a item
	 *
	 * @access	public
	 * @return	void
	 */
	public function delete($item_id)
	{
		$item = $this->item_m->get($item_id);
		
		if ($_POST)
		{
			// Check to make sure the action hash matches, if not kick em' to the curb
			if ($this->input->post('action_hash') !== $this->session->userdata('action_hash'))
			{
				$this->session->set_flashdata('error', lang('global:insecure_action'));
				redirect('admin/dashboard');
			}
			
            # This deletes all invoices, projects and proposals related to the item.
			$this->item_m->delete($item_id);
			$this->session->set_flashdata('success', lang('items:deleted'));
			redirect('admin/items');
		}

		// We set a unique action hash here to stop CSRF attacks (hacker's beware)
		$action_hash = md5(time().$item_id);
		$this->session->set_userdata('action_hash', $action_hash);

		// Lets make sure before we just go killing stuff like Rambo
		$this->template->build('are_you_sure', array(
			'item' => $item,
			'action_hash' => $action_hash,
		));
	}


	// ------------------------------------------------------------------------

	/**
	 * Shows the items info
	 *
	 * @access	public
	 * @param	string	The item id
	 * @return	void
	 */
	public function view($item_id)
	{
		if ( ! $item = $this->item_m->get($item_id))
		{
			$this->session->set_flashdata('error', lang('items:does_not_exist'));
			redirect('admin/items');
		}
		
		$this->template->build('view', array(
			'item' => $item,
		));
	}

	// ------------------------------------------------------------------------

	/**
	 * Return all items with partial matches
	 *
	 * @access	public
	 * @param	string	The item id
	 * @return	void
	 */
	public function ajax_auto_complete()
	{
		$name = $this->input->get('term');
		
		$items = $this->db
			->select('*, name as label')
			->where('name', $name)
			->or_like('name', $name)
			->or_like('name', $name, 'after')
			->get('items')
			->result();
		
		echo json_encode($items);
	}
}

/* End of file: admin.php */