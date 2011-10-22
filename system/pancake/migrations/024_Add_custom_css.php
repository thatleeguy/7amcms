<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_custom_css extends CI_Migration {
    
    public function up()
	{
        $this->db->insert_batch('settings', array(
			array('slug' => 'frontend_css'),
	        array('slug' => 'backend_css'),
        ));
    }
    
    public function down()
	{
        $this->db
			->where_in('slug', array('frontend_css', 'backend_css'))
			->delete('settings');
    }
}