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
 * The payments model
 *
 * @subpackage	Models
 * @category	Payments
 */
class Invoice_m extends Pancake_Model
{
	/**
	 * @var	string	The payments table name
	 */
	protected $table = 'invoices';

	/**
	 * @var string	The table that contains the invoice rows
	 */
	protected $rows_table = 'invoice_rows';

	/**
	 * @var	array	The array of validation rules
	 */
	protected $validate = array(
		'client_id' => array(
			'field'	  => 'client_id',
			'label'	  => 'Client',
			'rules'	  => 'required'
		),
		'type' => array(
			'field'	  => 'type',
			'label'	  => 'Type',
			'rules'	  => 'required'
		),
		array(
			'field'	  => 'is_recurring',
			'label'	  => 'Is recurring',
			'rules'	  => 'numeric'
		),
		array(
			'field'	  => 'currency',
			'label'	  => 'Currency',
			'rules'	  => ''
		),
		array(
			'field'	  => 'due_date',
			'label'	  => 'Due Date',
			'rules'	  => ''
		),
		array(
			'field'	  => 'frequency',
			'label'	  => 'Frequency',
			'rules'	  => 'max_length[1]'
		),
		array(
			'field'	  => 'auto_send',
			'label'	  => 'Auto Send',
			'rules'	  => ''
		)
	);
        
	function count($client_id, $is_paid = false)
	{
	    $where = array('is_paid' => $is_paid, 'type !=' => 'ESTIMATE');
	    if ($client_id !== NULL) {
	        $where['client_id'] = $client_id;
	    }
	    return $this->db->where($where)->count_all_results($this->table);
	}

	function countEstimates($client_id)
	{
	    if ($client_id > 0) {
	        $this->db->where('client_id', $client_id);
	    }
	    
	    $this->db->where('type', 'ESTIMATE');
            
            return $this->db->count_all_results($this->table);
        }
        
