<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_task_notes extends CI_Migration {

    public function up() {        
        $result = $this->db->query("SHOW COLUMNS FROM ".$this->db->dbprefix('project_tasks')." LIKE 'notes'")->row_array();
        if (!isset($result['Field']) or $result['Field'] != 'notes') {
            $this->dbforge->add_column('project_tasks', array(
                'notes' => array(
                    'type' => 'text',
                ),
            ));
        }
    }

    public function down() {
    }

}