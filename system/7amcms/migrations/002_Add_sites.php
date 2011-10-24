<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_sites extends CI_Migration {

    public function up() {
        $this->db->query("CREATE TABLE IF NOT EXISTS " . $this->db->dbprefix('sites') . " (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `key` varchar(40) NOT NULL,
		  `domain` varchar(128) DEFAULT NULL,
		  `title` varchar(200) DEFAULT NULL,
		  `description` longtext,
		  `theme` varchar(128) DEFAULT NULL,
		  `status` enum('active','inactive','maintenance') DEFAULT NULL,
		  `package` int(2) NOT NULL,
		  `date_created` int(11) NOT NULL,
		  `notes` text ,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    }

    public function down() {
        $this->dbforge->drop_table('sites');
    }

}