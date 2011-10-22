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
		include APPPATH.'modules/gateways/gateway.php';
		$this->load->library('form_validation');
		$this->load->model('settings_m');
		$this->load->model('tax_m');
		$this->load->model('upgrade/update_system_m', 'update');
		$this->load->model('key_m');
		
		$this->update->get_latest_version(true);

		$this->form_validation->set_rules('site_name', 'Site Name', 'required');
		$this->form_validation->set_rules('notify_email', 'Notify Email', 'required|valid_email');
		$this->form_validation->set_rules('task_time_interval', 'Task Time Interval', 'required|numeric');

		$this->form_validation->set_error_delimiters('<span class="form_error">', '</span>');

		if ($this->form_validation->run())
		{
            require_once APPPATH.'modules/gateways/gateway.php';
            if (!Gateway::processSettingsInput($_POST['gateways']))
			{
                $this->template->messages = array('error' => lang('gateways:errorupdating'));
            }
            unset($_POST['gateways']);
	    
	    $save_email = $this->settings_m->save_email_settings($_POST);
	    
	    if ($save_email === 'no_openssl') {
		$this->template->messages = array('error' => lang('settings:noopenssl'));
	    } else {
		unset($_POST['email_server']);
		unset($_POST['smtp_host']);
		unset($_POST['smtp_user']);
		unset($_POST['smtp_pass']);
		unset($_POST['smtp_port']);
		unset($_POST['gapps_user']);
		unset($_POST['gapps_pass']);
		unset($_POST['gmail_user']);
		unset($_POST['gmail_pass']);
		unset($_POST['mailpath']);
	    }
	    
		    $_POST['ftp_pasv'] = isset($_POST['ftp_pasv']);
	    	$_POST['bcc'] = isset($_POST['bcc']);

		    if ( ! empty($_POST['ftp_user']))
			{
				$ftp_test = $this->update->test_ftp($_POST['ftp_host'], $_POST['ftp_user'], $_POST['ftp_pass'], $_POST['ftp_port'], $_POST['ftp_path'], $_POST['ftp_pasv']);
				if ( ! $ftp_test)
				{
			    	$this->template->messages = array('error' => $this->update->get_error());
				} else {
				    $_POST['ftp_path'] = (substr($_POST['ftp_path'], strlen($_POST['ftp_path']) - 1, 1) == '/') ? $_POST['ftp_path'] : $_POST['ftp_path'] . '/';
				}
		    }
		    
		    if (PANCAKE_DEMO and isset($_POST['license_key'])) {
			unset($_POST['license_key']);
		    }
		    
		    if (isset($_POST['license_key'])) {
			if (get_url_contents('http://manage.pancakeapp.com/verify/key/'.$_POST['license_key'], false) !== 'valid') {
			    $this->template->messages = array('error' => __('settings:wrong_license_key'));
			}
		    }
            
			// Taxes
			$tax_update = $this->tax_m->update_taxes($_POST['tax_name'], $_POST['tax_value'], $_POST['tax_reg']);
			$tax_insert = TRUE;
			if (isset($_POST['new_tax_name']))
			{
				$tax_insert = $this->tax_m->insert_taxes($_POST['new_tax_name'], $_POST['new_tax_value'], $_POST['new_tax_reg']);
			}

			unset($_POST['tax_name'], $_POST['tax_value'], $_POST['tax_reg'], $_POST['new_tax_name'], $_POST['new_tax_value'], $_POST['new_tax_reg']);

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
            
            if ($logo_store = $this->settings_m->store_logo($_FILES['logo']))
			{
                $_POST['logo_url'] = $logo_store;
            }
			
		    if ( ! isset($this->template->messages['error']) or empty($this->template->messages['error']))
			{
				if ($this->settings_m->update_settings($_POST) AND $tax_update AND $tax_insert)
				{
	            	$this->template->messages = array('success' => 'The settings have been updated.');
				}
				else
				{
					$this->template->messages = array('error' => 'There was an error updating your settings.  Please contact support.');
				}
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
			$currencies[$code] = $currency['name'].' ('.$code.')';
		}

        $this->template->languages = $this->settings_m->get_languages();
	
	if (PANCAKE_DEMO) {
	    # Hide license key in demo.
	    $settings['license_key'] = 'demo-license-key';
	}
		
		$this->template->currencies = $currencies;
		$this->template->settings = $settings;
		$this->template->api_keys = $this->key_m->get_all();
		
		$this->update->get_latest_version();
		$this->template->guessed_ftp_host = parse_url(site_url(), PHP_URL_HOST);
		$this->template->latest_version = Settings::get('latest_version');
		$this->template->outdated = ($this->template->latest_version != '0' and $this->template->latest_version != Settings::get('version'));
		
		$this->template->email_servers = array(
		    'gmail'    => 'Gmail',
		    'gapps'    => 'Google Apps',
		    'smtp'     => 'SMTP',
		    'mail'     => 'PHP mail()',
		    'sendmail' => 'sendmail'
		);
		
		$email = $this->settings_m->interpret_email_settings();
		
		$email = array(
		    'type' => isset($_POST['email_server']) ? $_POST['email_server'] : $email['type'],
		    'smtp_host' => isset($_POST['smtp_host']) ? $_POST['smtp_host'] : $email['smtp_host'],
		    'smtp_user' => isset($_POST['smtp_user']) ? $_POST['smtp_user'] : $email['smtp_user'],
		    'smtp_pass' => isset($_POST['smtp_pass']) ? $_POST['smtp_pass'] : $email['smtp_pass'],
		    'smtp_port' => isset($_POST['smtp_port']) ? $_POST['smtp_port'] : $email['smtp_port'],
		    'gmail_user' => isset($_POST['gmail_user']) ? $_POST['gmail_user'] : $email['gmail_user'],
		    'gmail_pass' => isset($_POST['gmail_pass']) ? $_POST['gmail_pass'] : $email['gmail_pass'],
		    'gapps_user' => isset($_POST['gapps_user']) ? $_POST['gapps_user'] : $email['gapps_user'],
		    'gapps_pass' => isset($_POST['gapps_pass']) ? $_POST['gapps_pass'] : $email['gapps_pass'],
		    'mailpath' => isset($_POST['mailpath']) ? $_POST['mailpath'] : $email['mailpath'],
		);
		
		$this->template->email = $email;
		$this->template->temporary_no_internet_access = defined('TEMPORARY_NO_INTERNET_ACCESS');
		
		if ($this->template->outdated)
		{
		    # Add changelog, list of conflicted files, etc.
		    $this->template->conflicted_files = $this->update->check_for_conflicts($this->template->latest_version);
		    $this->template->changelog = $this->update->get_processed_changelog($this->template->latest_version);
		}
		
		$this->template->build('index');
	}
	
	public function remove_logo()
	{
	    $this->settings_m->clear_logo();
	    $this->session->set_flashdata('success', __('settings:logoremoved'));
	    redirect('admin/settings#branding');
	}

}

/* End of file: admin.php */