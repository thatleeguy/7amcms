<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_items extends CI_Migration {
    
	public function up()
	{
		if ( ! $this->db->table_exists('items'))
		{
			$this->db->query("CREATE TABLE ".$this->db->dbprefix('items')." (
	          `id` int(11) NOT NULL AUTO_INCREMENT,
	          `name` varchar(255) NOT NULL,
	          `description` text NOT NULL,
	          `qty` float unsigned NOT NULL DEFAULT '1',
	          `rate` float unsigned NOT NULL DEFAULT '0',
	          `tax_id` int(11) NOT NULL,
	          PRIMARY KEY (`id`)
	        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		}
		
		// Add name field to invoices
		$result = $this->db->query("SHOW COLUMNS FROM " . $this->db->dbprefix('invoice_rows') . " LIKE 'name'")->row_array();
	    
		if ( ! isset($result['Field']) or $result['Field'] != 'name')
		{
			$this->dbforge->add_column('invoice_rows', array(
			    'name' => array(
					'type' => 'varchar',
					'constraint' => 255,
					'null' => FALSE,
					'default' => '',
			    ),
			));
	    }
	
		// Populate Names with Descriptions
		$this->db->query('UPDATE '.$this->db->dbprefix('invoice_rows').' SET name = description, description = ""');
	
    }
    
	public function down()
	{
		$this->dbforge->drop_table('items');
    }
}