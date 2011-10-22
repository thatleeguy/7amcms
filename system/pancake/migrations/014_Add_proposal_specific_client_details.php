<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_proposal_specific_client_details extends CI_Migration {

    public function up() {
        $result = $this->db->query("SHOW COLUMNS FROM " . $this->db->dbprefix('proposals') . " LIKE 'client_company'")->row_array();
        if (!isset($result['Field']) or $result['Field'] != 'client_company') {
            $this->dbforge->add_column('proposals', array(
                'client_company' => array(
                    'type' => 'varchar',
                    'constraint' => 255,
                    'null' => FALSE,
                    'default' => '',
                ),
            ));
        }
        
        $result = $this->db->query("SHOW COLUMNS FROM " . $this->db->dbprefix('proposals') . " LIKE 'client_address'")->row_array();
        if (!isset($result['Field']) or $result['Field'] != 'client_address') {
            $this->dbforge->add_column('proposals', array(
                'client_address' => array(
                    'type' => 'text',
                    'null' => FALSE,
                    'default' => '',
                ),
            ));
        }
        
        $result = $this->db->query("SHOW COLUMNS FROM " . $this->db->dbprefix('proposals') . " LIKE 'client_name'")->row_array();
        if (!isset($result['Field']) or $result['Field'] != 'client_name') {
            $this->dbforge->add_column('proposals', array(
                'client_name' => array(
                    'type' => 'varchar',
                    'constraint' => 255,
                    'null' => FALSE,
                    'default' => '',
                ),
            ));
        }
        
    }

    public function down() {
        
    }

}