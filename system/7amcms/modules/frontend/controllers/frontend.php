<?php

defined('BASEPATH') OR exit('No direct script access allowed');
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
 * The frontend controller
 *
 * @subpackage	Controllers
 * @category	Frontend
 */
class Frontend extends Public_Controller {

    /**
     * Routes the request, shows the invoice or payment request,
     * or redirects to the admin if the URI is not found.
     *
     * @access	public
     * @param	string	The method name from the URI
     * @return	void
     */
    public function _remap($method)
	{
        if (is_callable(array($this, $method)))
		{
            return call_user_func(array($this, $method));
        }

        require_once APPPATH . 'modules/gateways/gateway.php';
        $this->load->helper('typography');
        $this->load->model('invoices/invoice_m');
        $this->load->model('files/files_m');

        $invoice = $this->invoice_m->get($method);

        if (!empty($invoice))
		{
	    
	    if (!is_admin()) {
		$this->invoice_m->recordView($invoice['unique_id']);
	    }
	    
            $this->template->pdf_mode = FALSE;
            $this->template->is_paid = $this->invoice_m->is_paid($method);
            $this->template->files = (array) $this->files_m->get_by_unique_id($method);
            $this->template->invoice = (array) $invoice;
            $this->template->is_overdue = (bool) ($invoice > 0 AND $invoice['due_date'] < time());

            $this->template->set_layout(strtolower($this->template->invoice['type']));
            $this->template->build(strtolower($this->template->invoice['type']));
        }
		else
		{
		    if (is_admin() or PANCAKE_DEMO) {
			redirect('admin');
		    } else {
			$this->template->build('default');
		    }
        }
    }
    
