<?php

defined('BASEPATH') OR exit('No direct script access allowed');
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
 * The Proposals Model
 *
 * @subpackage	Models
 * @category	Proposals
 */
class Proposals_m extends Pancake_Model {

    public $table = 'proposals';
    public $sections_table = 'proposal_sections';
    public $invoices_table = 'invoices';
    public $primary_key = 'unique_id';
    public $get_estimates = true; # Overriden when sending proposal emails to avoid template problems. Trust me, it's easier this way. - Bruno.

    function create($data) {
        $unique_id = $this->_generate_unique_id();
        $CI = &get_instance();
        $CI->load->model('clients/clients_m');
        $client = $CI->clients_m->get($data['client_id']);
        $data['client_name'] = $client->first_name . ' ' . $client->last_name;
        $data['client_address'] = $client->address;
        $data['client_company'] = $client->company;
        $data['proposal_number'] = $this->_generate_proposal_number($data['proposal_number']);
        $this->db->insert($this->table, array('unique_id' => $unique_id, 'created' => time()));
        $id = $this->db->insert_id();
        if ($this->edit($id, $data)) {
            return $unique_id;
        } else {
            return false;
        }
    }

    function accept($unique_id) {
	$CI = &get_instance();
        $CI->load->model('invoices/invoice_m');
	$CI->invoice_m->convertProposalEstimatesIntoInvoices($this->getIdByUniqueId($unique_id));
        return $this->db->where('unique_id', $unique_id)->update($this->table, array('status' => 'ACCEPTED', 'last_status_change' => time()));
    }

    function reject($unique_id) {
	$CI = &get_instance();
        $CI->load->model('invoices/invoice_m');
	$CI->invoice_m->convertProposalInvoicesIntoEstimates($this->getIdByUniqueId($unique_id));
        return $this->db->where('unique_id', $unique_id)->update($this->table, array('status' => 'REJECTED', 'last_status_change' => time()));
    }

    function unanswer($unique_id) {
	$CI = &get_instance();
        $CI->load->model('invoices/invoice_m');
	$CI->invoice_m->convertProposalInvoicesIntoEstimates($this->getIdByUniqueId($unique_id));
        return $this->db->where('unique_id', $unique_id)->update($this->table, array('status' => '', 'last_status_change' => time()));
    }

    function edit($id, $data) {

        if (!isset($data['sections']) or (isset($data['sections']) and !is_array($data['sections'])) or (isset($data['sections']) and empty($data['sections']))) {
            $data['sections'][1] = array(
                'page_key' => 1
            );
        }

        $sections = $data['sections'];
        unset($data['sections']);

        if ($this->db->where('id', $id)->update($this->table, $data)) {

            $this->deleteSections($id);
            $this->db->where('proposal_id', $id)->update($this->invoices_table, array('proposal_id' => 0));

            foreach ($sections as $key => $section) {
                $this->setSection($id, (!empty($section['page_key']) ? $section['page_key'] : $page_key), (!empty($section['key']) ? $section['key'] : $key), $section);
            }

            return true;
        } else {
            return false;
        }
    }
    
    function sendNotificationEmail($unique_id, $message = NULL, $subject = null)
	{        
     	if ($message == NULL) {
		    # Use default message if none was provided.
		    $message = PAN::setting('email_new_proposal');
		}
		
		if ($subject == null) {
		    $subject = 'Proposal #' . $proposal->proposal_number . ' - '. $proposal->title;
		}

		$proposal = $this->get_by('unique_id', $unique_id);
		$proposal->url = site_url('proposal/'.$unique_id);
		
		$this->load->model('clients/clients_m');
		$client = $this->clients_m->get($proposal->client_id);

		$this->load->library('simpletags');
		$result = $this->simpletags->parse(nl2br($message), array(
			'proposal' => $proposal,
			'settings' => $this->settings->get_all(),
		));
		
		$email_body = Email_Template::build('new_proposal', $result['content']);

		$to = $client->email;
		$message = $email_body;
		
		$result = send_pancake_email_raw($to, $subject, $message);

		if ($result) {
		    $this->update($proposal->id, array('last_sent' => time()));
		    return true;
		} else {
		    return false;
		}
    }

    function count($where = array())
	{
        $this->db->where($where);
        return $this->db->count_all_results($this->table);
    }

    function getIdByUniqueId($unique_id) {
        $buffer = $this->db->select('id')->where('unique_id', $unique_id)->get($this->table)->row_array();
        return $buffer['id'];
    }

    function getByUniqueId($unique_id) {
        $result = $this->getAll(null, null, array('unique_id' => $unique_id));
        foreach ($result as $row) {
            $row = (array) $row;
            if (!$row['client']) {
                $row['client'] = array(
                    'first_name' => '',
                    'last_name' => '',
                    'company' => '',
                    'address' => '',
                    'id' => 0
                );
            }

            $row['client']->company = $row['client_company'];
            $row['client']->address = $row['client_address'];
            $row['client']->name = $row['client_name'];

            $row['pages'] = $this->getProposalPages($row['id']);
            return $row;
        }
    }

