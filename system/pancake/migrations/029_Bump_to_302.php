<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Bump_to_302 extends CI_Migration {
    function up() {
        Settings::setVersion('3.0.2');
    }
    
    function down() {
        Settings::setVersion('3.0.0');
    }
}