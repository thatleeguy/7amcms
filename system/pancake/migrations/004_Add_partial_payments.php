<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_partial_payments extends CI_Migration {

    public function up() {
	
        $this->db->query("CREATE TABLE IF NOT EXISTS ".$this->db->dbprefix('partial_payments')." (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `unique_invoice_id` varchar(10) NOT NULL,
          `amount` float NOT NULL,
          `is_percentage` tinyint(1) NOT NULL,
          `due_date` int(11) NOT NULL,
          `notes` text NOT NULL,
          `txn_id` varchar(255) NOT NULL,
          `payment_gross` float NOT NULL,
          `item_name` varchar(255) NOT NULL,
          `is_paid` tinyint(1) NOT NULL,
          `payment_date` int(11) NOT NULL,
          `payment_type` varchar(255) NOT NULL,
          `payer_status` varchar(255) NOT NULL,
          `payment_status` varchar(255) NOT NULL,
          `unique_id` varchar(10) NOT NULL,
	  `transaction_fee` float NOT NULL,
          `payment_method` varchar(255) NOT NULL,
          `key` int(11) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
         
        $this->load->model('invoices/invoice_m');
        $this->invoice_m->upgradeToPartialPayments();
    }

    public function down() {
        $this->dbforge->drop_table('partial_payments');
    }

}