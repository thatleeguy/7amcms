<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_estimate_proposal_id extends CI_Migration {
    function up() {
        $result = $this->db->query("SHOW COLUMNS FROM " . $this->db->dbprefix('invoices') . " LIKE 'proposal_id'")->row_array();
        if (!isset($result['Field']) or $result['Field'] != 'proposal_id') {
            $this->dbforge->add_column('invoices', array(
                'proposal_id' => array(
                    'type' => 'int',
                    'constraint' => 20,
                    'null' => FALSE,
                    'default' => 0,
                ),
            ));
        }
    }
    
    function down() {
    }
}