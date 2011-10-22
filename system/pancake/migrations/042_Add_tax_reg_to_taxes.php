<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_tax_reg_to_taxes extends CI_Migration {
    
	public function up()
	{
	    $result = $this->db->query("SHOW COLUMNS FROM " . $this->db->dbprefix('taxes') . " LIKE 'reg'")->row_array();
	    
		if ( ! isset($result['Field']) or $result['Field'] != 'reg')
		{
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
    
	public function down()
	{
		$this->dbforge->drop_column('taxes', 'reg');
    }
}