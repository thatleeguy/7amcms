<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Fix_reg_install_issue extends CI_Migration {

    public function up() {
	# Add reg to all fresh 3.1.6 installations that are missing it. Doesn't do anything to other installations.
	$result = $this->db->query("SHOW COLUMNS FROM " . $this->db->dbprefix('taxes') . " LIKE 'reg'")->row_array();
	if (!isset($result['Field']) or $result['Field'] != 'reg') {
	    $this->dbforge->add_column('taxes', array(
		'reg' => array(
		    'type' => 'varchar',
		    'constraint' => 100,
		    'null' => FALSE,
		    'default' => '',
		),
	    ));
	}
    }

    public function down() {
	
    }

}