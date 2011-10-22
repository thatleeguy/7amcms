<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Bump_to_3_1_13 extends CI_Migration {
    function up() {
        Settings::setVersion('3.1.13');
	$this->load->model('upgrade/update_system_m', 'update');
	$this->update->get_latest_version(true);
	$this->update->pancake_updated();
    }
    
    function down() {
        Settings::setVersion('3.1.12');
    }
}