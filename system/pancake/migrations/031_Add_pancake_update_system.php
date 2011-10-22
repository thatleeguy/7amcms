<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_pancake_update_system extends CI_Migration {

    public function up() {
	Settings::create('latest_version_fetch', '0');
	Settings::create('ftp_host', '');
	Settings::create('ftp_user', '');
	Settings::create('ftp_pass', '');
	Settings::create('ftp_path', '/');
	Settings::create('auto_update', '0');
	Settings::create('ftp_port', '21');
	Settings::create('ftp_pasv', '1');
	Settings::create('latest_version', '0');
    }

    public function down() {
	Settings::delete('latest_version_fetch');
	Settings::delete('ftp_host');
	Settings::delete('ftp_user');
	Settings::delete('ftp_pass');
	Settings::delete('ftp_path');
	Settings::delete('auto_update');
	Settings::delete('ftp_port');
	Settings::delete('ftp_pasv');
	Settings::delete('latest_version');
    }

}