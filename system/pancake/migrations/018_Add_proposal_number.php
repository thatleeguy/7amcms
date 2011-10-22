<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_proposal_number extends CI_Migration {
	
    function up() {
        $result = $this->db->query("SHOW COLUMNS FROM " . $this->db->dbprefix('proposals') . " LIKE 'proposal_number'")->row_array();
        if (!isset($result['Field']) or $result['Field'] != 'proposal_number') {
            $this->dbforge->add_column('proposals', array(
                'proposal_number' => array(
                    'type' => 'int',
                    'constraint' => 20,
                    'null' => FALSE,
                    'default' => 0,
                ),
            ));
        }
        
        $this->db->query('UPDATE '.$this->db->dbprefix('proposals').' SET proposal_number = id');
    }
    
    function down() {
    }
}