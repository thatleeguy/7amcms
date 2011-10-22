<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Move_email_config_to_db extends CI_Migration {

    public function up() {
	$config = array();
	include APPPATH.'config/email.php';
	
	if (isset($config['protocol'])) {
	    $type = $config['protocol'];
	} else {
	    $type = 'mail';
	}
	
	if (isset($config['smtp_host'])) {
	    $host = $config['smtp_host'];
	} else {
	    $host = '';
	}
	
	if (isset($config['smtp_user'])) {
	    $user = $config['smtp_user'];
	} else {
	    $user = '';
	}
	
	if (isset($config['smtp_pass'])) {
	    $pass = $config['smtp_pass'];
	} else {
	    $pass = '';
	}
	
	if (isset($config['smtp_port'])) {
	    $port = $config['smtp_port'];
	} else {
	    $port = '';
	}
	
	if (isset($config['mailpath'])) {
	    $mailpath = $config['mailpath'];
	} else {
	    $mailpath = '/usr/sbin/sendmail';
	}
	
	Settings::create('email_type', 'mail'); # googleapps, gmail, smtp, mail, sendmail
	Settings::create('smtp_host', $host);
	Settings::create('smtp_user', $user);
	Settings::create('smtp_pass', $pass);
	Settings::create('smtp_port', $port);
	Settings::create('mailpath', $mailpath);
	
    }

    public function down() {
	Settings::delete('email_type');
	Settings::delete('smtp_host');
	Settings::delete('smtp_user');
	Settings::delete('smtp_pass');
	Settings::delete('smtp_port');
	Settings::delete('mailpath');
    }

}