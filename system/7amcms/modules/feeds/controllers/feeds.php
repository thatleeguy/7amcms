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
 * The Feeds controller
 *
 * @subpackage	Controllers
 * @category	Frontend
 */
class Feeds extends Public_Controller
{

	/**
	 * The construct doesn't do anything useful right now.
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct()
	{
		parent::Public_Controller();
		$this->load->helper('xml');
	}

	// ------------------------------------------------------------------------

	/**
	 * Creates the Overdue RSS feed
	 *
	 * @access	public
	 * @param	int		The number of invoices to show
	 * @param	string	The rss password
	 * @return	void
	 */
	public function overdue($limit = 10, $password = '')
	{
		if (PAN::setting('rss_password') !== $password)
		{
			show_error('Access Denied');
		}
		$this->load->model('invoices/invoice_m');
		$this->db->limit($limit);
		$data['invoices'] = $this->invoice_m->get_all_overdue();
		$data['list_title'] = "Overdue Invoices";
        $data['feed_name'] = 'Pancake Payments - Overdue Invoices';
        $data['page_description'] = 'Your overdue invoices.';
		$this->_output_feed($data);
	}

	// ------------------------------------------------------------------------

	/**
	 * Creates the RSS feed for unpaid invoices
	 *
	 * @access	public
	 * @param	int		The number of invoices to show
	 * @param	string	The rss password
	 * @return	void
	 */
	public function unpaid($limit = 10, $password = '')
	{
		if (PAN::setting('rss_password') !== $password)
		{
			show_error('Access Denied');
		}
		$this->load->model('invoices/invoice_m');
		$this->db->limit($limit);
		$data['invoices'] = $this->invoice_m->get_all_unpaid();
		$data['list_title'] = "Unpaid Invoices";
        $data['feed_name'] = 'Pancake Payments - Unpaid Invoices';
        $data['page_description'] = 'Your unpaid invoices.';
		$this->_output_feed($data);
	}

	// ------------------------------------------------------------------------

	/**
	 * Creates the RSS feed for paid invoices
	 *
	 * @access	public
	 * @param	int		The number of invoices to show
	 * @param	string	The rss password
	 * @return	void
	 */
	public function paid($limit = 10, $password = '')
	{
		if (PAN::setting('rss_password') !== $password)
		{
			show_error('Access Denied');
		}
		$this->load->model('invoices/invoice_m');
		$this->db->limit($limit);
		$data['invoices'] = $this->invoice_m->get_all_paid();
		$data['list_title'] = "Paid Invoices";
        $data['feed_name'] = 'Pancake Payments - Paid Invoices';
        $data['page_description'] = 'Your paid invoices.';
		$this->_output_feed($data);
	}

	// ------------------------------------------------------------------------

	/**
	 * Outputs the RSS Feed
	 *
	 * @access	public
	 * @param	int		The number of invoices to show
	 * @param	string	The rss password
	 * @return	void
	 */
	private function _output_feed($data)
	{
		$data['encoding'] = 'utf-8';
        $data['feed_url'] = BASE_URL;
        $data['page_language'] = 'en';
        $data['creator_email'] = PAN::setting('notify_email');
        header("Content-Type: application/rss+xml");
		$this->load->view('feed', $data);
	}

}

/* End of file frontend.php */