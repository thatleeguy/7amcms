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
 * The partial payments model
 *
 * @subpackage	Models
 * @category	Payments
 */
class Partial_payments_m extends Breakfast_Model {

    /**
     * @var string The payments table name
     */
    protected $table = 'partial_payments';

    public function __construct() {
        parent::__construct();
    }

    function flexible_get_all($config) {

        $from = isset($config['from']) ? $config['from'] : 0;
        $to = isset($config['to']) ? $config['to'] : 0;
        $client_id = isset($config['client_id']) ? $config['client_id'] : NULL;
        $overdue = isset($config['overdue']) ? $config['overdue'] : NULL;
        $paid = isset($config['paid']) ? $config['paid'] : NULL;

        $data = array();
        if ($client_id) {
            $this->db->where('client_id', $client_id);
        }

        if ($from != 0) {
            $this->db->where('date_entered >', $from);
        }

        if ($to != 0) {
            $this->db->where('date_entered <', $to);
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
                $this->db->where(array('partial_payments.is_paid' => 1));
            } else {
                $this->db->where(array('partial_payments.is_paid' => 0));
            }
        }

        $table = $this->db->dbprefix('partial_payments');
        # Below is some freaking hardcore SQL action.
        # I love using SQL to process things for me. This query returns all the fields I want,
        # already processed. Why use PHP, when SQL can do these things for you? That's what it's here for!
        # - Bruno De Barros
        # P.S. I know I didn't do this in the rest of the methods of this class,
        # I just felt inspired to do it on this one, today.
        $return = $this->db->select("partial_payments.payment_date, partial_payments.is_paid, invoices.unique_id, invoices.client_id, invoices.date_entered, partial_payments.due_date, clients.company, 
                                         CONCAT(first_name, ' ', last_name) as client_name,
                                         (IF(is_percentage, (($table.amount / 100) * " . $this->db->dbprefix('invoices') . ".amount), " . $this->db->dbprefix('invoices') . ".amount) / " . $this->db->dbprefix('invoices') . ".exchange_rate) as money_amount,
                                         IF(" . $this->db->dbprefix('partial_payments') . ".is_paid, '" . __('global:paid') . "', '" . __('global:unpaid') . "') as formatted_is_paid", false)
                        ->join('invoices', 'invoices.unique_id = partial_payments.unique_invoice_id')
                        ->join('clients', 'clients.id = invoices.client_id')
                        ->get($this->table)->result_array();
        return $return;
    }

    function getTotals($client_id = null, $is_paid = false) {
        $CI = &get_instance();
        $CI->load->model('invoices/invoice_m');

        if ($is_paid === 'OVERDUE') {
            $type = 'OVERDUE';
            $is_paid = false;
        } else {
            $type = '';
        }

        $invoice_totals = array();
        $partial_payments = $this->getAllPartialPayments($client_id, $is_paid);
        $total = 0;
        $count = $CI->invoice_m->count($client_id, $is_paid);

        # Let's find out what the totals of the invoices are.
        foreach ($partial_payments as $payment) {

            if ($type == 'OVERDUE' and ($payment['is_paid'] OR $payment['due_date'] > time())) {
                continue;
            }

            if (!isset($invoice_totals[$payment['unique_invoice_id']])) {
                $invoice_totals[$payment['unique_invoice_id']] = $this->getInvoiceTotalAmount($payment['unique_invoice_id']);
            }

            $moneyAmount = ($payment['is_percentage']) ? ( ($payment['amount'] / 100) * $invoice_totals[$payment['unique_invoice_id']] ) : $payment['amount'];
            $moneyAmount = $moneyAmount / $payment['exchange_rate'];
            $total = $total + $moneyAmount;
        }

        return array('count' => $count, 'total' => round($total, 2));
    }
    
