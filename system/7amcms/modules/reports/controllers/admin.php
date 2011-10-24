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
 * The admin controller for reports
 *
 * @subpackage	Controllers
 * @category	Reports
 */
class Admin extends Admin_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('reports/reports_m');
        $this->load->model('clients/clients_m');
    }

    function index() {
        redirect('admin/reports/all');
        return;
    }

    function all($string = 'from:0-to:0-client:0') {
        
        $string = $this->reports_m->processReportString($string);
        
        $data = array('reports' => $this->reports_m->getOverviews($string['from'], $string['to'], $string['client']));
        $this->template->clients_dropdown = array_merge(array(0 => __('reports:allclients')), $this->clients_m->build_client_dropdown());
        $this->template->from = $this->reports_m->getDefaultFrom($string['from']);
        $this->template->to = $this->reports_m->getDefaultTo($string['to']);
        $this->template->client_id = $string['client'];
        $this->template->from_input = $this->template->from > 0 ? format_date($this->template->from) : '';
        $this->template->to_input = $this->template->to > 0 ? format_date($this->template->to) : '';
        $this->template->build('all', $data);
    }

}