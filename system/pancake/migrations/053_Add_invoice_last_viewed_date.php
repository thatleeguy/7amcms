<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_invoice_last_viewed_date extends CI_Migration {

    public function up() {
	add_column('invoices', 'last_viewed', 'int', 20, 0);
    }

    public function down() {
	
    }

}