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
 * The Clients Model
 *
 * @subpackage	Models
 * @category	Clients
 */
class Clients_m extends Breakfast_Model
{
	/**
	 * @var	string	The name of the clients table
	 */
	protected $table = 'clients';

	protected $validate = array(
		array(
			'field'	  => 'first_name',
			'label'	  => 'First Name',
			'rules'	  => 'required'
		),
		array(
			'field'	  => 'last_name',
			'label'	  => 'Last Name',
			'rules'	  => 'required'
		),
		array(
			'field'	  => 'email',
			'label'	  => 'Email',
			'rules'	  => 'required|valid_email'
		),
	);

	public function build_client_dropdown()
	{
		$this->load->model('clients/clients_m');
		$dropdown_array = array();
		$clients = $this->order_by('first_name')->get_all();
		foreach ($clients as $client)
		{
			$dropdown_array += array($client->id => $client->first_name.' '.$client->last_name.($client->company ? ' - '.$client->company : ''));
		}

		return $dropdown_array;
	}
        
        function health($id) {
            $CI = &get_instance();
            $CI->load->model('invoices/partial_payments_m', 'ppm');
            return $CI->ppm->getClientHealth($id);
        }
        
        function delete($id) {
            $CI = &get_instance();
            $CI->load->model('invoices/invoice_m');
            $CI->load->model('projects/project_m');
            $CI->load->model('proposals/proposals_m');
            $CI->invoice_m->delete_by_client_id($id);
            $CI->project_m->delete_by_client($id);
            $CI->proposals_m->delete_by_client($id);
            return $this->db->where($this->primary_key, $id)->delete($this->table);
        }
}

/* End of file: settings_m.php */