    /**
     * Gets the amount in USD of a partial payment.
     * 
     * Used in certain payment methods, such as Authorize.net.
     * 
     * @param float $amount
     * @param string $unique_id
     * @return float 
     */
    function getUsdAmountByAmountAndUniqueId($amount, $unique_id) {
	$buffer = $this->db->select('currency_id, exchange_rate')->join('invoices', 'partial_payments.unique_invoice_id = invoices.unique_id')->get_where('partial_payments', array('partial_payments.unique_id' => $unique_id))->row_array();
	if (isset($buffer['exchange_rate']) and $buffer['exchange_rate'] != 0) {
	    
	    if ($buffer['currency_id'] == 0) {
		$buffer['code'] = Currency::code();
	    } else {
		$code = Currency::code($buffer['currency_id']);
		if (isset($code['code'])) {
		    $buffer['code'] = $code;
		} else {
		    $buffer['code'] = Currency::code();
		}
	    }
	    
	    # Okay, so, the current currency is $buffer['code']. If that's USD, we can return right now.
	    if ($buffer['code'] == 'USD') {
		return $amount;
	    } else {
		# Let's see if the default currency is USD. If so, then we can convert to USD by dividing by the exchange rate.
		if (Currency::code() == 'USD') {
		    return $amount / $buffer['exchange_rate'];
		} else {
		    # Since the invoice isn't in USD and Pancake's default currency isn't USD, let's see if there's USD in the DB.
		    $currency = $this->db->select('rate')->where('code', 'USD')->get('currencies')->row();
		    if (isset($currency['rate'])) {
			# It's in the DB, so we need to divide to the default rate, then multiply by the DB rate.
			$amount = $amount / $buffer['exchange_rate'];
			$amount = $amount * $currency['rate'];
			return $amount;
		    } else {
			# Okay, the invoice isn't in USD, Pancake's default currency isn't USD, there's no USD value in the DB.
			# So let's go ask Google. Last resort.
			
			$from = $buffer['code'];
			$to = 'USD';
			$url = sprintf('http://www.google.com/ig/calculator?hl=en&q=%d%s=?%s', $amount, $from, $to);
			return (float) current(explode(' ', end(explode('rhs: "', get_url_contents($url, false)))));
		    }
		}
	    }
	} else {
	    return 0;
	}
    }

    function getClientHealth($client_id) {

        $unpaidTotals = $this->getTotals($client_id);
        $unpaidTotals = $unpaidTotals['total'];
        $paidTotals = $this->getTotals($client_id, true);
        $paidTotals = $paidTotals['total'];
        $overdueTotals = $this->getTotals($client_id, 'OVERDUE');
        $overdueTotals = $overdueTotals['total'];
        
        # 100 = all paid, no unpaid, no overdue.
        # 50 = 50% unpaid, 50% paid
        # 100 = 100 - unp

        $invoice_total = $unpaidTotals + $paidTotals;
        $unpaid_without_overdue = $unpaidTotals - $overdueTotals;
        $health = array();
        if ($invoice_total > 0) {
            $health['overdue'] = round(($overdueTotals / $invoice_total) * 100, 2);
            $health['paid'] = round(($paidTotals / $invoice_total) * 100, 2);
            $health['unpaid'] = round(($unpaidTotals / $invoice_total) * 100, 2);
            $health['overall'] = 100 - $health['unpaid'];
        } else {
            $health = array('overdue' => 0, 'paid' => 100, 'unpaid' => 0, 'overall' => 100);
        }
        return $health;
    }

    function getAllPartialPayments($client_id = null, $is_paid = false) {
        $where = array('partial_payments.is_paid' => $is_paid);
        if ($client_id !== null) {
            $where['client_id'] = $client_id;
        }
        return $this->db->select('partial_payments.*, invoices.client_id, invoices.exchange_rate')->join('invoices', 'partial_payments.unique_invoice_id = invoices.unique_id')->get_where('partial_payments', $where)->result_array();
    }

    function getPartialPaymentDetails($key, $unique_invoice_id, $create_if_not_exists = false) {
        $buffer = $this->db->select('partial_payments.*, currency_id')->join('invoices', 'partial_payments.unique_invoice_id = invoices.unique_id')->get_where($this->table, array('unique_invoice_id' => $unique_invoice_id, 'key' => $key))->row_array();
        if (!isset($buffer['unique_id']) and $create_if_not_exists) {
	    # This part does not exist, so let's create it, for editing.
	    $this->setPartialPayment($unique_invoice_id, $key, 0, 1, 0, '', true);
	    return $this->getPartialPaymentDetails($key, $unique_invoice_id, $create_if_not_exists);
	}
	return array(
            'unique_id' => $buffer['unique_id'],
            'gateway' => $buffer['payment_method'],
            'date' => ($buffer['payment_date'] == '0') ? '' : format_date($buffer['payment_date']),
            'tid' => $buffer['txn_id'],
	    'fee' => $buffer['transaction_fee'],
            'status' => $buffer['payment_status'],
	    'amount' => $buffer['amount'],
	    'currency' => Currency::symbol(Currency::code($buffer['currency_id']))
        );
    }

