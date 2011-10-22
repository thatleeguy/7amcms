<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Bump_to_3 extends CI_Migration {
    function up() {
        Settings::setVersion('3.0.0');
    }
    
    function down() {
        Settings::setVersion('2.2.0');
    }
}