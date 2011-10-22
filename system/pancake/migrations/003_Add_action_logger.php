<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_action_logger extends CI_Migration {

    public function up() {
        $this->db->query("CREATE TABLE IF NOT EXISTS " . $this->db->dbprefix('action_logs') . " (
        `id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
        `timestamp` INT( 11 ) NOT NULL ,
        `user_id` INT( 11 ) NOT NULL ,
        `action` VARCHAR( 255 ) NOT NULL ,
        `message` TEXT NOT NULL ,
        `item_id` INT( 11 ) NOT NULL
        ) ENGINE = MYISAM DEFAULT CHARSET=utf8;");
    }

    public function down() {
        $this->dbforge->drop_table('action_logs');
    }

}