<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_payment_gateways extends CI_Migration {

    public function up() {
        $this->db->query("CREATE TABLE IF NOT EXISTS " . $this->db->dbprefix('gateway_fields') . " (
                  `gateway` varchar(255) NOT NULL,
                  `field` varchar(255) NOT NULL,
                  `value` text NOT NULL,
                  `type` varchar(255) NOT NULL,
                  KEY `gateway` (`gateway`),
                  KEY `field` (`field`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
                ");
        
        require_once APPPATH.'modules/gateways/gateway.php';
        
        # Upgrade PayPal - If people have a PayPal email, it gets enabled automatically.
        $paypal_email = $this->db->select('value')->where('slug', 'paypal_email')->get('settings')->row_array();
        $paypal_email = $paypal_email['value'];
        if (!empty($paypal_email)) {
            Gateway::set_field('paypal_m', 'enabled', 1, 'ENABLED');
            Gateway::set_field('paypal_m', 'paypal_email', $paypal_email, 'FIELD');
            # And there it is. Now it's automatically turned on for all clients and all invoices.
        }
    }

    public function down() {
        $this->dbforge->drop_table('gateway_fields');
    }

}