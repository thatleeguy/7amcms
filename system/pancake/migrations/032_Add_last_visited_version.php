<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_last_visited_version extends CI_Migration {

    public function up() {
	$result = $this->db->query("SHOW COLUMNS FROM " . $this->db->dbprefix('meta') . " LIKE 'last_visited_version'")->row_array();
        if (!isset($result['Field']) or $result['Field'] != 'last_visited_version') {
            $this->dbforge->add_column('meta', array(
                'last_visited_version' => array(
                    'type' => 'varchar',
                    'constraint' => 48,
                    'null' => FALSE,
                    'default' => 0,
                ),
            ));
        }
	
	$this->db->query('ALTER TABLE  '.$this->db->dbprefix('hidden_notifications').' CHANGE  `notification_id`  `notification_id` VARCHAR( 255 ) NOT NULL');
	
	$this->db->update('meta', array('last_visited_version' => Settings::get('version')));
    }
    
    public function down() {
	$this->db->query('ALTER TABLE '.$this->db->dbprefix('meta').' DROP `last_visited_version`');
    }
    
}