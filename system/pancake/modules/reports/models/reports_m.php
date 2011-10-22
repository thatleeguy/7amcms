<?php

defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Pancake
 *
 * A simple, fast, self-hosted invoicing application
 *
 * @package		Pancake
 * @author		Pancake Dev Team
 * @copyright           Copyright (c) 2011, Pancake Payments
 * @license		http://pancakeapp.com/license
 * @link		http://pancakeapp.com
 * @since		Version 2.2
 */
// ------------------------------------------------------------------------

/**
 * The Reports Model
 *
 * @subpackage	Models
 * @category	Reports
 */
class Reports_m extends Pancake_Model {

    var $reports = array();

    function __construct() {
        parent::__construct();

        $this->reports['unpaid_invoices'] = 'Unpaid Invoices';
        $this->reports['paid_invoices'] = 'Paid Invoices';
        $this->reports['overdue_invoices'] = 'Overdue Invoices';
        $this->reports['tax_collected'] = 'Tax Collected';
        $this->reports['invoices'] = 'Invoices';
        $this->reports['invoices_per_status'] = 'Invoices';
        $this->reports['invoices_per_status_pie'] = 'Invoices';
    }

    function getOverviews($from = 0, $to = 0, $client_id = NULL) {
        $return = array();
        foreach (array_keys($this->reports) as $report) {
            $return[$report] = $this->load->view('reports/overview', $this->get($report, $from, $to, $client_id), true);
        }
        return $return;
    }

    function generateReportString($from = 0, $to = 0, $client = 0) {
        if ($client == NULL) {
            $client = 0;
        }
        return "from:$from-to:$to-client:$client";
    }

    function processReportString($string) {
        $string = explode('-', $string);
        $data = array(
            'from' => str_ireplace('from:', '', $string[0]),
            'to' => str_ireplace('to:', '', $string[1]),
            'client' => str_ireplace('client:', '', $string[2]),
        );
        return $data;
    }

    function getDefaultFrom($from) {
        $CI = &get_instance();
        $CI->load->model('invoices/invoice_m');
        return ($from > 0 ? $from : $CI->invoice_m->getEarliestInvoiceDate());
    }

    function getDefaultTo($to) {
        return ($to > 0 ? $to : time());
    }

    function _process_due_date($input) {
        return format_date($input);
    }

    function _process_amount($input) {
        return Currency::format($input);
    }

    function _process_money_amount($input) {
        return Currency::format($input);
    }

    function _process_unpaid_amount($input) {
        return Currency::format($input);
    }

    function _process_paid_amount($input) {
        return Currency::format($input);
    }

    function _process_tax_collected($input) {
        return Currency::format($input);
    }

