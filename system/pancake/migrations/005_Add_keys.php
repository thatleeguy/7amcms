<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_keys extends CI_Migration {

    public function up() {
        $this->db->query("CREATE TABLE IF NOT EXISTS " . $this->db->dbprefix('keys') . " (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `key` varchar(40) NOT NULL,
		  `level` int(2) NOT NULL,
		  `date_created` int(11) NOT NULL,
		  `notes` text ,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    }

    public function down() {
        $this->dbforge->drop_table('keys');
    }

}