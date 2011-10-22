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
 * The frontend controller
 *
 * @subpackage	Controllers
 * @category	Frontend
 */
class Files extends Public_Controller
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
	}

	// ------------------------------------------------------------------------

	public function download($unique_id, $file_id)
	{
		$this->load->model('files_m');
		$this->load->helper('download');

		$file = $this->files_m->get_by(array('invoice_unique_id' => $unique_id, 'id' => $file_id));

		if (empty($file))
		{
			show_error('File not found.');
		}

		if ( ! is_file('uploads/'.$file->real_filename))
		{
			show_error('File is in the database but missing from the file system.');
		}
		force_download($file->orig_filename, file_get_contents('uploads/'.$file->real_filename));
	}
}

/* End of file files.php */