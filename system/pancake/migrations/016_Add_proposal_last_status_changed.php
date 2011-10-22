<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_proposal_last_status_changed extends CI_Migration {

	public function up()
	{
        $result = $this->db->query("SHOW COLUMNS FROM " . $this->db->dbprefix('proposals') . " LIKE 'last_status_change'")->row_array();
        if (!isset($result['Field']) or $result['Field'] != 'last_status_change') {
            $this->dbforge->add_column('proposals', array(
                'last_status_change' => array(
                    'type' => 'int',
                    'constraint' => 20,
                    'null' => FALSE,
                    'default' => 0,
                ),
            ));
        }
        
        $result = $this->db->query("SHOW COLUMNS FROM " . $this->db->dbprefix('proposals') . " LIKE 'last_viewed'")->row_array();
        if (!isset($result['Field']) or $result['Field'] != 'last_viewed') {
            $this->dbforge->add_column('proposals', array(
                'last_viewed' => array(
                    'type' => 'int',
                    'constraint' => 20,
                    'null' => FALSE,
                    'default' => 0,
                ),
            ));
        }
    }
    
    public function down()
	{
        
    }
}