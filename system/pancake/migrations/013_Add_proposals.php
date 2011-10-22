<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_proposals extends CI_Migration {

    public function up() {
        $this->db->query("CREATE TABLE IF NOT EXISTS ".$this->db->dbprefix('proposals')." (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `unique_id` varchar(10) NOT NULL,
          `created` int(11) NOT NULL,
          `last_sent` int(11) NOT NULL,
          `invoice_id` int(11) NOT NULL,
          `project_id` int(11) NOT NULL,
          `client_id` int(11) NOT NULL,
          `title` varchar(255) NOT NULL,
          `status` varchar(255) NOT NULL, 
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        
        $this->db->query("CREATE TABLE IF NOT EXISTS ".$this->db->dbprefix('proposal_sections')." (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `proposal_id` int(11) NOT NULL,
          `title` varchar(255) NOT NULL,
          `subtitle` varchar(255) NOT NULL,
          `contents` text NOT NULL,
          `key` int(11) NOT NULL,
          `parent_id` INT( 11 ) NOT NULL ,
          `page_key` INT( 11 ) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    }

    public function down() {
        $this->dbforge->drop_table('proposals');
    }

}