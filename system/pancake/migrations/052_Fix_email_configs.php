<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Fix_email_configs extends CI_Migration {
    function up() {
        $this->load->model('settings/settings_m', 's');
	$s = $this->s->interpret_email_settings();
	if ($s['type'] == 'smtp' and empty($s['smtp_host'])) {
	    # This configuration was moved and emails were broken.
	    $s['email_server'] = 'mail';
	    $this->s->save_email_settings($s);
	}
    }
    
    function down() {
        
    }
}