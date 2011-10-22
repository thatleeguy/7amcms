<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_api_logs extends CI_Migration {

    public function up() {
        $this->db->query("CREATE TABLE IF NOT EXISTS ".$this->db->dbprefix('logs')." (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `uri` varchar(255) NOT NULL,
		  `method` varchar(6) NOT NULL,
		  `params` text NOT NULL,
		  `api_key` varchar(40) NOT NULL,
		  `ip_address` varchar(15) NOT NULL,
		  `time` int(11) NOT NULL,
		  `authorized` tinyint(1) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    }

    public function down() {
        $this->dbforge->drop_table('logs');
    }

}