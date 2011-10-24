<?php

defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Pancake
 *
 * A simple, fast, self-hosted invoicing application
 *
 * @package		Pancake
 * @author		Pancake Dev Team
 * @copyright           Copyright (c) 2010, Pancake Payments
 * @license		http://pancakeapp.com/license
 * @link		http://pancakeapp.com
 * @since		Version 2.2.0
 */
// ------------------------------------------------------------------------

/**
 * The Gateway Class
 *
 * By way of reference: A field's type can be ENABLED, FIELD, CLIENT or INVOICE.
 * A ENABLED field simply defines if a gateway is enabled or not. A FIELD field
 * is a field that can be used by the payment gateway. A CLIENT field defines if
 * a payment gateway is enabled/disabled for a client, and a INVOICE field defines
 * if a payment gateway is enabled/disabled for an invoice.
 * 
 * If there is no ENABLED field, it is assumed that the gateway is disabled.
 * 
 * If there is no CLIENT field for a client, or no INVOICE field for an invoice,
 * it is assumed that the gateway is enabled for them.
 * 
 * @subpackage	Gateway
 * @category	Payments
 */
abstract class Gateway extends Breakfast_Model {

    public $gateway;
    public $title;
    public $frontend_title;
    public $table = 'gateway_fields';
    public $version;
    public $author = 'Pancake Dev Team';
    public $fields = array();
    public $has_payment_page = true;
    
    public function __construct($gateway) {
        parent::__construct();
        $this->gateway = strtolower($gateway);
    }
    
    /**
     * Get the value of a given field for a gateway.
     * If $field is not provided, all fields will be returned.
     * 
     * @param string $field
     * @return array|string
     */
    public function get_field($field = null) {
        $buffer = self::get_fields($this->gateway, 'FIELD', $field);
        return isset($buffer[0]['value']) ? $buffer[0]['value'] : '';
    }
    
    public static function get_fields($gateway = null, $type = null, $field = null) {
        $where = array();
        
        if ($gateway !== null) {
            $where['gateway'] = $gateway;
        }
        
        if ($type !== null) {
            $where['type'] = $type;
        }
        
        if ($field !== null) {
            $where['field'] = $field;
        }
        
        $CI = &get_instance();
        return $CI->db->where($where)->get('gateway_fields')->result_array();
    }
    
    /**
     * Set the value of a field of a certain type for a gateway.
     * 
     * @param string $gateway
     * @param string $field
     * @param mixed $value
     * @param string $type (ENABLED, FIELD, INVOICE or CLIENT)
     * @return boolean 
     */
    public static function set_field($gateway, $field, $value, $type) {
        
        $CI = &get_instance();
        
        $where = array(
            'gateway' => $gateway,
            'field'   => $field,
            'type' => $type
        );
        
        $data = array(
            'gateway' => $gateway,
            'field' => $field,
            'value' => $value,
            'type' => $type
        );
        
        if ($CI->db->where($where)->count_all_results('gateway_fields') == 0) {
            return $CI->db->insert('gateway_fields', $data);
        } else {
            return $CI->db->where($where)->update('gateway_fields', $data);
        }
    }
    
