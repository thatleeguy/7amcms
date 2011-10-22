<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_languages_timezones_branding extends CI_Migration {
    function up() {
        if ($this->db->where('slug', 'language')->count_all_results('settings') == 0) {
            $this->db->insert('settings', array('value' => 'english', 'slug' => 'language'));
            $this->db->insert('settings', array('value' => @date_default_timezone_get(), 'slug' => 'timezone'));
            $this->db->insert('settings', array('value' => '', 'slug' => 'logo_url'));
        }
    }
    
    function down()
	{
        
    }
}