        function upgradeToPartialPayments() {
            $CI = &get_instance();
            $CI->load->model('invoices/partial_payments_m', 'ppm');
            
            $invoices = $this->db->select("invoices.unique_id, invoices.due_date, invoices.is_paid, invoices.payment_date, invoices.txn_id, 
                IF(".$this->db->dbprefix('invoices').".txn_id = '', IF(".$this->db->dbprefix('invoices').".is_paid = 1, 'cash', ''), 'paypal_m') as gateway,
                IF(".$this->db->dbprefix('invoices').".payment_status = '', IF(".$this->db->dbprefix('invoices').".is_paid, 'Completed', ''), ".$this->db->dbprefix('invoices').".payment_status) as status", false)
                                 ->not_like('type', 'ESTIMATE')
                                 ->where('(SELECT COUNT(*) FROM '.$this->db->dbprefix('partial_payments').' WHERE '.$this->db->dbprefix('invoices').'.unique_id = '.$this->db->dbprefix('partial_payments').'.unique_invoice_id) = 0', null, false)
                                 ->get($this->table)
                                 ->result_array();
            foreach ($invoices as $invoice) {
                    # This invoice has no part payments, let's create one.
                    $CI->ppm->setPartialPayment($invoice['unique_id'], 1, 100, 1, (($invoice['due_date'] > 0) ? $invoice['due_date'] : 0), '');
                    $CI->ppm->setPartialPaymentDetails($invoice['unique_id'], 1, $invoice['payment_date'], $invoice['gateway'], $invoice['status'], $invoice['txn_id']);
            }
            return true;
        }
        
        function getProposalAmount($proposal_id)
		{
            $buffer = $this->db->select('amount, exchange_rate')->get_where($this->table, array('proposal_id' => $proposal_id))->result_array();
            $amount = 0;
            
            # We need to calculate the amount in the default currency, so the totals will add up properly!
            foreach ($buffer as $row) {
                $amount = $row['amount'] / $row['exchange_rate'];
            }
            return $amount;
        }
	
	function convertProposalEstimatesIntoInvoices($proposal_id) {
	    
	    $CI = &get_instance();
            $CI->load->model('invoices/partial_payments_m', 'ppm');
	    
	    $estimates = $this->db->select('unique_id, id, type')->get_where($this->table, array('proposal_id' => $proposal_id))->result_array();
	    
	    foreach ($estimates as $invoice) {
		if ($invoice['type'] != 'ESTIMATE') {
		    # This has already been turned into an invoice, let's not touch it.
		    continue;
		} else {
		    # First, we need to change the type:
		    $this->db->where('id', $invoice['id'])->update($this->table, array('type' => 'DETAILED'));
		    # Now we need to give it a partial payment.
		    $CI->ppm->setPartialPayment($invoice['unique_id'], 1, 100, 1, 0, '');
		    # Now we're going to let Pancake fix the invoice record. This should always be done when you're messing with partial payments.
		    $this->fixInvoiceRecord($invoice['unique_id']);
		    # Done! Estimate converted to invoice.
		}
	    }
       
	}
	
	function convertProposalInvoicesIntoEstimates($proposal_id) {
	    
	    $CI = &get_instance();
            $CI->load->model('invoices/partial_payments_m', 'ppm');
	    
	    $invoices = $this->db->select('id, unique_id, type')->get_where($this->table, array('proposal_id' => $proposal_id))->result_array();
	    
	    foreach ($invoices as $invoice) {
		if ($invoice['type'] == 'ESTIMATE') {
		    # This has already been turned into an estimate, let's not touch it.
		    continue;
		} else {
		    $this->db->where('id', $invoice['id'])->update($this->table, array('type' => 'ESTIMATE'));
                    $CI->ppm->removePartialPayments($invoice['unique_id']);
		    $this->fixInvoiceRecord($invoice['unique_id']);
		}
	    }
	}
        
        /**
         * Set the invoice as paid if all its parts are paid.
         * Also changes the due date to the latest due date of the partial payments.
         * 
         * Called when creating or editing an invoice, and on IPN.
         *
         * @param string $unique_id
         * @return boolean 
         */
        function fixInvoiceRecord($unique_id) {
            
            $invoice = $this->get($unique_id);
            
            $CI = &get_instance();
            $CI->load->model('invoices/partial_payments_m', 'ppm');
            $parts = $CI->ppm->getInvoicePartialPayments($unique_id);
            
            $is_paid = 1;
            $due_date = ($invoice['due_date'] <= 0) ? 0 : $invoice['due_date'];
            $last_part_payment_date = 0;
	    
            foreach ($parts as $part) {
                if (!$part['is_paid']) {
                    $is_paid = 0;
                } else {
		    
		}
                
                if ($part['due_date'] > $due_date) {
                    $due_date = $part['due_date'];
                }
		
		if ($part['payment_date'] > $last_part_payment_date) {
		    $last_part_payment_date = $part['payment_date'];
		}
            }
            
            $data = array();
            
            if (!$invoice['is_paid']) {
                $data = array(
                    'is_paid' => $is_paid,
                    'payment_date' => $is_paid ? $last_part_payment_date : 0
                );
            } else {
		# Invoice is paid. Let's check if the parts have been modified.
		if (!$is_paid) {
		    # Parts have been changed, and invoice isn't paid anymore.
		    $data = array(
			'is_paid' => 0,
			'payment_date' => 0
		    );
		}
		    
	    }
            
           if ($invoice['due_date'] != $due_date) {
               $data['due_date'] = $due_date;
           }
           
           if ($data != array()) {
                return $this->db->where('unique_id', $unique_id)->update($this->table, $data);
           } else {
               return true;
           }
        }
	
	function recordView($unique_id) {
	    return $this->db->where('unique_id', $unique_id)->update($this->table, array('last_viewed' => time()));
	}
        
        public function sendNotificationEmail($unique_id, $message = NULL, $subject = '') {
            
            if ($message == NULL) {
                # Use default message if none was provided.
                $message = PAN::setting('email_new_invoice');
            }
            
            $this->load->model('clients/clients_m');
            $this->load->model('files/files_m');

            $invoice = (array) $this->get_by_unique_id($unique_id);
            $invoice['url'] = site_url($unique_id);
	    
	    if ($subject == null) {
		$subject = 'Invoice #'.$invoice['invoice_number'];
	    }

            $client = (array) $this->clients_m->get($invoice['client_id']);
            $settings = (array) $this->settings->get_all();
            $files = $this->files_m->get_by_unique_id($unique_id);
            $files = empty($files) ? array() : array(1);
            $parser_array = array(
                'invoice' => $invoice,
                'client' => $client,
                'settings' => $settings,
                'files' => $files,
            );
            $message = nl2br($message);

            $this->load->library('simpletags');
            $result = $this->simpletags->parse($message, $parser_array);
            $email_body = Email_Template::build('new_invoice', $result['content']);

	    $to = $invoice['email'];
	    $message = $email_body;
	    
	    $result = send_pancake_email_raw($to, $subject, $message);
            
            if ($result) {
                $this->update_simple($unique_id, array('last_sent' => time(), 'has_sent_notification' => 1));
                return true;
            } else {
                return false;
            }
        }

    // ------------------------------------------------------------------------

	/**
	 * Gets the total number of paid invoices and total of those invoices
	 *
	 * @access	public
	 * @param	int		The client id to filter it by
	 * @return	array 	An array containing count and total
	 */
	public function paid_totals($client_id = NULL)
	{
		$CI = &get_instance();
                $CI->load->model('invoices/partial_payments_m', 'ppm');
                return $CI->ppm->getTotals($client_id, true);
	}

	// ------------------------------------------------------------------------

	/**
	 * Gets the total number of overdue invoices and total of those invoices
	 *
	 * @access	public
	 * @param	int		The client id to filter it by
	 * @return	array 	An array containing count and total
	 */
	public function overdue_totals($client_id = NULL)
	{
		$CI = &get_instance();
                $CI->load->model('invoices/partial_payments_m', 'ppm');
                return $CI->ppm->getTotals($client_id, 'OVERDUE');
	}

	// ------------------------------------------------------------------------

	/**
	 * Gets the total number of unpaid invoices and total of those invoices
	 *
	 * @access	public
	 * @param	int		The client id to filter it by
	 * @return	array 	An array containing count and total
	 */
	public function unpaid_totals($client_id = NULL)
	{
		$CI = &get_instance();
                $CI->load->model('invoices/partial_payments_m', 'ppm');
                return $CI->ppm->getTotals($client_id, false);
	}

	// ------------------------------------------------------------------------

	/**
	 * Checks if the invoice is paid or not.
	 *
	 * @access	public
	 * @param	string	The unique id of the invoice
	 * @return	bool	If the invoice is paid
	 */
	public function is_paid($unique_id)
	{
		$result = $this->db->select("unique_id")
						   ->where(array('invoices.is_paid' => 1, 'unique_id' => $unique_id))
						   ->get($this->table)->result();

		if (empty($result))
		{
			return FALSE;
		}

		return TRUE;
	}
        
        /**
         * Gets the ID of an invoice based on its Unique ID.
         * 
         * It's this kind of stuff that makes me want to use Terra Data.
         * Terra Data generates these functions for you with the
         * Method Query Interface. Sigh.
         * 
         * @param string $unique_id
         * @return int 
         */
        function getIdByUniqueId($unique_id) {
            $buffer = $this->db->select('id')->where('unique_id', $unique_id)->get($this->table)->row_array();
            return (int) $buffer['id'];
        }
        
        function getInvoiceNumberById($id) {
            $buffer = $this->db->select('invoice_number')->where('id', $id)->get($this->table)->row_array();
            return (int) $buffer['invoice_number'];
        }
        
        function getIsRecurringByUniqueId($unique_id) {
            $buffer = $this->db->select('is_recurring')->where('unique_id', $unique_id)->get($this->table)->row_array();
            return (int) $buffer['is_recurring'];
        }
        
        function getEstimatesForDropdown() {
            $config = array(
                'type' => 'estimates',
                'return_object' => false
            );
            $buffer = $this->flexible_get_all($config);
            $return = array();
            
            foreach ($buffer as $row) {
                if ($row['proposal_id'] == 0) {
		    $company = empty($row['company']) ? '' : ' - '.$row['company'];
                    $return[$row['id']] = __('proposals:estimatexfory', array($row['invoice_number'], $row['client_name'].$company));
                }
            }
            
            return $return;
            
        }

	// ------------------------------------------------------------------------

	/**
	 * Retrieves an invoice
	 *
	 * @access	public
	 * @param	string	The unique id of the invoice
	 * @return	array 	The payment array
	 */
	public function get($unique_id) {
	    return $this->flexible_get_all(array('unique_id' => $unique_id, 'include_totals' => true, 'return_object' => false, 'get_single' => true, 'include_partials' => true, 'type' => 'both'));
	}

	private function _calculate_invoice($invoice, $object = false)
	{
            $invoice = (array) $invoice;
		$invoice['taxes'] = array();
		$invoice['tax_total'] = 0;
		$invoice['sub_total'] = 0;
		// Loop through items and build subtotal & tax stuff
		foreach($invoice['items'] as & $item)
		{
			// Only increase tax if the item is taxable
			if ($item['tax_id'] != 0)
			{
				$tax_id = $item['tax_id'];
				$tax = Settings::tax($tax_id);
				$invoice['tax_total'] += $item['total'] * ( $tax['value'] / 100 );

				if ( ! isset($invoice['taxes'][$tax_id]))
				{
					$invoice['taxes'][$tax_id] = $item['total'] * ( $tax['value'] / 100 );
				}
				else
				{
					$invoice['taxes'][$tax_id] += $item['total'] * ( $tax['value'] / 100 );
				}
			}

			// Update sub-total
			$invoice['sub_total'] += $item['total'];
		}

		$invoice['total'] = $invoice['sub_total'] + $invoice['tax_total'];

                if ($object) {
                    $buffer = new stdClass();
                    foreach ($invoice as $key => $value) {
                        $buffer->$key = $value;
                    }
                    
                    return $buffer;
                } else {
                    return $invoice;
                }
	}

	// ------------------------------------------------------------------------

	/**
	 * Gets all the invoices from the past 30 days
	 *
	 * @access	public
	 * @param	int		The client id to filter it by
	 * @return	object 	An object containing the invoices
	 */
	public function past_30_days($client_id = NULL)
	{
	    return $this->flexible_get_all(array(
		'past_x_days' => 30,
		'client_id' => $client_id
	    ));
	}

	// ------------------------------------------------------------------------

	/**
	 * Gets all the invoices
	 *
	 * @access	public
	 * @param	int		The client id to filter it by
	 * @return	object 	An object containing the invoices
	 */
	public function get_all_for_api()
	{
		$this->db
			->select("invoices.*, IF(date_entered > 0, FROM_UNIXTIME(date_entered), NULL) as date_entered, 
			IF(due_date > 0, FROM_UNIXTIME(due_date), NULL) as due_date,
			IF(payment_date > 0, FROM_UNIXTIME(payment_date), NULL) as payment_date,
			clients.first_name, clients.last_name, clients.email, clients.company, clients.phone, currencies.code as currency_code", FALSE)
			->from($this->table)
			->join('clients', 'invoices.client_id = clients.id')
			->join('currencies', 'invoices.currency_id = currencies.id', 'left');

		$results = $this->db->get()->result();

		foreach ($results as &$row)
		{
			$row->paid = FALSE;
			$row->overdue = FALSE;

			if ($row->is_paid == 1)
			{
				$row->paid = TRUE;
			}
			elseif ($row->due_date < time())
			{
				$row->overdue = TRUE;
			}
			
			$row->id = (int) $row->id;
			$row->client_id = (int) $row->client_id;
			$row->is_paid = (bool) $row->is_paid;
			$row->is_recurring = (bool) $row->is_recurring;
			$row->auto_send = (bool) $row->auto_send;
			$row->auto_send = (bool) $row->auto_send;
			$row->exchange_rate = (float) $row->exchange_rate;
		}

		return $results;
	}

	// ------------------------------------------------------------------------

	/**
	 * Gets all the invoices
	 *
	 * @access	public
	 * @param	int		The client id to filter it by
	 * @return	object 	An object containing the invoices
	 */
	public function get_all($client_id = NULL)
	{
		$this->db
			->select("invoices.*, clients.first_name, clients.last_name, clients.email, clients.company, clients.phone, currencies.code as currency_code")
			->from($this->table)
			->join('clients', 'invoices.client_id = clients.id')
			->join('currencies', 'invoices.currency_id = currencies.id', 'left');

		if ($client_id !== NULL)
		{
			$this->db->where('client_id', $client_id);
		}
		
		$this->db->where('type !=', 'ESTIMATE');

		$results = $this->db->get()->result();

		foreach ($results as & $row)
		{
			$row->paid = FALSE;
			$row->overdue = FALSE;

			if ($row->is_paid == 1)
			{
				$row->paid = TRUE;
			}
			elseif ($row->due_date < time())
			{
				$row->overdue = TRUE;
			}
		}

		return $results;
	}

	// ------------------------------------------------------------------------

	/**
	 * Gets by Unique ID.
	 *
	 * @access	public
	 * @param	int		The unique id
	 * @return	object 	An object containing the invoice
	 */
	public function get_by_unique_id($unique_id)
	{
                return $this->flexible_get_all(array('type' => 'both', 'unique_id' => $unique_id, 'get_single' => true, 'return_object' => false, 'include_totals' => true, 'include_partials' => true));
	}

	// ------------------------------------------------------------------------

	/**
	 * Gets all the paid invoices.  Optionally filtered by client OR by project
	 *
	 * @access	public
	 * @param	int		The client id to filter it by
         * @param       int             The project id to filter it by
	 * @return	object 	An object containing the payments
	 */
	public function get_all_paid($client_id = NULL, $project_id = NULL, $offset = null)
	{
            return $this->flexible_get_all(array('client_id' => $client_id, 'project_id' => $project_id, 'paid' => true, 'offset' => $offset));
	}

	// ------------------------------------------------------------------------

	/**
	 * Gets all the unpaid invoices.  Optionally filtered by client.
	 *
	 * @access	public
	 * @param	int		The client id to filter it by
	 * @return	object 	An object containing the payments
	 */
	public function get_all_unpaid($client_id = NULL, $offset = null)
	{
            return $this->flexible_get_all(array('client_id' => $client_id, 'paid' => false, 'offset' => $offset));
	}

	// ------------------------------------------------------------------------

	/**
	 * Gets all the overdue invoices.  Optionally filtered by client.
	 *
	 * @access	public
	 * @param	int		The client id to filter it by
	 * @return	object 	An object containing the payments
	 */
	public function get_all_overdue($client_id = NULL, $offset = null)
	{
            return $this->flexible_get_all(array('client_id' => $client_id, 'paid' => false, 'overdue' => true, 'offset' => $offset));
	}

	// ------------------------------------------------------------------------

	/**
	 * Gets all the estimates.  Optionally filtered by client.
	 *
	 * @access	public
	 * @param	int		The client id to filter it by
	 * @return	object 	An object containing the estimates
	 */
	public function get_all_estimates($client_id = NULL, $offset = null)
	{
		return $this->flexible_get_all(array('type' => 'estimates', 'client_id' => $client_id, 'offset' => $offset));
	}
	
	public function is_estimate($unique_id) {
	    $buffer = $this->db->select('type')->where('type', 'ESTIMATE')->where('unique_id', $unique_id)->get($this->table)->row_array();
	    return isset($buffer['type']);
	}
        
        function flexible_get_all($config) {
                    
            $type = isset($config['type']) ? $config['type'] : 'invoices';
            $from = isset($config['from']) ? $config['from'] : 0;
            $to = isset($config['to']) ? $config['to'] : 0;
            $client_id = isset($config['client_id']) ? $config['client_id'] : NULL;
            $unique_id = isset($config['unique_id']) ? $config['unique_id'] : NULL;
            $id = isset($config['id']) ? $config['id'] : NULL;
            $project_id = isset($config['project_id']) ? $config['project_id'] : NULL;
            $overdue = isset($config['overdue']) ? $config['overdue'] : NULL;
            $paid = isset($config['paid']) ? $config['paid'] : NULL;
            $object = isset($config['return_object']) ? $config['return_object'] : true;
            $get_single = isset($config['get_single']) ? $config['get_single'] : false;
            $offset = isset($config['offset']) ? $config['offset'] : NULL; # if offset is NOT null, then it was provided, meaning we want pagination
            $include_totals = isset($config['include_totals']) ? $config['include_totals'] : false;
            $include_partials = isset($config['include_partials']) ? $config['include_partials'] : false;
	    $past_x_days = isset($config['past_x_days']) ? (int) $config['past_x_days'] : false;
            $order = isset($config['order']) ? $config['order'] : array('date_entered' => 'DESC');
	    
            $this->db
                ->select("UNIX_TIMESTAMP(DATE_SUB(FROM_UNIXTIME(IF(due_date > 0, due_date, date_entered )), INTERVAL send_x_days_before DAY)) as date_to_automatically_notify, invoices.id as real_invoice_id, invoices.*, clients.address, clients.first_name, clients.last_name, clients.email, clients.company, clients.phone, currencies.code as currency_code", false)
                ->from($this->table)
                ->join('clients', 'invoices.client_id = clients.id')
                ->join('currencies', 'invoices.currency_id = currencies.id', 'left');

            if ($unique_id !== NULL) {
                $this->db->where('unique_id', $unique_id);
            }
            
            if ($id !== NULL) {
                $this->db->where('invoices.id', $id);
            }
	    
	    if ($past_x_days) {
		$this->db->where(array('invoices.date_entered >' => strtotime('-'.$past_x_days.' days')));
	    }
            
            # if offset is NOT null, then it was provided, meaning we want pagination
            if ($offset !== NULL) {
                $this->db->limit(10, $offset);
            }
            
            if ($overdue !== NULL) {
                if ($overdue) {
                    $this->db->where(array('due_date <' => time()));
                } else {
                    $this->db->where(array('due_date >' => time()));
                }
            }

            if ($paid !== NULL) {
                if ($paid) {
                    $this->db->where(array('invoices.is_paid' => 1));
                } else {
                    $this->db->where(array('invoices.is_paid' => 0));
                }
            }


            if ($client_id) {
                $this->db->where('client_id', $client_id);
            }

            if ($project_id !== NULL) {
                $this->db->where('project_id', $project_id);
            }

            if ($from != 0) {
                $this->db->where('date_entered >', $from);
            }

            if ($to != 0) {
                $this->db->where('date_entered <', $to);
            }

            if ($type == 'invoices') {
                $this->db->where('type !=', 'ESTIMATE');
            } elseif ($type == 'both') {
                // No need for filtering any! :)
            } else {
                $this->db->where('type', 'ESTIMATE');
            }
	    
	    foreach ($order as $field_to_order_by => $desc_or_asc) {
		$this->db->order_by('invoices.'.$field_to_order_by, strtoupper($desc_or_asc));
	    }
            
            $result = $this->db->get()->result();
            
            $CI = &get_instance();
                $CI->load->model('invoices/partial_payments_m', 'ppm');
                
                $return = array();
            
            foreach ($result as $invoice) {
                $invoice->client_name = $invoice->first_name.' '.$invoice->last_name;
                $invoice->formatted_is_paid = $invoice->is_paid ? __('global:paid') : __('global:unpaid');
		$invoice->paid = $invoice->is_paid;
		$invoice->url = site_url($invoice->unique_id);
		$invoice->currency_symbol = Currency::symbol($invoice->currency_code);
                
                if ($include_totals) {
                    if ($invoice->type == 'DETAILED' OR $invoice->type == 'ESTIMATE') {
                        $invoice->items = $this->db
                                ->where('unique_id', $invoice->unique_id)
                                ->order_by('sort')
                                ->get($this->rows_table)
                                ->result_array();

                            $invoice->paid_amount = $this->ppm->getInvoicePaidAmount($invoice->unique_id);
                            $invoice->unpaid_amount = $invoice->amount - $invoice->paid_amount;
                        
                        $invoice = $this->_calculate_invoice($invoice, true);
			
			$i = 0;
			foreach ($invoice->taxes as $id) {
			    $tax = Settings::tax($id);
			    if (!empty($tax['reg'])) {$i++;}
			}

			$invoice->has_tax_reg = ($i > 0);
			
                        $invoice->tax_collected = ($invoice->paid_amount * $invoice->tax_total) / $invoice->amount;
                    } else {
                        $invoice->items = array();
                        $invoice->paid_amount = $this->ppm->getInvoicePaidAmount($invoice->unique_id);
                        $invoice->unpaid_amount = $invoice->amount - $invoice->paid_amount;
                        $invoice->tax_collected = 0;
			$invoice->tax_total = 0;
			$invoice->has_tax_reg = 0;
                    }
                }
                
		if ($include_partials) {
		    if (isset($invoice->tax_total)) {
			$invoice->partial_payments = $CI->ppm->getInvoicePartialPayments($invoice->unique_id, $invoice->amount, $invoice->tax_total);
		    } else {
			$invoice->partial_payments = $CI->ppm->getInvoicePartialPayments($invoice->unique_id);
		    }
		    $invoice->next_part_to_pay = 0;
                
		    foreach ($invoice->partial_payments as $part) {
			if (!$part['is_paid'] and $invoice->next_part_to_pay == 0) {
			    $invoice->next_part_to_pay = $part['key'];
			}
		    }
                }
		
                $return[] = $object ? $invoice : (array) $invoice;
            }

            if ($get_single) {
                reset($return);
                return current($return);
            } else {
                return $return;
            }
        }
        
        public function getEarliestInvoiceDate() {
            $buffer = $this->db->select('date_entered')->limit(1)->order_by('date_entered', 'asc')->get($this->table)->row_array();
            if (isset($buffer['date_entered'])) {
                return $buffer['date_entered'];
            } else {
                return 0;
            }
        }

	// ------------------------------------------------------------------------

	/**
	 * Inserts a new invoice
	 *
	 * @access	public
	 * @param	array	The input array
	 * @return	string 	The unique id of the payment
	 */
	public function insert($input)
	{
		if ($this->input->post('project_id'))
		{
			$query = $this->db->select('client_id')->get_where('projects', array('id' => $this->input->post('project_id')))->row();
			
			$input['client_id'] = $query ? $query->client_id : null;
			$input['type'] = 'DETAILED';
		}
		
		if ( ! $this->validate($input))
		{
			return FALSE;
		}
		
		if ($input['type'] != 'ESTIMATE')
		{
			array_pop($this->validate);
		}

                $input['invoice_number'] = $this->_generate_invoice_number(isset($input['invoice_number']) ? $input['invoice_number'] : null);

		$unique_id = $this->_generate_unique_id();

		if ($input['type'] == 'DETAILED' OR $input['type'] == 'ESTIMATE')
		{
			// If items are in HTML format, format em
			if (isset($input['invoice_item']))
			{
				$input['amount'] = $this->insert_invoice_rows($unique_id, $input['invoice_item'], TRUE);
			}

			else
			{
				$input['amount'] = $this->insert_invoice_rows($unique_id, $input['items'], FALSE);
			}
		}

		if (empty($input['amount']))
		{
			$this->form_validation->_error_array['amount'] = __('invoices:amountrequired');
			return FALSE;
		}

		// Get currency rate for historically accurate invoicing
		if ( ! empty($input['currency']))
		{
			$currency = $this->db
				->select('id, rate')
				->where('code', $input['currency'])
				->get('currencies')
				->row() OR show_error(__('invoices:currencydoesnotexist'));

			$this->db->set(array(
				'currency_id' => $currency->id,
				'exchange_rate' => $currency->rate
			));
		}
                
                $due_date =  isset($input['due_date']) ? ((is_numeric($input['due_date']) and strlen($input['due_date']) == 10) ? $input['due_date'] : strtotime($input['due_date'])) : 0;
                
		$this->db->set(array(
			'unique_id'			=> $unique_id,
			'client_id'			=> $input['client_id'],
			'amount'			=> str_replace(array(',', ' '), '', $input['amount']),
			'due_date'			=> $due_date,
			'invoice_number'	=> ! empty($input['invoice_number']) ? $input['invoice_number'] : '',
			'notes'				=> ! empty($input['notes']) ? $input['notes'] : null,
			'description'		=> ! empty($input['description']) ? $input['description'] : null,
			'payment_hash'		=> md5(time()),
			'type'				=> $input['type'],
			'date_entered'		=> time(),
			'is_paid'			=> ! empty($input['is_paid']),
                         'send_x_days_before' => isset($input['send_x_days_before']) ? (($input['send_x_days_before'] >= 0) ? $input['send_x_days_before'] : 7) : 7,
			'payment_date'		=> ! empty($input['is_paid']) ? time() : 0,
			'is_recurring'		=> ! empty($input['is_recurring']),
			'frequency'			=> isset($input['frequency']) ? $input['frequency'] : null,
			'auto_send'			=> ( ! empty($input['is_recurring']) && ! empty($input['auto_send'])),
			'recur_id'			=> ( ! empty($input['is_recurring']) && ! empty($input['recur_id'])) ? $input['recur_id'] : 0,
		))->insert($this->table);

		$insert_id = $this->db->insert_id();
                
                $this->getNextInvoiceReoccurrenceDate($insert_id);
                
                # Partial Payments. Let's make sure the amounts work properly AFTER creating the invoice (so we can use getInvoiceTotalAmount()). Shall we?
                
                if ($input['type'] != 'ESTIMATE') {
                    $CI = &get_instance();
                    $CI->load->model('invoices/partial_payments_m', 'ppm');
                    
                    if (!isset($input['partial-amount'])) {
                        # No partial payments have been entered, let's create a 100% due when the invoice is due payment plan.
                        $CI->ppm->setPartialPayment($unique_id, 1, 100, 1, (($due_date > 0) ? $due_date : 0), '');
                    } else {
                        $result = $CI->ppm->processInput($unique_id, 
                                $input['partial-amount'], $input['partial-is_percentage'], 
                                $input['partial-due_date'], $input['partial-notes'], isset($input['partial-is_paid']) ? $input['partial-is_paid'] : array());

                        if ($result === 'WRONG_TOTAL') {
                            $this->form_validation->_error_array['amount'] = lang('partial:wrongtotal');
                            $this->delete($unique_id);
                            return FALSE;
                        } elseif (!$result) {
                            $this->form_validation->_error_array['amount'] = lang('partial:problemsaving');
                            $this->delete($unique_id);
                            return FALSE;
                        }
                    }
                }

		// No input number given, use the insert_id
		if ( ! empty($input['is_recurring']) AND empty($input['recur_id']))
		{
			$this->db
				->where('unique_id', $unique_id)
				 ->set('recur_id', $insert_id)
				 ->update($this->table);
		}

                $this->fixInvoiceRecord($unique_id);
		return $unique_id;
	}
        
        function refresh_reoccurring_invoices() {
            $invoices = $this->db->dbprefix('invoices');
            
            # Get all invoices whose last reoccurrence is in the past, which means that we need to create a new reocurrence for them.
            # If they have no reoccurrence, last reoccurrence is the due_date of the original invoice. If the original had no due_date,
            # the last reocurrence is date_entered, and the next will be date_entered + 1 frequency. - Bruno
            $buffer = $this->db->query("SELECT id, unique_id FROM $invoices WHERE is_recurring = 1 and id = recur_id and
                IF((SELECT due_date FROM $invoices as i2 WHERE recur_id = $invoices.id order by date_entered desc LIMIT 0, 1) > 0, (SELECT due_date FROM $invoices as i2 WHERE recur_id = $invoices.id order by date_entered desc LIMIT 0, 1), date_entered) < UNIX_TIMESTAMP()")->result_array();
            
            foreach ($buffer as $row) {
                $invoice = $this->get_by_unique_id($row['unique_id']);
                # Need to create new invoices for each of these.
                
                if ($invoice['currency_id'] > 0) {
                    $invoice['currency'] =  $this->db->select('code')->where('id', $invoice['currency_id'])->get('currencies')->row_array();
                    $invoice['currency'] = $invoice['currency']['code'];
                } else {
                    $invoice['currency'] = '';
                }
                
                $data = array(
                        'client_id' => $invoice['client_id'],
                        'amount' => $invoice['amount'],
                        'due_date' => $this->getNextInvoiceReoccurrenceDate($invoice['id']),
                        'invoice_number' => $this->getNextInvoiceReocurrenceNumber($invoice['id']),
                        'notes' => $invoice['notes'],
                        'description' => isset($invoice['description']) ? $invoice['description'] : '',
                        'type' => $invoice['type'],
                        'is_paid' => 0,
                        'payment_date' => 0,
                        'send_x_days_before' => $invoice['send_x_days_before'],
                        'is_recurring' => 1,
                        'currency' => $invoice['currency'],
                        'frequency' => $invoice['frequency'],
                        'auto_send' => $invoice['auto_send'],
                        'recur_id' => $invoice['recur_id'],
                        'items' => $invoice['items']
                    );
                
                $id = $this->insert($data);
                
                require_once APPPATH.'modules/gateways/gateway.php';
                Gateway::duplicateInvoiceGateways($id);
                
                if ($id) {
                    echo "Created invoice: {$id}" . (IS_CLI ? PHP_EOL : '<br/>');
                } else {
                    echo "Failed to create clone of {$invoice['recur_id']}" . (IS_CLI ? PHP_EOL : '<br/>');
                }
                
                # Update the next reoccurrence date.
                $this->getNextInvoiceReoccurrenceDate($invoice['id']);
            }
            
            # Send necessary reoccurring invoice emails.
            
            $buffer = $this->db->query("SELECT id, unique_id, send_x_days_before, IF(due_date > 0, due_date, date_entered) as due_date FROM $invoices WHERE is_recurring = 1 and has_sent_notification = 0 and due_date > UNIX_TIMESTAMP()")->result_array();
            
            foreach ($buffer as $row) {
                if (time() > strtotime('-'.$row['send_x_days_before'].' days', $row['due_date'])) {
                    # Need to send out notification email!
                    $success = $this->sendNotificationEmail($row['unique_id']);
                    
                     if ($success) {
                        echo "Sent invoice notification email for invoice: {$row['id']}" . (IS_CLI ? PHP_EOL : '<br/>');
                    } else {
                        echo "Failed to send invoice notification email for invoice: {$row['id']}" . (IS_CLI ? PHP_EOL : '<br/>');
                    }
                    
                }
            }
            
        }
        
        function getNextInvoiceReocurrenceNumber($invoice_id) {
            $buffer = $this->db->select('invoice_number')->order_by('date_entered', 'desc')->limit(1)->where('recur_id', $invoice_id)->get($this->table)->row_array();
            $buffer = explode('-', $buffer['invoice_number']);
            $number = $buffer[0].'-';
            $buffer = isset($buffer[1]) ? sprintf("%03d",   ((int) $buffer[1]) + 1) : '001';
            return $number.$buffer;
        }
        
        function getNextInvoiceReoccurrenceDate($invoice_id) {
            $invoice = $this->flexible_get_all(array('type' => 'both', 'id' => $invoice_id, 'get_single' => true, 'return_object' => false));
            
            if ($invoice['is_recurring'] and $invoice['recur_id'] == $invoice['id']) {
                $buffer = $this->db->where('recur_id', $invoice['id'])->order_by('due_date', 'desc')->limit(1)->get($this->table)->row_array();
                $lastReoccurrence = $buffer['due_date'];
                
                if ($lastReoccurrence == 0) {
                    $lastReoccurrence = $invoice['date_entered'];
                }
                
                switch ($invoice['frequency']) {
                    case 'w':
                        $frequency = 'week';
                        break;
                    case 'm':
                        $frequency = 'month';
                        break;
                    case 'y':
                        $frequency = 'year';
                        break;
                }
                
                $nextReoccurrence = strtotime('+1 '. $frequency, $lastReoccurrence);
                
                $this->db->where('id', $invoice['id'])->update($this->table, array('next_recur_date' => $nextReoccurrence));
                return $nextReoccurrence;
                
            } else {
                return 0;
            }
        }

	// ------------------------------------------------------------------------

	/**
	 * Updates the given invoice.
	 *
	 * @access	public
	 * @param	string	The unique id of the invoice
	 * @param	array	The input array
	 * @return	string	The unique id of the invoice
	 */
	public function update($unique_id, $input)
	{
		$this->validate[] = array(
			'field'	  => 'invoice_number',
			'label'	  => 'Invoice Number',
			'rules'	  => 'required'
		);
		if ( ! $this->validate($input))
		{
			return FALSE;
		}
		if ($input['type'] != 'ESTIMATE')
		{
			array_pop($this->validate);
		}
		array_pop($this->validate);

		if ($input['type'] == 'DETAILED' OR $input['type'] == 'ESTIMATE')
		{
			$input['amount'] = $this->update_invoice_rows($unique_id, $input['invoice_item']);
		}

		if (empty($input['amount']))
		{
			$this->form_validation->_error_array['amount'] = __('invoices:amountrequired');
			return FALSE;
		}

		// If this is a recurring invoice with no history, start it here
		if ( ! empty($input['is_recurring']) AND empty($input['recur_id']))
		{
			$this->db->set('recur_id', 'id', FALSE);
		}

		$this->db->where('unique_id', $unique_id)->set(array(
			'client_id'			=> $input['client_id'],
			'amount'			=> str_replace(array(',', ' '), '', $input['amount']),
			'due_date'			=> strtotime($input['due_date']),
			'invoice_number'	=> $input['invoice_number'],
			'notes'				=> $input['notes'],
			'description'		=> $input['description'],
			'type'				=> $input['type'],
			'is_paid'			=>  ! empty($input['is_paid']),
			'payment_date'		=> (isset($input['is_paid']) AND $input['is_paid'] == '1') ? time() : 0,
			'is_recurring'		=> ! empty($input['is_recurring']),
			'frequency'			=> $input['frequency'],
                        'send_x_days_before' => isset($input['send_x_days_before']) ? (($input['send_x_days_before'] >= 0) ? $input['send_x_days_before'] : 7) : 7,
			'auto_send'			=> ( ! empty($input['is_recurring']) AND ! empty($input['auto_send']))
		))->update($this->table);
                
                $this->getNextInvoiceReoccurrenceDate($this->getIdByUniqueId($unique_id));
                
                # Partial Payments. Let's make sure the amounts work properly AFTER creating the invoice (so we can use getInvoiceTotalAmount()). Shall we?
                
                if ($input['type'] != 'ESTIMATE') {
                    $CI = &get_instance();
                    $CI->load->model('invoices/partial_payments_m', 'ppm');
                    $result = $CI->ppm->processInput($unique_id, 
                            $_POST['partial-amount'], $_POST['partial-is_percentage'], 
                            $_POST['partial-due_date'], $_POST['partial-notes'], isset($_POST['partial-is_paid']) ? $_POST['partial-is_paid'] : array());

                    if ($result === 'WRONG_TOTAL') {
                        $this->form_validation->_error_array['amount'] = lang('partial:wrongtotalbutsaved');
                        return FALSE;
                    } elseif (!$result) {
                        $this->form_validation->_error_array['amount'] = lang('partial:problemsavingbutsaved');
                        return FALSE;
                    }
                } else {
                    $CI = &get_instance();
                    $CI->load->model('invoices/partial_payments_m', 'ppm');
                    $CI->ppm->removePartialPayments($unique_id);
                }

                $this->fixInvoiceRecord($unique_id);
		return $unique_id;

	}
	
	// ------------------------------------------------------------------------

	/**
	 * Updates the given invoice with no validation.
	 *
	 * @access	public
	 * @param	string	The unique id of the invoice
	 * @param	array	The array of items
	 * @return	mixed
	 */
	public function update_simple($unique_id, $data)
	{
		return $this->db->where('unique_id', $unique_id)->update($this->table, $data);
	}

	// ------------------------------------------------------------------------

	/**
	 * Inserts the invoice items for the given items
	 *
	 * @access	public
	 * @param	string	The unique id of the invoice
	 * @param	array	The array of items
	 * @return	int 	The total of the items
	 */
	public function insert_invoice_rows($unique_id, $items, $html = TRUE)
	{
		$this->db->trans_start();
		$amount = 0;

		if ($html === TRUE)
		{
			$items_array = array();

			for ($i = 0; $i < count($items['name']); $i++)
			{
				$qty = isset($items['qty'][$i]) ? $items['qty'][$i] : 0;
				$tax_id = (int) ($items['tax_id'][$i]);
				$rate = (float) str_replace(',', '', $items['rate'][$i]);

				// Work out the total
				$total = $qty * $rate;

				$items_array[] = array(
					'name'			=> $items['name'][$i],
					'description'	=> $items['description'][$i],
					'qty'			=> $qty,
					'rate'			=> $rate,
					'tax_id'		=> $tax_id,
					'total'			=> $total,
					'sort' 			=> $i,
				);
			}

			$items = $items_array;
		}
		
		foreach ($items as $item)
		{
			unset($item['id']);

			$item['unique_id'] = $unique_id;
			
			$this->db->insert($this->rows_table, $item);

			// Add this item total to the invoice amount
			$amount += $item['total'];
		}

		if ($this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();
			return FALSE;
		}

		$this->db->trans_commit();

		return $amount;
	}

	// ------------------------------------------------------------------------

	/**
	 * Updates the invoice rows.
	 *
	 * @access	public
	 * @param	string	The unique id of the invoice
	 * @param	array	The array of items
	 * @return	int		The total of the items
	 */
	public function update_invoice_rows($unique_id, $items)
	{
		$this->db->where(array('unique_id' => $unique_id))->delete($this->rows_table);
		return $this->insert_invoice_rows($unique_id, $items);
	}

	// ------------------------------------------------------------------------

	/**
	 * Deletes an invoice by unique id
	 *
	 * @access	public
	 * @param	string	The unique id of the invoice
	 * @return	void
	 */
	public function delete($unique_id)
	{
            
                $buffer = $this->db->select('id, type')->where('unique_id', $unique_id)->get($this->table)->row_array();
                if (isset($buffer['type']) and !empty($buffer['type'])) {
                    if ($buffer['type'] == 'ESTIMATE') {
                        # It's an estimate, delete proposal sections that use it.
                        $CI = &get_instance();
                        $CI->load->model('proposals/proposals_m');
                        $CI->proposals_m->deleteEstimateSections($buffer['id']);
                    }

                    $this->db->where('unique_id', $unique_id)->delete($this->table);
                    $this->db->where('unique_id', $unique_id)->delete($this->rows_table);
                    $CI = &get_instance();
                    $CI->load->model('invoices/partial_payments_m', 'ppm');
                    $CI->ppm->deleteInvoicePartialPayments($unique_id);
                }
	}

	// ------------------------------------------------------------------------

	/**
	 * Deletes all the invoices for a client
	 *
	 * @access	public
	 * @param	int		The client id
	 * @return	void
	 */
	public function delete_by_client_id($client_id)
	{
		$invoices = $this->db->where('client_id', $client_id)->get($this->table);

		foreach ($invoices->result() as $invoice)
		{
			$this->delete($invoice->unique_id);
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Updates a payment by the payment hash
	 *
	 * @access	public
	 * @param	string	The hash to update
	 * @param	array	The array of data to update
	 * @return	void
	 */
	public function update_by_hash($hash, $data)
	{
		return $this->db->where('payment_hash', $hash)->set($data)->update($this->table);
	}

	/**
	 * Generates the unique id for an invoice
         * 
	 * @access	private
	 * @return	string
	 */
	public function _generate_unique_id()
	{
		$this->load->helper('string');

		$valid = FALSE;
		while ($valid === FALSE)
		{
			$unique_id = random_string('alnum', 8);
			$results = $this->db->where('unique_id', $unique_id)->get($this->table)->result();
			if (empty($results))
			{
				$valid = TRUE;
			}
		}

		return $unique_id;
	}
	
	/**
	 * Generates an invoice number
	 *
	 * @access	private
	 * @return	string
	 */
	public function _generate_invoice_number($number = null)
	{
		$this->load->helper('string');
                
                if (!empty($number)) {
                    if ($this->db->where('invoice_number', $number)->count_all_results($this->table) == 0) {
                       return $number;
                    }
                }

		$valid = FALSE;
                $result = $this->db->limit(1)->select('invoice_number')->order_by('invoice_number', 'desc')->get($this->table)->row_array();
                $invoice_number = isset($result['invoice_number']) ? $result['invoice_number'] : 0;
                $invoice_number = $invoice_number + 1;
		while ($valid === FALSE) {
                    if ($this->db->where('invoice_number', $invoice_number)->count_all_results($this->table) == 0) {
                        $valid = TRUE;
                    } else {
                        $invoice_number++;
                    }
		}
		return $invoice_number;
	}
	
	
}

/* End of file: invoices_m.php */