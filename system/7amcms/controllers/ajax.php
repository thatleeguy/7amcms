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
 * The javascript controller
 *
 * @subpackage	Controllers
 * @category	Javascript
 */
class Ajax extends Breakfast_Controller {
	
	public function convert_currency($to, $amount = 1)
	{
		$from = Currency::code();

		$url = sprintf('http://www.google.com/ig/calculator?hl=en&q=%d%s=?%s', $amount, $from, $to);

		// I'd love to use json_decode() but Google has provided invalid JSON. Phil
		$url = get_url_contents($url, false);
		if (!empty($url)) {
		    echo (float) current(explode(' ', end(explode('rhs: "', $url))));
		}
	}

	function get_payment_details($invoice_unique_id, $key) {
	    if (is_admin()) {
	        require_once APPPATH.'modules/gateways/gateway.php';
	        $this->load->model('invoices/invoice_m');
	        $this->load->model('invoices/partial_payments_m', 'ppm');
	        $part = $this->ppm->getPartialPaymentDetails($key, $invoice_unique_id, true);
		$part['key'] = $key;
	        $this->load->view('invoices/partial_payment_details', $part);
	    }
	}

	function refresh_tracked_hours($task_id) {
	    $this->load->model('projects/project_m');
	    $this->load->model('projects/project_task_m');
	    $this->load->model('projects/project_time_m');
	    print $this->project_task_m->get_processed_task_hours($task_id);
	    die;
	}
	
	function upgraded($from, $to) {
	    if (is_admin()) {
		$this->load->model('upgrade/update_system_m', 'update');
		$this->load->view('upgrade/upgraded', array('from' => $from, 'to' => $to, 'changelog' => $this->update->get_processed_changelog($to, $from)));
	    }
	}
	
	function outdated($to) {
	    if (is_admin()) {
		$this->load->model('upgrade/update_system_m', 'update');
		$from = Settings::get('version');
		$this->load->view('upgrade/outdated', array('from' => $from, 'to' => $to, 'changelog' => $this->update->get_processed_changelog($to, $from)));
	    }
	}
	
	function hide_notification($notification_id) {
	    if (is_admin()) {
		hide_notification($notification_id);
	    }
	}

	function set_payment_details($invoice_unique_id, $key) {
	    if (is_admin()) {
	        require_once APPPATH.'modules/gateways/gateway.php';
	        $this->load->model('invoices/invoice_m');
	        $this->load->model('invoices/partial_payments_m', 'ppm');
	        $this->ppm->setPartialPaymentDetails($invoice_unique_id, $key, $_POST['date'], $_POST['gateway'], $_POST['status'], $_POST['tid'], $_POST['fee']);
	    }
	    exit('WORKED');
	}

	function save_proposal($unique_id) {
	    $this->load->model('proposals/proposals_m');
	    if (is_admin()) {
	        $id = $this->proposals_m->getIdByUniqueId($unique_id);
	        echo $this->proposals_m->edit($id, $_POST) ? 'WORKED' : 'UHOH';
	    } else {
	        print "WORKED";
	        die;
	    }
	}

	function save_premade_section() {
	    $this->load->model('proposals/proposals_m');
	    if (is_admin()) {
	        if ($_POST['title'] != '' and $_POST['contents'] != '') {
	            $this->proposals_m->createPremadeSection($_POST['title'], $_POST['subtitle'], $_POST['contents']);
	        }
	    }
	    exit('WORKED');
	}

	function get_estimates($client_id) {
	    if (is_admin()) {
	        $this->load->model('invoices/invoice_m');
	        $this->load->view('proposals/get_estimates', array('client_id' => $client_id, 'estimates' => $this->invoice_m->getEstimatesForDropdown()));
	    }
	}

	function set_proposal($unique_id, $status = null) {
	    $this->load->model('proposals/proposals_m');
    
	    if ($status == 'accept') {
	        $this->proposals_m->accept($unique_id);
	        print "ACCEPTED";
	    } elseif ($status == 'reject') {
	        $this->proposals_m->reject($unique_id);
	        print "REJECTED";
	    } else {
	        $this->proposals_m->unanswer($unique_id);
	        print "UNANSWERED";
	    }
	    die;
	}
}

/* End of file cron.php */