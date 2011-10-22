<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_bcc_setting extends CI_Migration {

    public function up() {
	Settings::create('bcc', '0');
    }

    public function down() {
	Settings::delete('bcc');
    }

}