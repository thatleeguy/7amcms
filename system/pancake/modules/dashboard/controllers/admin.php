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
 * The admin controller for the dashboard
 *
 * @subpackage	Controllers
 * @category	Dashboard
 */
class Admin extends Admin_Controller
{
	/**
	 * Outputs a nice dashboard for the user
	 *
	 * @access	public
	 * @return	void
	 */
	public function index()
	{
		$this->load->model('invoices/invoice_m');
        $this->load->model('proposals/proposals_m');
		$this->load->model('projects/project_task_m');
		$this->load->helper('array');
                
        $invoices    = $this->invoice_m->past_30_days();
        $proposals   = $this->proposals_m->past_30_days();
        $thirty_days = array();
        
        foreach ($invoices as $invoice)
		{
            $thirty_days[$invoice->date_entered] = $invoice;
        }
        
        foreach ($proposals as $proposal)
		{
            $proposal->proposal = true;
            $thirty_days[$proposal->created] = $proposal;
        }
        
        krsort($thirty_days);
        $buffer = $thirty_days;
        $thirty_days = array();
        $i = 0;
        foreach ($buffer as $row)
		{
            $thirty_days[] = $row;
            $i++;
            
            if ($i == 10) {break;}
        }

		$this->template->thirty_days = $thirty_days;
		$this->template->paid = $this->invoice_m->paid_totals();
		$this->template->unpaid = $this->invoice_m->unpaid_totals();
		$this->template->overdue = $this->invoice_m->overdue_totals();
		$this->template->tasks = $this->project_task_m->get_upcoming_tasks(5);
		$this->template->build('dashboard');
	}

}

/* End of file: admin.php */