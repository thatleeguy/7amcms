<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Fix_currency_issue_for_can_upgraders extends CI_Migration {

    public function up() {
	if ($this->db->where('slug', 'currency')->where('value', 'CAN')->count_all_results('settings') != 0) {
	    $this->db->where('slug', 'currency')->update('settings', array('value' => 'CAD'));
	}
    }

    public function down() {
	if ($this->db->where('slug', 'currency')->where('value', 'CAD')->count_all_results('settings') != 0) {
	    $this->db->where('slug', 'currency')->update('settings', array('value' => 'CAN'));
	}
    }

}