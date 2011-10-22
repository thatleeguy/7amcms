<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_invoice_sent_recur_dates extends CI_Migration {

    public function up() {
        
        $result = $this->db->query("SHOW COLUMNS FROM " . $this->db->dbprefix('invoices') . " LIKE 'last_sent'")->row_array();
        if (!isset($result['Field']) or $result['Field'] != 'last_sent') {
            $this->dbforge->add_column('invoices', array(
                'last_sent' => array(
                    'type' => 'int',
                    'constraint' => 10,
                    'null' => FALSE,
                    'default' => 0,
                ),
            ));
        }
        
        $result = $this->db->query("SHOW COLUMNS FROM " . $this->db->dbprefix('invoices') . " LIKE 'next_recur_date'")->row_array();
        if (!isset($result['Field']) or $result['Field'] != 'next_recur_date') {
            $this->dbforge->add_column('invoices', array(
                'next_recur_date' => array(
                    'type' => 'int',
                    'constraint' => 10,
                    'null' => FALSE,
                    'default' => 0,
                ),
            ));
        }
    }

    public function down() {
        
    }

}