    function getProposalPages($proposal_id) {
        $sections = $this->db->where('proposal_id', $proposal_id)->order_by('page_key', 'asc')->order_by('key', 'asc')->get($this->sections_table)->result_array();
        $pages = array();
        foreach ($sections as $section) {
            
            $section['estimate_id'] = ($section['section_type'] == 'estimate') ? $section['contents'] : '';
            if ($section['section_type'] == 'estimate' and $this->get_estimates) {
                $invoice = $this->invoice_m->flexible_get_all(array('id' => $section['contents'], 'include_totals' => true, 'get_single' => true, 'return_object' => false, 'type' => 'both'));
                $this->template->is_paid = $this->invoice_m->is_paid($invoice['unique_id']);
                $this->template->files = (array) $this->files_m->get_by_unique_id($invoice['unique_id']);
                $this->template->invoice = (array) $invoice;
                $this->template->is_overdue = (bool) ($invoice > 0 AND $invoice['due_date'] < time());
                $this->template->is_estimate = true;
                $this->template->set_layout(false);
                $section['contents'] = $this->template->build('detailed', array(), true);
                $this->template->set_layout(true);
            }
            
            if ($section['parent_id'] == 0) {
                $pages[$section['page_key']]['sections'][$section['key']] = $section;
            } else {
                $pages[$section['page_key']]['sections'][$section['parent_id']]['sections'] = $section;
            }
        }
        return $pages;
    }

    function getAll($per_page = null, $offset = null, $where = array()) {
        if ($per_page !== null) {
            $this->db->limit($per_page, $offset);
        }
        
        $CI = &get_instance();
        $CI->load->model('clients/clients_m');
        $CI->load->model('invoices/invoice_m');
        
        $result = $this->db->order_by('created', 'desc')->where($where)->get($this->table)->result();
        $return = array();
        foreach ($result as $key => $row) {
            $row->amount = $CI->invoice_m->getProposalAmount($row->id);
            $row->client = $CI->clients_m->get($row->client_id);
            $return[$key] = $row;
        }
        return $return;
    }
    
    function delete_by_client($client_id) {
        $buffer = $this->db->select('unique_id')->where('client_id', $client_id)->get($this->table)->result_array();
        foreach ($buffer as $row) {
            $this->delete($row['unique_id']);
        }
        return true;
    }

    function delete($unique_id) {
        $id = $this->getIdByUniqueId($unique_id);
        if ($id) {
            if ($this->db->where('id', $id)->delete($this->table)) {
                if ($this->deleteSections($id)) {
                    return true;
                }
            }
            return false;
        }
        return true;
    }

    function recordView($id) {
        return $this->db->where('id', $id)->update($this->table, array('last_viewed' => time()));
    }
    
    function deleteEstimateSections($estimate_id) {
        return $this->db->where('section_type', 'estimate')->where('contents', $estimate_id)->delete($this->sections_table);
    }

    function deleteSections($proposal_id) {
        return $this->db->where('proposal_id', $proposal_id)->delete($this->sections_table);
    }
    
    function createPremadeSection($title, $subtitle, $contents) {
        return $this->db->insert($this->sections_table, array(
            'title' => $title,
            'proposal_id' => 0,
            'subtitle' => $subtitle,
            'contents' => $contents
        ));
    }

    function setSection($proposal_id, $page_key, $key, $data) {
        $id = $this->db->select('id')->where('proposal_id', $proposal_id)->where('page_key', $page_key)->where('key', $key)->get($this->sections_table)->row_array();
        $id = isset($id['id']) ? $id['id'] : false;
        $data['key'] = $key;
        $data['page_key'] = $page_key;
        $data['proposal_id'] = $proposal_id;
        $data['section_type'] = !empty($data['section_type']) ? $data['section_type'] : 'section';
        
        if ($data['section_type'] == 'estimate') {
            $this->db->where('id', $data['contents'])->update($this->invoices_table, array('proposal_id' => $proposal_id));
        }
        
        if ($id) {
            return $this->db->where('id', $id)->update($this->sections_table, $data);
        } else {
            return $this->db->insert($this->sections_table, $data);
        }
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
            $results = $this->db->where('unique_id', $unique_id)->get($this->table)->result();
            if (empty($results)) {
                $valid = TRUE;
            }
        }

        return $unique_id;
    }

    /**
     * Generates a proposal number
     *
     * @access	private
     * @return	string
     */
    public function _generate_proposal_number($number = null) {
        $this->load->helper('string');

        if (!empty($number)) {
            if ($this->db->where('proposal_number', $number)->count_all_results($this->table) == 0) {
               return $number;
            }
        }

        $valid = FALSE;
        $result = $this->db->limit(1)->select('proposal_number')->order_by('proposal_number', 'desc')->get($this->table)->row_array();
        $invoice_number = isset($result['proposal_number']) ? $result['proposal_number'] : 0;
        $invoice_number = $invoice_number + 1;
        while ($valid === FALSE) {
            if ($this->db->where('proposal_number', $invoice_number)->count_all_results($this->table) == 0) {
                $valid = TRUE;
            } else {
                $invoice_number++;
            }
        }
        
        return $invoice_number;
    }

    function past_30_days() {
        return $this->getAll(null, null, array('created >' => strtotime('-30 days')));
    }

}