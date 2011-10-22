<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_project_times extends CI_Migration {

    public function up() {
        $this->db->query("CREATE TABLE IF NOT EXISTS " . $this->db->dbprefix('project_times') . " (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`project_id` int(10) unsigned NOT NULL,
			`task_id` int(10) unsigned NULL,
			`user_id` int(10) unsigned NULL,
			`start_time` varchar(5) NOT NULL DEFAULT '',
			`end_time` varchar(5) NOT NULL DEFAULT '',
			`minutes` decimal(5,1) NOT NULL,
			`date` int(11) NULL,
			`note` text NULL,
			PRIMARY KEY (`id`),
			INDEX project_id (`project_id`),
			INDEX user_id (`user_id`),
			INDEX date (`date`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    }

    public function down() {
        $this->dbforge->drop_table('project_times');
    }

}
