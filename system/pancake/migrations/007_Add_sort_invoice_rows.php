<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_sort_invoice_rows extends CI_Migration {

    public function up()
	{
        $result = $this->db->query("SHOW COLUMNS FROM " . $this->db->dbprefix('invoice_rows') . " LIKE 'sort'")->row_array();
        if (!isset($result['Field']) or $result['Field'] != 'sort') {
            $this->dbforge->add_column('invoice_rows', array(
                'sort' => array(
                    'type' => 'smallint',
                    'constraint' => 4,
                    'null' => FALSE,
                    'default' => 0,
                ),
            ));
        }
    }

    public function down() {
        
    }

}