    function get_processed_estimate()
	{    
        $this->load->helper('typography');
        
        $this->load->model('invoices/invoice_m');
        $this->load->model('files/files_m');
        $estimate_id = $this->uri->segment(2);
        
        if (is_admin())
		{
            $invoice = $this->invoice_m->flexible_get_all(array('id' => $estimate_id, 'include_totals' => true, 'get_single' => true, 'return_object' => false, 'type' => 'estimate'));
            $this->template->is_paid = $this->invoice_m->is_paid($invoice['unique_id']);
            $this->template->files = (array) $this->files_m->get_by_unique_id($invoice['unique_id']);
            $this->template->invoice = (array) $invoice;
            $this->template->is_overdue = (bool) ($invoice > 0 AND $invoice['due_date'] < time());
            $this->template->is_estimate = true;
            $this->template->_layout = false;
            $this->template->build('detailed');
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Renders the invoice or payment request into a PDF and forces the
     * download.
     *
     * @access	public
     * @return	void
     */
    public function pdf() {
        
        require_once APPPATH . 'modules/gateways/gateway.php';
        $this->load->helper('typography');
        $this->load->model('invoices/invoice_m');
        $this->load->model('files/files_m');
        
        $unique_id = $this->uri->segment(2);

        if (empty($unique_id)) {
            redirect('/', 'location', 301);
        }

        $this->load->model('invoices/invoice_m');
        $this->load->model('files/files_m');

        $invoice = $this->invoice_m->get($unique_id);

        $this->template->pdf_mode = TRUE;
        $this->template->is_paid = $this->invoice_m->is_paid($unique_id);
        $this->template->files = (array) $this->files_m->get_by_unique_id($unique_id);
        $this->template->invoice = (array) $invoice;
        $this->template->set_layout(strtolower($invoice['type']));
        $html = $this->template->build(strtolower($invoice['type']), array(), TRUE);
        #die($html);
        include_once APPPATH . 'libraries/dompdf/dompdf_config.inc.php';
        $dompdf = new DOMPDF();
        $dompdf->set_paper("A4");
        $dompdf->load_html($html);
        $dompdf->render();
        $dompdf->stream(preg_replace('/[^A-Za-z0-9-]/', '', str_ireplace(' ', '-',strtolower(Settings::get('site_name'))))."-invoice-{$invoice['invoice_number']}.pdf", array('Attachment' => '0'));
    }

    public function timesheet()
	{
        $this->load->model(array('projects/project_m', 'projects/project_task_m', 'projects/project_time_m'));
        $unique_id = $this->uri->segment(2);
        $project = $this->project_m->getForTimesheet($unique_id);

		$project or redirect('/');

        $this->template->pdf_mode = true;
        $this->template->total_hours = $project['total_hours'];
        $this->template->tasks = $project['tasks'];
        $this->template->count_users = $project['user_count'];
        $this->template->project = $project['name'];
        $this->template->client = $project['client'];
        $this->template->project_due_date = $project['due_date'];

        $this->template->set_layout('timesheet');
        $html = $this->template->build('timesheet', array(), TRUE);
        include_once APPPATH . 'libraries/dompdf/dompdf_config.inc.php';
        $dompdf = new DOMPDF();
        $dompdf->set_paper("A4");
        $dompdf->load_html($html);
        $dompdf->render();
        $dompdf->stream("timesheet.pdf", array('Attachment' => '0'));
    }
    
    public function reports()
	{
        $this->load->model('reports/reports_m');
        
        $report = $this->uri->segment(2);
        $pdf = $this->uri->segment(3);
        $string = $this->uri->segment(4);
        $string = $this->reports_m->processReportString($string);
        
        $pdf = ($pdf == 'pdf');

        $this->template->pdf_mode = $pdf;
        $this->template->set_layout('report');
        if (!$pdf) {
            $this->template->build('report', $this->reports_m->get($report, $string['from'], $string['to'], $string['client']));
        } else {
            $html = $this->template->build('report', $this->reports_m->get($report), true);
            include_once APPPATH . 'libraries/dompdf/dompdf_config.inc.php';
            $dompdf = new DOMPDF();
            $dompdf->set_paper("A4");
            $dompdf->load_html($html);
            $dompdf->render();
            $dompdf->stream("report-{$report}.pdf", array('Attachment' => '0'));
        }
    }
    
    function download_version() {
	
	$version = $this->uri->segment(2);
	
	$this->load->model('upgrade/update_system_m', 'update');
	if ($this->update->download_version($version)) {
	    print 'Downloaded '.$version.' successfully.';
	} else {
	    print "Version $version does not exist.";
	}
	die;
    }
    
    function check_latest_version() {
	if (PANCAKE_DEMO) {
	    Settings::set('auto_update', 1);
	}
	$this->load->model('upgrade/update_system_m', 'update');
	$this->update->get_latest_version(true);
	redirect('admin');
    }

    public function proposal()
	{
        $this->load->helper('typography');
        
        $pdf = ($this->uri->segment(3) == 'pdf');
        
        $this->load->model('proposals/proposals_m');
        $this->load->model('invoices/invoice_m');
        $this->load->model('files/files_m');
        $this->load->model('clients/clients_m');

        $unique_id = $this->uri->segment(2);

        $proposal = (array) $this->proposals_m->getByUniqueId($unique_id);

		$proposal or redirect('/');

        $proposal['client'] = (array) $proposal['client'];

        if ( ! is_admin())
		{
            $this->proposals_m->recordView($proposal['id']);
        }

        $this->template->new = (bool) $proposal;
        $result = $this->db->get('clients')->result_array();
        $clients = array();
        foreach ($result as $row)
		{
            $row['title'] = $row['first_name'].' '.$row['last_name'].($row['company'] ? ' - '.$row['company'] : '');
            $clients[] = $row;
        }
        $this->template->clients = $clients;

        $this->template->proposal = $proposal;
        $this->template->pdf_mode = $pdf;
        $this->template->set_layout('proposal');
        
		if (!$pdf) {
            $this->template->build('proposal');
        } else {
            $html = $this->template->build('proposal', array(), true);
            include_once APPPATH . 'libraries/dompdf/dompdf_config.inc.php';
            $dompdf = new DOMPDF();
            $dompdf->set_paper("A4");
            $dompdf->load_html($html);
            $dompdf->render();
            $dompdf->stream("proposal-$unique_id.pdf", array('Attachment' => '0'));
        }
    }

}

/* End of file frontend.php */