    function get($report, $from = 0, $to = 0, $client_id = NULL) {

        if ($client_id == 0) {
            $client_id = NULL;
        }

        $CI = &get_instance();
        $CI->load->model('invoices/invoice_m');
        $CI->load->model('invoices/partial_payments_m', 'ppm');

        $fields = array('invoice_number' => 'Invoice #', 'client_name' => 'Client Name', 'company' => 'Company', 'due_date' => 'Due Date', 'unpaid_amount' => 'Unpaid Amount', 'paid_amount' => 'Paid Amount', 'amount' => 'Total Amount');
        $configs = array(
            'client_id' => $client_id,
            'from' => $from,
            'to' => $to,
            'include_totals' => true,
            'return_object' => false
        );

        switch ($report) {

            case 'invoices':
                $field_totals = array('amount', 'unpaid_amount', 'paid_amount');
                $records = $CI->invoice_m->flexible_get_all($configs);
                $configs['all'] = true;
                break;

            case 'invoices_per_status':
                $field_totals = array('amount', 'unpaid_amount', 'paid_amount');
                $chart_field = 'formatted_is_paid';
                $per = 'Paid vs. Unpaid over time';
                $chart_type = 'line';
                break;

            case 'invoices_per_client_line':
                $field_totals = array('amount', 'unpaid_amount', 'paid_amount');
                $chart_field = 'client_name';
                $per = 'per client';
                $chart_type = 'line';
                break;

            case 'invoices_per_status_pie':
                $field_totals = array('money_amount');
                $records = $CI->ppm->flexible_get_all($configs);
                $configs['convert_to_invoices'] = true;
                $chart_field = 'formatted_is_paid';
                $per = 'Paid and Unpaid';
                break;

            case 'tax_collected':
                $fields['tax_collected'] = 'Tax Collected';
                $field_totals = array('tax_collected', 'amount', 'unpaid_amount', 'paid_amount');
                $records = $CI->invoice_m->flexible_get_all($configs);
                $configs['tax_collected'] = true;
                break;

            case 'overdue_invoices':
                $field_totals = array('unpaid_amount', 'amount', 'paid_amount');
                $configs['paid'] = false;
                $configs['overdue'] = true;
                break;
            case 'paid_invoices':
                $field_totals = array('paid_amount', 'amount');
                unset($fields['unpaid_amount']);
                unset($fields['paid_amount']);
                $records = $CI->invoice_m->flexible_get_all($configs);
                $configs['paid'] = true;
                break;
            case 'unpaid_invoices':
                $field_totals = array('unpaid_amount', 'amount', 'paid_amount');
                $records = $CI->invoice_m->flexible_get_all($configs);
                $configs['paid'] = false;
                break;
        }

        if (!isset($records)) {
            $records = $CI->invoice_m->flexible_get_all($configs);
        }

        $return_field_totals = array();
        $client_field_totals = array();
        $chart_field = isset($chart_field) ? $chart_field : 'client_name';

        $toBuffer = $this->getDefaultTo($to);
        $fromBuffer = $this->getDefaultFrom($from);
        $time_difference = $toBuffer - $fromBuffer;
        $parts = 10;
        $time_difference_part = $time_difference / ($parts - 1);
        $times = array();
        for ($i = 0; $i <= $parts - 1; $i++) {
            $times[] = (int) round($fromBuffer + ($i * $time_difference_part));
        }

        $time_points = array();

        foreach ($records as &$record) {
            foreach ($field_totals as $total) {
                if (!isset($return_field_totals[$total])) {
                    $return_field_totals[$total] = 0;
                }

                if (!isset($client_field_totals[$total][$record[$chart_field]])) {
                    $client_field_totals[$total][$record[$chart_field]] = 0;
                }

                $return_field_totals[$total] = $return_field_totals[$total] + $record[$total];
                $client_field_totals[$total][$record[$chart_field]] = $client_field_totals[$total][$record[$chart_field]] + $record[$total];
            }
        }

        foreach ($times as $time) {

            $count = 0;

            foreach ($records as $sub) {

                if ($chart_field == 'formatted_is_paid') {
                    $time_points[$total][__('global:unpaid')][$time] = isset($time_points[$total][__('global:unpaid')][$time]) ? $time_points[$total][__('global:unpaid')][$time] : 0;
                    $time_points[$total][__('global:paid')][$time] = isset($time_points[$total][__('global:paid')][$time]) ? $time_points[$total][__('global:paid')][$time] : 0;
                } else {
                    $time_points[$total][$sub[$chart_field]][$time] = isset($time_points[$total][$sub[$chart_field]][$time]) ? $time_points[$total][$sub[$chart_field]][$time] : 0;
                }

                if ($sub['date_entered'] < $time) {
                    # Invoice existed at this point.
                    if ($chart_field == 'formatted_is_paid') {
                        if ($sub['payment_date'] > $time or $sub['payment_date'] == 0) {
                            # Not paid at this point.
                            $time_points[$total][__('global:unpaid')][$time] = $time_points[$total][__('global:unpaid')][$time] + $sub[$total];
                        } else {
                            # Paid at this point.
                            $time_points[$total][__('global:paid')][$time] = $time_points[$total][__('global:paid')][$time] + $sub[$total];
                        }
                    } else {
                        $time_points[$total][$sub[$chart_field]][$time] = $time_points[$total][$sub[$chart_field]][$time] + $sub[$total];
                    }
                }
            }
        }

        foreach ($return_field_totals as $key => $value) {
            $method = '_process_' . $key;
            if (method_exists($this, $method)) {
                $return_field_totals[$key] = $this->$method($value);
            }
        }

        reset($return_field_totals);
        $formatted_total = current($return_field_totals);
        reset($return_field_totals);

        reset($client_field_totals);
        $chart_totals = current($client_field_totals);
        reset($client_field_totals);

        if (empty($chart_totals)) {
            $chart_totals = array();
        }

        reset($time_points);
        $chart_time_points = current($time_points);
        reset($time_points);

        if (empty($chart_time_points)) {
            $chart_time_points = array();
        }

        $reportString = $this->generateReportString($from, $to, $client_id);

        $newRecords = array();
        # Time to filter out the records that aren't meant to show up in the report.
        # I have to get them all for partial_payments' sake, but I can't just show them all!
        foreach ($records as $record) {
            if ((isset($configs['paid']) and $record['is_paid'] == $configs['paid']) OR
                    (isset($configs['tax_collected']) and $record['tax_collected'] > 0) OR
                    (isset($configs['all']) and $configs['all'])) {
                    $newRecords[] = $record;
            } elseif (isset($configs['convert_to_invoices']) and $configs['convert_to_invoices']) {
                if (!isset($newRecords[$record['unique_id']])) {
                    $newRecords[$record['unique_id']] = $record;
                    $newRecords[$record['unique_id']]['amount'] = 0;
                    $newRecords[$record['unique_id']]['unpaid_amount'] = 0;
                    $newRecords[$record['unique_id']]['paid_amount'] = 0;
                }
                 
                $newRecords[$record['unique_id']]['amount'] = $newRecords[$record['unique_id']]['amount'] + $record['money_amount'];
                $newRecords[$record['unique_id']]['unpaid_amount'] = $newRecords[$record['unique_id']]['unpaid_amount'] + ($record['is_paid'] ? 0 : $record['money_amount']);
                $newRecords[$record['unique_id']]['paid_amount'] = $newRecords[$record['unique_id']]['paid_amount'] + ($record['is_paid'] ? $record['money_amount'] : 0);
                
            }
        }
        
        if (isset($configs['convert_to_invoices']) and $configs['convert_to_invoices']) {
            $field_totals[] = 'unpaid_amount';
            $field_totals[] = 'paid_amount';
            $field_totals[] = 'amount';
        }

        $return_field_totals = array();
        foreach ($newRecords as &$record) {
            foreach ($field_totals as $total) {
                if (!isset($return_field_totals[$total])) {
                    $return_field_totals[$total] = 0;
                }

                $return_field_totals[$total] = $return_field_totals[$total] + $record[$total];
            }

            foreach ($record as $key => $value) {
                $method = '_process_' . $key;
                if (method_exists($this, $method)) {
                    $record[$key] = $this->$method($value);
                }
            }
        }

        foreach ($return_field_totals as $key => $value) {
            $method = '_process_' . $key;
            if (method_exists($this, $method)) {
                $return_field_totals[$key] = $this->$method($value);
            }
        }

        $return = array(
            'report' => $report,
            'title' => $this->reports[$report],
            'from' => $from,
            'to' => $to,
            'formatted_from' => format_date($from),
            'formatted_to' => format_date($to),
            'report_url' => site_url("reports/$report/view/$reportString"),
            'report_url_pdf' => site_url("reports/$report/pdf/$reportString"),
            'fields' => $fields,
            'field_totals' => $return_field_totals,
            'formatted_total' => $formatted_total,
            'client_field_totals' => $client_field_totals,
            'chart_type' => isset($chart_type) ? $chart_type : 'pie',
            'per' => isset($per) ? $per : __('reports:perclient'),
            'chart_totals' => $chart_totals,
            'time_points' => $time_points,
            'chart_time_points' => $chart_time_points,
            'records' => $newRecords
        );

        return $return;
    }

}