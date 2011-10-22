<?php

defined('BASEPATH') OR exit('No direct script access allowed');

# Don't edit this file set your email sending settings in Pancake instead.
# Trust me, it's much easier.

$config['mailtype'] = 'html';
$config['charset'] = 'utf-8';
$config['crlf'] = "\r\n";
$config['newline'] = "\r\n";

$config['protocol'] = Settings::get('email_type');

if ($config['protocol'] == 'sendmail') {
    $config['mailpath'] = Settings::get('mailpath');
} elseif ($config['protocol'] == 'smtp') {
    $config['smtp_host'] = Settings::get('smtp_host');
    $config['smtp_user'] = Settings::get('smtp_user');
    $config['smtp_pass'] = Settings::get('smtp_pass');
    $config['smtp_port'] = Settings::get('smtp_port');
}

/* End of file: email.php */