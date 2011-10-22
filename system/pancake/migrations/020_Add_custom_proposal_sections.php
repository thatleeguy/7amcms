<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_custom_proposal_sections extends CI_Migration {
	
    function up() {
        $result = $this->db->query("SHOW COLUMNS FROM " . $this->db->dbprefix('proposal_sections') . " LIKE 'section_type'")->row_array();
        if (!isset($result['Field']) or $result['Field'] != 'section_type') {
            $this->dbforge->add_column('proposal_sections', array(
                'section_type' => array(
                    'type' => 'varchar',
                    'constraint' => 128,
                    'null' => FALSE,
                    'default' => '',
                ),
            ));
        }
        
        $this->db->where('section_type', '')->update('proposal_sections', array('section_type' => 'section'));
    }
    
    function down() {
    }
}