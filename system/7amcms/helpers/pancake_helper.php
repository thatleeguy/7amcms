<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Throws a 403 and displays an Access Denied error message.
 *
 * @access	public
 * @return	void
 */
function access_denied() {
    show_error(__('You do not have permission to access this page.'), 403, __('Access Denied'));

    // show_error() will exit, but just in-case...
    exit;
}

// --------------------------------------------------------------------

/**
 * Sets the appropriate JSON header, the status header, then
 * encodes the output and exits execution with the JSON.
 *
 * @access	public
 * @param	mixed	The output to encode
 * @param	int		The status header
 * @return	void
 */
function output_json($output, $status = 200) {
    if (headers_sent()) {
        show_error(__('Headers have already been sent.'));
    }

    PAN::$CI->output->set_status_header($status);
    PAN::$CI->output->set_header('Content-type: application/json');
    exit(json_encode($output));
}

function logo($img_only = false, $anchor = true, $h = 1) {
    $logo  = Settings::get('logo_url');
    $title = Settings::get('site_name');
    if (empty($logo)) {
	$anchor = $anchor ? anchor('admin', $title) : $title;
        return $img_only ? '' : "<h".$h." class='logo'>".$anchor."</h".$h.">";
    } else {
	$logo = "<img src='$logo' style='height:106px;' alt='$title' />";
	$anchor = $anchor ? '<a href="'.site_url('admin').'">'.$logo.'</a>' : $logo;
        return "<div class='img-logo'>$anchor</div>";
    }
}

/**
 * Replaces PHP's file_get_contents in URLs, to get around the allow_url_fopen limitation.
 * Still loads regular files using file_get_contents.
 * 
 * @param string $url
 * @return string 
 */
function get_url_contents($url, $redirect = true) {
    if (substr($url, 0, 7) != 'http://') {
	return file_get_contents($url);
    } else {
	include_once APPPATH.'libraries/HTTP_Request.php';
	$http = new HTTP_Request();
	try {
	    $result = $http->request($url);
	} catch (Exception $e) {
	    deal_with_no_internet($redirect);
	    return '';
	}
	$result = trim($result);
	return $result;
    }
}

/**
 * Redirects to the no_internet_access page if $redirect is true (which is only true in PDFs), or if a firewall is blocking external resource access completely.
 * Else, defines TEMPORARY_NO_INTERNET_ACCESS which is used in the admin layout, to show a subtle "no internet access" notification.
 * 
 * @param boolean $redirect 
 */
function deal_with_no_internet($redirect = false) {
    # Firstly, let's check if it's temporary or permanent.
    # If it's temporary (user is on localhost and not connected to the internet), then it's fine.
    # If it's permanent (firewall is blocking PHP from accessing external resources), then it's not fine, and the user NEEDS to fix it.
    # To check, we try to access the Pancake index page. If on localhost and not connected to the internet, that page should still be accessible.
    # If firewall is blocking, that page will never be accessible.
    
    if ($redirect) {
	redirect('no_internet_access');
    }
    
    include_once APPPATH . 'libraries/HTTP_Request.php';
    $http = new HTTP_Request();
    $url = site_url('third_party');
    try {
	$result = $http->request($url);
	$temporary = true;
    } catch (Exception $e) {
	$temporary = false;
    }
    
    if ($temporary) {
	defined('TEMPORARY_NO_INTERNET_ACCESS') or define('TEMPORARY_NO_INTERNET_ACCESS', true);
    } else {
	redirect('no_internet_access');
    }
}

/**
 * Sends an email as given, without doing any processing.
 * 
 * BCCs the email if it's being sent to a client and the BCC setting is turned on.
 * 
 * If $from is not provided, the notify_email will be used.
 * 
 * @param string $to
 * @param string $subject
 * @param string $message
 * @param string $from
 * @return boolean 
 */
function send_pancake_email_raw($to, $subject, $message, $from = null) {
    
    $CI = &get_instance();
    $CI->load->library('email');
    
    if (!empty($from)) {
	$CI->email->from($from);
    } else {
	$CI->email->from(Settings::get('notify_email'), Settings::get('site_name'));
    }
    
    $CI->email->to($to);
    
    $CI->email->subject($subject);
    $CI->email->message(str_ireplace('{bcc}', '', $message));
    $result = $CI->email->send();
    $CI->email->clear();
    
    if ($to != Settings::get('notify_email') and Settings::get('bcc')) {
	# It's for a client, let's BCC this stuff.
	$date = format_date(time());
	send_pancake_email_raw(Settings::get('notify_email'), $subject, str_ireplace('{bcc}', 'This email was sent to '.$to.' on '.$date.'<br /><hr /><br />', $message));
    }
    
    return $result;
}

function add_column($table, $name, $type, $constraint, $default = '') {
    $CI = &get_instance();
    $result = $CI->db->query("SHOW COLUMNS FROM " . $CI->db->dbprefix($table) . " LIKE '{$name}'")->row_array();
    if (!isset($result['Field']) or $result['Field'] != $name) {
	return $CI->dbforge->add_column($table, array(
	    $name => array(
		'type' => $type,
		'constraint' => $constraint,
		'null' => FALSE,
		'default' => $default,
	    ),
	));
    }
}

function pancake_lower_than($version, $current = null) {
    
    if ($current == null) {
	$current = Settings::get('version');
    }
    
    $versions = array('1.1', '1.1.1', '1.1.2', '1.1.3', '1.1.4', '2.0', '2.0.1', '2.0.2', '2.1.0', '3.0.0', '3.0.2', '3.1.0', '3.1.1', '3.1.2', '3.1.3', '3.1.31', '3.1.4');
    
    if (in_array($version, $versions)) {
	if (in_array($current, $versions)) {
	    # It's one of the old versions.
	    
	    $found_wanted  = false;
	    
	    foreach ($versions as $v) {
		if ($v == $version) {
		    if ($found_wanted == false) {
			# It found the current version before it found the wanted version, so it's lower than.
			return true;
		    } else {
			# It found the current version AFTER it found the wanted version, so it's higher than.
			return false;
		    }
		}
		
		if ($v == $current) {
		    $found_wanted = true;
		}
	    }
	} else {
	    # It's one of the new versions, you can calculate using maths.
	    
	    $format = '%1$03d';

	    $number = explode('.', $version);
	    $i = 0;
	    $version = '';
	    foreach ($number as $part) {
		if ($i != 0) {
		    $part = sprintf($format, $part);
		} else {
		    $i++;
		}

		$version .= $part;
	    }
	    
	    $version = (int) $version;
	    
	    $number = explode('.', $current);
	    $i = 0;
	    $current = '';
	    foreach ($number as $part) {
		if ($i != 0) {
		    $part = sprintf($format, $part);
		} else {
		    $i++;
		}

		$current .= $part;
	    }
	    
	    $current = (int) $current;
	    
	    return ($current < $version);
	    
	}
    } else {
	trigger_error("Pancake Version $version does not exist. Please contact support@pancakeapp.com with this message.");
    }
    
}

/* End of file: pancake_helper.php */