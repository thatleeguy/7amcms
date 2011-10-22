<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_time_format extends CI_Migration {
    function up() {
        if ($this->db->where('slug', 'time_format')->count_all_results('settings') == 0) {
            $this->db->insert('settings', array('value' => 'h:ia', 'slug' => 'time_format'));
        }
    }
    
    function down() {
        
    }
}