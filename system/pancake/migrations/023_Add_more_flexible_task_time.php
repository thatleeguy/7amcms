<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_more_flexible_task_time extends CI_Migration {
    
    function up() {
        $this->db->query('ALTER TABLE  '.$this->db->dbprefix('project_times').' CHANGE  `minutes`  `minutes` DECIMAL( 16, 8 ) NOT NULL');
    }
    
    function down() {
        
    }
}