    function getPartialPayment($unique_id) {
        $buffer = $this->db->get_where($this->table, array('unique_id' => $unique_id))->row_array();
        $CI = &get_instance();
        $CI->load->model('invoices/invoice_m');
        if (isset($buffer['unique_invoice_id'])) {
            $buffer['invoice'] = $CI->invoice_m->get($buffer['unique_invoice_id']);

            if ($buffer['invoice']['type'] == 'DETAILED') {
                $tax = $buffer['invoice']['total'] - $buffer['invoice']['amount'];
            } else {
                $tax = 0;
            }

            $moneyAmount = ($buffer['is_percentage']) ? ( ($buffer['amount'] / 100) * $buffer['invoice']['amount'] ) : $buffer['amount'];
            $percentageAmount = ($buffer['is_percentage']) ? ($buffer['amount'] / 100) : ($buffer['amount'] / $buffer['invoice']['amount']);
            $taxAmount = $tax * $percentageAmount;

            $buffer['billableAmount'] = $moneyAmount + $taxAmount;
            $buffer['payment_url'] = site_url('transaction/process/' . $buffer['unique_id']);
        }
        return $buffer;
    }

    function updatePartialPayment($unique_id, $data) {
        return $this->db->where('unique_id', $unique_id)->update($this->table, $data);
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

    public function removePartialPayments($unique_invoice_id) {
        return $this->db->where('unique_invoice_id', $unique_invoice_id)->delete($this->table);
    }

    /**
     * Creates (or, if it already exists, updates) a partial payment for an invoice.
     * 
     * $key is the index of the payment part, starting from 1.
     * $due_date is a UNIX timestamp.
     * The rest should be self-explanatory.
     * 
     * Returns an integer (the ID of the payment part) if it is created successfully,
     * true if it is already existed and was updated,
     * and false if anything went wrong.
     * 
     * @param string $unique_invoice_id
     * @param integer $key
     * @param double $amount
     * @param boolean $is_percentage
     * @param integer $due_date
     * @param string $notes
     * @param boolean $is_paid
     * @return integer|boolean 
     */
    function setPartialPayment($unique_invoice_id, $key, $amount, $is_percentage, $due_date, $notes = '', $force = false) {

        $where = array(
            'unique_invoice_id' => $unique_invoice_id,
            'key' => $key
        );
        
        $exists = $this->db->select('unique_id')->where($where)->get($this->table)->row_array();
        $unique_id = (isset($exists['unique_id']) and !empty($exists['unique_id'])) ? $exists['unique_id'] : $this->_generate_unique_id();
        $exists = (isset($exists['unique_id']) and !empty($exists['unique_id']));
        
        $data = array(
            'unique_invoice_id' => $unique_invoice_id,
            'amount' => $amount,
            'key' => $key,
            'is_percentage' => $is_percentage,
            'due_date' => $due_date,
            'notes' => $notes,
            'unique_id' => $unique_id
        );

        if (!$exists) {
            if ($amount > 0 or $force) {
                return $this->db->insert($this->table, $data);
            } else {
                # The amount is empty, this partial payment can go to hell.
                return true;
            }
        } else {
            if ($amount > 0 or $force) {
                return $this->db->where($where)->update($this->table, $data);
            } else {
                # The amount is empty, let's delete this partial payment.
                return $this->db->where($where)->delete($this->table);
            }
        }
    }

    function setPartialPaymentDetails($invoice_unique_id, $key, $payment_date, $gateway, $status, $txn_id, $fee = 0) {
        $part = $this->getPartialPaymentDetails($key, $invoice_unique_id);
        $is_paid = (!empty($status) or !empty($gateway));
	
        if ($is_paid) {
            # Date's gotta be bigger than 0.
            # If it is NOT a UNIX Timestamp, turn it into one.
            # Obvious, huh?
	    
            $date = (!is_numeric($payment_date)) ? strtotime($payment_date) : $payment_date;
            $date = ($date == 0) ? time() : $date;
	    
        } else {
            # Date's gotta be 0, because no payment was made yet.
            $date = 0;
        }
        
        
        $data = array(
            'is_paid' => $is_paid,
            'payment_status' => (!empty($gateway) and empty($status)) ? 'Completed' : $status,
            'payment_method' => $gateway,
            'payment_date' => $date,
            'txn_id' => $txn_id,
	    'transaction_fee' => $fee
        );
        $this->ppm->updatePartialPayment($part['unique_id'], $data);
        $this->invoice_m->fixInvoiceRecord($invoice_unique_id);
    }

    /**
     * This function resets the keys of the partial payments to avoid problems when parts are deleted.
     * @param string $unique_invoice_id 
     */
    function organiseInvoicePartialPayments($unique_invoice_id) {
        $parts = $this->db->order_by('key', 'ASC')->get_where($this->table, array('unique_invoice_id' => $unique_invoice_id))->result_array();
        $i = 1;
        $remaining = count($parts);
        while ($remaining >= $i) {
            $this->db->where('id', $parts[$i - 1]['id'])->update($this->table, array('key' => $i));
            $i++;
        }
    }

    function deleteInvoicePartialPayments($unique_invoice_id, $exceptFirst = false) {
        if ($exceptFirst) {
            $this->db->where('key !=', 1);
        }
        return $this->db->where('unique_invoice_id', $unique_invoice_id)->delete($this->table);
    }

    /**
     * Processes POST input from the invoice form.
     * 
     * Returns 'WRONG_TOTAL' if the total does not match 100% of the invoice's costs.
     * Returns true if everything went well.
     * Returns false if it failed at creating/updating one of the partial payments.
     * 
     * In the case anything goes wrong, all partial payments are removed, to prevent orphans.
     *
     * @param string $unique_invoice_id
     * @param double $invoice_total
     * @param array $partial_amounts
     * @param array $partial_is_percentages
     * @param array $partial_due_dates
     * @param array $partial_notes
     * @param array $partial_is_paids 
     */
    function processInput($unique_invoice_id, $partial_amounts, $partial_is_percentages, $partial_due_dates, $partial_notes, $partial_is_paids) {
        
        # Let's organise the data properly. I hate dealing with one array for each field.
        # We also process it for insertion in the DB.
        # And we also check that the amounts match 100%.
        # Oh, and we insert it in the DB, too.

        $partials = array();
        $invoice_total = $this->getInvoiceTotalAmount($unique_invoice_id);
        $invoice_is_recurring = $this->invoice_m->getIsRecurringByUniqueId($unique_invoice_id);
        $total_amount = 0;

        foreach ($partial_amounts as $key => $amount) {
            
            if ($invoice_is_recurring) {
                if ($key > 1) {
                    # Only one part allowed, ignore all the rest!
                    break;
                } else {
                    # First part, let's make sure the amount/percentage is correct.
                    # We could just change (sanitize) it, but that might be unexpected for the user.
                    if ($amount != 100 or $partial_is_percentages[$key] != 1) {
                        return 'WRONG_TOTAL';
                    }
                }
            }
            
            $partials[$key] = array(
                'amount' => $amount,
                'is_percentage' => ($partial_is_percentages[$key] == "1") ? true : false,
                'due_date' => read_date_picker($partial_due_dates[$key]),
                'notes' => $partial_notes[$key]
            );

            # We calculate the money amount. If it's a percentage, we calculate that against the invoice total.
            $moneyAmount = ($partials[$key]['is_percentage']) ? ( ($amount / 100) * $invoice_total ) : $amount;
            $total_amount = $total_amount + $moneyAmount;

            if ($total_amount > $invoice_total) {
                # Okay, it's wrong. Let's quit.
                return 'WRONG_TOTAL';
            }
        }

        if ($total_amount < $invoice_total) {
            # It's wrong, too. Let's quit.
            return 'WRONG_TOTAL';
        }

        # Everything's processed, and the total is correct. Let's go and put it all in the DB.
        foreach ($partials as $key => $partial) {
            if (!$this->setPartialPayment($unique_invoice_id, $key, $partial['amount'], $partial['is_percentage'], $partial['due_date'], $partial['notes'])) {
                # Something wrong happened, so let's just stop this.
                $this->deleteInvoicePartialPayments($unique_invoice_id);
                return false;
            }
        }
        
        if ($invoice_is_recurring) {
            # We also need to delete the rest of the parts, only part one is allowed to stay!
            $this->deleteInvoicePartialPayments($unique_invoice_id, true);
        }

        $this->organiseInvoicePartialPayments($unique_invoice_id);

        # Everything worked perfectly! Jolly good show, old chap.
        return true;
    }

    function getInvoicePaidAmount($unique_invoice_id) {
        $total_amount = 0;
        $invoice_total = $this->getInvoiceTotalAmount($unique_invoice_id);

        foreach ($this->getInvoicePartialPayments($unique_invoice_id) as $row) {
            if ($row['is_paid']) {
                # We calculate the money amount. If it's a percentage, we calculate that against the invoice total.
                $moneyAmount = (($row['is_percentage']) ? ( ($row['amount'] / 100) * $invoice_total ) : $row['amount']);
                $total_amount = $total_amount + $moneyAmount;
            }
        }

        return $total_amount;
    }

    function getInvoiceTotalAmount($unique_invoice_id) {
        $CI = &get_instance();
        $CI->load->model('invoices/invoice_m');
        $invoice = $CI->invoice_m->flexible_get_all(array('unique_id' => $unique_invoice_id, 'include_totals' => false, 'return_object' => false, 'get_single' => true, 'include_partials' => false));
        return $invoice['amount'];
    }
    
    function getIdByUniqueId($unique_id) {
	$buffer = $this->select('id')->where('unique_id', $unique_id)->get($this->table);
	return (isset($buffer['id']) ? $buffer['id'] : false);
    }
    
    function getUniqueInvoiceIdByUniqueId($unique_id) {
	$buffer = $this->db->select('unique_invoice_id')->where('unique_id', $unique_id)->get($this->table)->row_array();
	return (isset($buffer['unique_invoice_id']) ? $buffer['unique_invoice_id'] : false);
    }

    function getInvoiceUnpaidAmount($unique_invoice_id) {
        $total_amount = 0;
        $invoice_total = $this->getInvoiceTotalAmount($unique_invoice_id);

        foreach ($this->getInvoicePartialPayments($unique_invoice_id) as $row) {
            if (!$row['is_paid']) {
                # We calculate the money amount. If it's a percentage, we calculate that against the invoice total.
                $moneyAmount = (($row['is_percentage']) ? ( ($row['amount'] / 100) * $invoice_total ) : $row['amount']);
                $total_amount = $total_amount + $moneyAmount;
            }
        }

        return $total_amount;
    }

    function getInvoiceIsPaid($unique_invoice_id) {
        foreach ($this->getInvoicePartialPayments($unique_invoice_id) as $row) {
            if (!$row['is_paid']) {
                return false;
            }
        }

        # All partial payments are paid, so the invoice is paid.
        return true;
    }

    /**
     * Get all parts of the payments for a given invoice.
     * 
     * Appends a column to the results, called 'billableAmount', 
     * which is the amount that the client is getting charged,
     * in cash. No percentages, no nothing. Tax included.
     * 
     * It also appends payment_url, self-explanatory, and over_due, also self-explanatory.
     * It also appends due_date_input, which is the due date in a format suitable for
     * displaying in the create/edit invoice pages.
     * 
     * @param string $unique_invoice_id
     * @param float $invoice_total
     * @return array 
     */
    function getInvoicePartialPayments($unique_invoice_id, $invoice_total = 0, $tax = 0) {
        $buffer = $this->db->order_by($this->table . '.key', 'ASC')->get_where($this->table, array('unique_invoice_id' => $unique_invoice_id))->result_array();
        $return = array();

        foreach ($buffer as $row) {
            $row['due_date_input'] = $row['due_date'] > 0 ? format_date($row['due_date']) : '';
            if ($invoice_total) {

                $moneyAmount = ($row['is_percentage']) ? ( ($row['amount'] / 100) * $invoice_total ) : $row['amount'];
                $percentageAmount = ($row['is_percentage']) ? ($row['amount'] / 100) : ($row['amount'] / $invoice_total);
                $taxAmount = $tax * $percentageAmount;
                $row['billableAmount'] = $moneyAmount + $taxAmount;
            }
            $row['payment_url'] = site_url('transaction/process/' . $row['unique_id']);
            $row['over_due'] = $row['due_date'] < time();
            $return[$row['key']] = $row;
        }
        return $return;
    }

    function countInvoicePartialPayments($unique_invoice_id) {
        return $this->db->where(array('unique_invoice_id' => $unique_invoice_id))->count_all_results($this->table);
    }

}