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
 * The Settings Model
 *
 * @subpackage	Models
 * @category	Settings
 */
class Settings_m extends Breakfast_Model
{
	/**
	 * @var string	The name of the settings table
	 */
	protected $table = 'settings';

	/**
	 * @var string	The primary key
	 */
	protected $primary_key = 'slug';

	/**
	 * @var bool	Tells the model to skip auto validation
	 */
	protected $skip_validation = TRUE;

	public function update_settings($settings)
	{
            
		$this->db->trans_begin();

		foreach ($settings as $slug => $value)
		{
			// This ensures we are only updating what has changed
			if (PAN::setting($slug) != $value)
			{
				$this->db->where('slug', $slug)->update($this->table, array('value' => $value));
			}
		}

		if ($this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();
			return FALSE;
		}

		$this->db->trans_commit();
		return TRUE;
	}
        
        function store_logo($logo) {
	    if (isset($logo['error'][0]) and $logo['error'][0] == UPLOAD_ERR_OK) {
		$this->clear_logo();
		$CI = &get_instance();
		$CI->load->model('files/files_m');
		$buffer = $CI->files_m->upload($logo, 'settings');
		reset($buffer);
		return current($buffer);
	    } else {
		return false;
	    }
        }
	
	function clear_logo() {
	    $this->load->helper('directory');
	    if ($map = directory_map(FCPATH.'uploads/branding/', 1))
		{
	    	foreach ($map as $file)
			{
				@unlink(FCPATH.'uploads/branding/'.$file);
	    	}
		}
	    Settings::set('logo_url', '');
	}
	
	function interpret_email_settings() {
	    $type     = Settings::get('email_type');
	    $host     = Settings::get('smtp_host');
	    $user     = Settings::get('smtp_user');
	    $pass     = Settings::get('smtp_pass');
	    $port     = Settings::get('smtp_port');
	    $mailpath = Settings::get('mailpath');
	    $gmail_user = '';
	    $gmail_pass = '';
	    $gapps_user = '';
	    $gapps_pass = '';
	    
	    $not_gmail_domain = (stristr($user, 'gmail.com') === false and stristr($user, 'googlemail.com') === false);
	    
	    if (stristr($host, 'gmail.com') !== false or stristr($host, 'googlemail.com') !== false) {
		$type = $not_gmail_domain ? 'gapps' : 'gmail';
		$host = '';
		if ($not_gmail_domain) {
		    $gapps_pass = $pass;
		    $gapps_user = $user;
		} else {
		    $gmail_pass = $pass;
		    $gmail_user = $user;
		}
		$user = '';
		$pass = '';
		$port = 25;
	    } else {
		$port = empty($port) ? 25 : $port;
	    }
	    
	    return array(
		'type' => $type,
		'smtp_host' => $host,
		'smtp_user' => $user,
		'smtp_pass' => $pass,
		'smtp_port' => $port,
		'gmail_user' => $gmail_user,
		'gmail_pass' => $gmail_pass,
		'gapps_user' => $gapps_user,
		'gapps_pass' => $gapps_pass,
		'mailpath' => $mailpath,
	    );
	}
	
	function save_email_settings($email) {
	    
	    $type     = $email['email_server'];
	    $host     = $email['smtp_host'];
	    $user     = $email['smtp_user'];
	    $pass     = $email['smtp_pass'];
	    $port     = $email['smtp_port'];
	    $mailpath = $email['mailpath'];
	    $gmail_user = $email['gmail_user'];
	    $gmail_pass = $email['gmail_pass'];
	    $gapps_user = $email['gapps_user'];
	    $gapps_pass = $email['gapps_pass'];
	    
	    if ($type == 'gmail' or $type == 'gapps') {
		# Needs OpenSSL.
		if (!extension_loaded('openssl')) {
		    return 'no_openssl';
		}
		
		$type = 'smtp';
		$host = 'ssl://smtp.gmail.com';
		$user = ($type == 'gmail') ? $gmail_user : $gapps_user;
		$pass = ($type == 'gmail') ? $gmail_pass : $gapps_pass;
		$port = 465;
	    } else {
		$port = empty($port) ? 25 : $port;
	    }
	    
	    Settings::set('email_type', $type); 
	    Settings::set('smtp_host', $host);
	    Settings::set('smtp_user', $user);
	    Settings::set('smtp_pass', $pass);
	    Settings::set('smtp_port', $port);
	    Settings::set('mailpath', $mailpath);
	    
	    return true;
	}
        
        function get_languages() {
            $var = scandir(APPPATH.'language/');
            $ret = array();
            foreach ($var as $row) {
                if ($row != '.' and $row != '..') {
                    $ret[$row] = ucwords($row);
                }
            }
            return $ret;
        }

}

/* End of file: settings_m.php */