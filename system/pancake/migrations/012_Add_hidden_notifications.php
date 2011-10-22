<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_hidden_notifications extends CI_Migration {

    public function up() {
        $this->db->query("CREATE TABLE IF NOT EXISTS " . $this->db->dbprefix('hidden_notifications') . " (
        `user_id` INT( 11 ) NOT NULL ,
        `notification_id` INT( 11 ) NOT NULL ,
        INDEX (  `user_id` ,  `notification_id` )
        ) ENGINE = MYISAM DEFAULT CHARSET=utf8;");
    }

    public function down() {
        $this->dbforge->drop_table('hidden_notifications');
    }

}