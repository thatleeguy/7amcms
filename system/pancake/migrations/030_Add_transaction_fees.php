<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_transaction_fees extends CI_Migration {

    function up() {
	$result = $this->db->query("SHOW COLUMNS FROM " . $this->db->dbprefix('partial_payments') . " LIKE 'transaction_fee'")->row_array();
	if (!isset($result['Field']) or $result['Field'] != 'transaction_fee') {
	    $this->dbforge->add_column('partial_payments', array(
		'transaction_fee' => array(
		    'type' => 'float',
		    'null' => FALSE,
		    'default' => 0,
		),
	    ));
	}
    }

    function down() {
	
    }

}