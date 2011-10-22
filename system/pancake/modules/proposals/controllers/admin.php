<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Pancake
 *
 * A simple, fast, self-hosted invoicing application
 *
 * @package		Pancake
 * @author		Pancake Dev Team
 * @copyright	Copyright (c) 2011, Pancake Payments
 * @license		http://pancakeapp.com/license
 * @link		http://pancakeapp.com
 * @since		Version 2.2
 */
// ------------------------------------------------------------------------

/**
 * The admin controller for proposals
 *
 * @subpackage	Controllers
 * @category	Proposals
 */
class Admin extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('proposals/proposals_m');
        $this->load->model('clients/clients_m');
    }

    public function index()
	{
        redirect('admin/proposals/all');
        return;
    }

    public function all($offset = 0)
	{        
        $this->_build_client_filter();
        $client_id = ($this->template->client_id != 0) ? $this->template->client_id : NULL;
        if ($client_id !== NULL) {
            $where = array('client_id' => $client_id);
        } else {
            $where = array();
        }
        
        // Start up the pagination 
        $this->load->library('pagination');
        $this->pagination_config['base_url'] = site_url('admin/proposals/all/');
        $this->pagination_config['uri_segment'] = 4;
        $this->pagination_config['total_rows'] = $this->proposals_m->count($where);
        $this->pagination->initialize($this->pagination_config);
        
        $data = array('proposals' => $this->proposals_m->getAll($this->pagination->per_page, $offset, $where));
        $this->template->clients_dropdown = $this->clients_m->build_client_dropdown();
        $this->template->build('all', $data);
    }
    
    function send($unique_id)
	{   
        if (isset($_POST['message'])) {
            $result = $this->proposals_m->sendNotificationEmail($unique_id, $this->input->post('message'), $this->input->post('subject'));

            if (!$result) {
                $this->session->set_flashdata('error', lang('global:couldnotsendemail'));
		redirect('admin/proposals/send/'.$unique_id);
            } else {
                $this->session->set_flashdata('success', lang('global:emailsent'));
		redirect('admin/proposals/send/'.$unique_id);
            }
        }
        
	$this->proposals_m->get_estimates = false;
        $proposal = $this->proposals_m->getByUniqueId($unique_id);

        if (!isset($proposal['id']) or empty($proposal['id']))
		{
            redirect('admin/proposals/all');
        }
	
        $this->template->proposal = $proposal;
        $this->template->unique_id = $unique_id;

        $this->template->build('send');
    }

    function create() {

        if ($_POST) {
            $unique_id = $this->proposals_m->create(array(
                        'title' => $_POST['title'],
                        'client_id' => $_POST['client_id'],
                        'proposal_number' => $_POST['proposal_number'],
                    ));
            redirect('proposal/' . $unique_id);
            return;
        }
	$this->template->proposal_number = $this->proposals_m->_generate_proposal_number();
        $this->template->clients_dropdown = $this->clients_m->build_client_dropdown();
        $this->template->action = 'create';
        $this->template->build('form');
    }

    function edit($unique_id) {
        redirect('proposal/' . $unique_id);
        return;
    }

    function delete($unique_id) {
        if ($_POST) {
            // Check to make sure the action hash matches, if not kick em' to the curb
            if ($this->input->post('action_hash') !== $this->session->userdata('action_hash')) {
                $this->session->set_flashdata('error', 'Insecure action was attempted but caught');
                redirect('admin/dashboard');
            }
            $this->load->model('proposals_m');

            $this->proposals_m->delete($unique_id);

            // Delete the invoices for the user
            $this->session->set_flashdata('success', 'The proposal has been deleted!');
            redirect('admin/proposals');
        }

        // We set a unique action hash here to stop CSRF attacks (hacker's beware)
        $action_hash = md5(time() . $unique_id);
        $this->session->set_userdata('action_hash', $action_hash);
        $this->template->action_hash = $action_hash;

        // Lets make sure before we just go killing stuff like Rambo
        $this->template->proposal_id = $unique_id;
        $this->template->build('are_you_sure');
    }

    /**
     * Builds the client dropdown array and sets the current client id
     *
     * @access	public
     * @return	void
     */
    private function _build_client_filter() {
        $this->load->model('clients/clients_m');

        $dropdown_array = array('0' => 'All') + $this->clients_m->build_client_dropdown();

        $this->template->clients_dropdown = $dropdown_array;

        if (!$this->session->userdata('client_filter')) {
            $client_id = 0;
        } else {
            $client_id = $this->session->userdata('client_filter');
        }

        $client_id = isset($_POST['client_id']) ? $_POST['client_id'] : $client_id;
        $this->session->set_userdata('client_filter', $client_id);

        $this->template->client_id = $client_id;
    }

}