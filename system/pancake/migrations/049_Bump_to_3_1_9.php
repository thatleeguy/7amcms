<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Bump_to_3_1_9 extends CI_Migration {
    function up() {
        Settings::setVersion('3.1.9');
	
	
	
    }
    
    function down() {
        Settings::setVersion('3.1.8');
    }
}