    /**
     * Process the input from the settings page, store everything properly. 
     * 
     * @param array $gateways
     * @return boolean 
     */
    public static function processSettingsInput($gateways) {
        
        foreach (self::get_gateways() as $gateway) {
            if (!isset($gateways[$gateway['gateway']]['enabled'])) {
                $gateways[$gateway['gateway']]['enabled'] = 0;
            }
        }
        
        foreach ($gateways as $gateway => $fields) {
            
            foreach ($fields as $field => $value) {
                if ($field == 'enabled') {
                    $type = 'ENABLED';
                } else {
                    $type = 'FIELD';
                }
                if (!self::set_field($gateway, $field, $value, $type)) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    public static function duplicateInvoiceGateways($invoice_id) {
        $CI = &get_instance();
        $buffer = $CI->db->get_where('gateway_fields', array('type' => 'INVOICE', 'field' => $invoice_id))->result_array();
        foreach ($buffer as $row) {
            self::set_field($buffer['gateway'], $invoice_id, $buffer['value'], 'INVOICE');
        }
        return true;
    }
    
    public static function processItemInput($item_type, $item, $gateways) {
        $enabled = self::get_enabled_gateways();
        
        foreach ($enabled as $field) {
            if (!isset($gateways[$field['gateway']])) {
                $gateways[$field['gateway']] = 0;
            }
        }
        
        foreach ($gateways as $gateway => $enabled) {
            if (!self::set_field($gateway, $item, $enabled, $item_type)) {
                return false;
            }
        }
        
        return true;
    }
    
    private static function get_gateway_list($gateway = null) {
        $gateways = array();
        if ($gateway === null) {
            foreach (scandir(APPPATH . 'modules/gateways/models') as $file) {
                if (substr($file, strlen($file) - 4, 4) == '.php') {
                    $gateways[str_ireplace('.php', '', $file)] = str_ireplace('.php', '', $file);
                }
            }
        } else {
            $gateways[$gateway] = $gateway;
        }
        return $gateways;
    }
    
    public static function get_gateways($gateway = null) {
        
        $return = array();
        $enabled = self::get_fields($gateway, 'ENABLED');
        $fields = self::get_fields($gateway, 'FIELD');
        $gateways = self::get_gateway_list($gateway);
        
        foreach ($gateways as $file) {
             require_once APPPATH . 'modules/gateways/models/' . $file .'.php';
             $object = ucfirst($file);
             $object = new $object();
             
             $return[$file] = array(
                    'gateway' => $file,
                    'title' => $object->title,
		    'frontend_title' => empty($object->frontend_title) ? $object->title : $object->frontend_title,
                    'enabled' => false,
                    'version' => $object->version,
                    'author' => $object->author,
                    'fields' => $object->fields,
                    'has_payment_page' => $object->has_payment_page,
                    'field_values' => array()
                );
        }
        
        foreach ($fields as $field) {
            $return[$field['gateway']]['field_values'][$field['field']] = $field['value'];
        }
        
        foreach ($enabled as $field) {
            $return[$field['gateway']]['enabled'] = (bool) $field['value'];
        }
        
        if ($gateway != null) {
            $return = $return[$gateway];
        }
        
        return $return;
    }
    
    public static function get_enabled_gateways() {
        $gateways = self::get_gateways();
        $return = array();
        foreach ($gateways as $gateway => $fields) {
            if ($fields['enabled']) {
                $return[$gateway] = $fields;
            }
        }
        
        return $return;
    }
    
    public static function get_enabled_gateway_select_array() {
        $gateways = self::get_enabled_gateways();
        $return = array(
            '' => __('gateways:nogatewayused')
        );
        foreach($gateways as $key => $gateway) {
            $return[$key] = $gateway['title'];
        }
        return $return;
    }
    
    public static function get_frontend_gateways($invoice_id = null) {
        $buffer = self::get_item_gateways('INVOICE', $invoice_id, true);
	$enabled = self::get_enabled_gateways();
	
        $return = array();
        foreach ($buffer as $gateway) {
            if ($gateway['has_payment_page'] and isset($enabled[$gateway['gateway']])) {
                $return[$gateway['gateway']] = $gateway;
            }
        }
        return $return;
    }
    
    public static function get_item_gateways($type, $item = null, $include_data = false) {
        $gateways = self::get_fields(null, $type, $item);
        # That's all the gateways for $type, with value $item. Okay.
        
        $available_gateways = self::get_gateway_list();
        
        $return = array();
        
        if (!isset($_POST['gateways'])) {
            
            foreach ($available_gateways as $gateway) {
                $return[$gateway] = true;
            }
            
            foreach ($gateways as $gateway) {
                $return[$gateway['gateway']] = (bool) $gateway['value'];
            }
        } else {
            
            foreach ($available_gateways as $gateway) {
                $return[$gateway] = false;
            }
            
            foreach ($_POST['gateways'] as $gateway => $value) {
                $return[$gateway] = true;
            }
        }
        
        if ($include_data) {
            $buffer = $return;
            $return = array();
            
            foreach ($buffer as $gateway => $value) {
                if ($value) {
                    $return[$gateway] = self::get_gateways($gateway);
                }
            }
        }
        
        return $return;
    }
    
    public static function complete_payment($unique_id, $gateway, $data) {
	
	$CI = &get_instance();
	
	if ($data) {
	    $CI->load->model('invoices/invoice_m');
	    $CI->load->model('clients/clients_m');
	    $CI->load->model('invoices/partial_payments_m', 'ppm');
	    $CI->load->model('files/files_m');

	    $data['payment_method'] = $gateway;
	    $CI->ppm->updatePartialPayment($unique_id, $data);
	    
	    $unique_invoice_id = $CI->ppm->getUniqueInvoiceIdByUniqueId($unique_id);
	    
	    $CI->invoice_m->fixInvoiceRecord($unique_invoice_id);
	    
	    $partCount = $CI->ppm->countInvoicePartialPayments($unique_id);

	    $part = $CI->ppm->getPartialPayment($unique_id);
	    $invoice = $part['invoice'];

	    $client = (array) $CI->clients_m->get($invoice['client_id']);
	    $settings = (array) $CI->settings->get_all();
	    $files = $CI->files_m->get_by_unique_id($unique_id);
	    $files = empty($files) ? array() : array(1);
	    
	    $data['first_name'] = $invoice['first_name'];
	    $data['last_name'] = $invoice['last_name'];
	    
	    $parser_array = array(
		'invoice' => $invoice,
		'client' => $client,
		'settings' => $settings,
		'files' => $files,
		'ipn' => $data,
	    );

	    $receipt = Email_Template::build('receipt', nl2br(PAN::setting('email_receipt')));
	    $notify = Email_Template::build('paid_notification', nl2br(PAN::setting('email_paid_notification')));

	    $CI->load->library('simpletags');
	    $receipt_result = $CI->simpletags->parse($receipt, $parser_array);
	    $notify_result = $CI->simpletags->parse($notify, $parser_array);

	    $to = $client['email'];
	    $subject = 'Your payment has been received for Invoice #' . $invoice['invoice_number'];
	    $message = $receipt_result['content'];
	    
	    send_pancake_email_raw($to, $subject, $message);
	    
	    $to = PAN::setting('notify_email');
	    if ($partCount > 1) {
		$partSubject = 'Part #' . $part['key'] . ' of ' . $partCount . ' of ';
	    } else {
		$partSubject = '';
	    }
	    $subject = $partSubject . 'Invoice #' . $invoice['invoice_number'] . ' has been paid';
	    $message = $notify_result['content'];
	    
	    send_pancake_email_raw($to, $subject, $message);
	    return true;
	} else {
	    return false;
	}
    }

    public abstract function generate_payment_form($unique_id, $item_name, $amount, $success, $cancel, $notify, $currency_code);

    public abstract function process_cancel($unique_id);

    public abstract function process_success($unique_id);

    public abstract function process_notification($unique_id);
}