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
 * The admin controller for Settings
 *
 * @subpackage	Controllers
 * @category	Settings
 */
class Admin extends Admin_Controller
{
	/**
	 * Lets the user edit the settings
	 *
	 * @access	public
	 * @return	void
	 */
	public function index()
	{
		$this->load->library('form_validation');
		$this->load->model('settings_m');
		$this->load->model('tax_m');
		$this->load->model('key_m');

		$this->form_validation->set_rules('site_name', 'Site Name', 'required');
		$this->form_validation->set_rules('notify_email', 'Notify Email', 'required|valid_email');
		$this->form_validation->set_rules('task_time_interval', 'Task Time Interval', 'required|numeric');

		$this->form_validation->set_error_delimiters('<span class="form_error">', '</span>');

		if ($this->form_validation->run())
		{
			// Taxes
			$tax_update = $this->tax_m->update_taxes($_POST['tax_name'], $_POST['tax_value']);
			$tax_insert = TRUE;
			if (isset($_POST['new_tax_name']))
			{
				$tax_insert = $this->tax_m->insert_taxes($_POST['new_tax_name'], $_POST['new_tax_value']);
			}

			unset($_POST['tax_name'], $_POST['tax_value'], $_POST['new_tax_name'], $_POST['new_tax_value']);

			// Currencies

			if ($this->input->post('currency_name') AND $this->input->post('currency_code') AND $this->input->post('currency_rate'))
			{
				$this->currency_m->update_currencies($_POST['currency_name'], $_POST['currency_code'], $_POST['currency_rate']);
			}
			$currency_insert = TRUE;
			if ($this->input->post('new_currency_name'))
			{
				$currency_insert = $this->currency_m->insert_currencies($_POST['new_currency_name'], $_POST['new_currency_code'], $_POST['new_currency_rate']);
			}

			unset($_POST['currency_name'], $_POST['currency_code'], $_POST['currency_rate'], $_POST['new_currency_name'], $_POST['new_currency_code'], $_POST['new_currency_rate']);
			
			// API Keys


			if ($this->input->post('key_key') AND $this->input->post('key_note'))
			{
				$this->key_m->update_keys($this->input->post('key_key'), $this->input->post('key_note'));
			}
			if ($this->input->post('new_key'))
			{
				$this->key_m->insert_keys($this->input->post('new_key'), $this->input->post('new_key_note'));
			}

			unset($_POST['key_key'], $_POST['key_note'], $_POST['new_key'], $_POST['new_key_note']);
			
			if ($this->settings_m->update_settings($_POST) AND $tax_update AND $tax_insert)
			{
				$this->template->messages = array('success' => 'The settings have been updated.');
			}
			else
			{
				$this->template->messages = array('error' => 'There was an error updating your settings.  Please contact support.');
			}

			// Refresh the settings cache
			$this->settings->reload();
		}
		$settings = array();
		foreach($this->settings->get_all() as $name => $value)
		{
			$settings[$name] = set_value($name, $value);
		}

		// Populate currency dropdown
		$currencies = array();
		foreach (Currency::currencies() as $code => $currency)
		{
			$currencies[$code] = $currency['name'];
		}

		$this->template->currencies = $currencies;
		$this->template->settings = $settings;
		$this->template->api_keys = $this->key_m->get_all();
		$this->template->build('index');
	}

}

/* End of file: admin.php */