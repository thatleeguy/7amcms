<?php

defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Pancake
 *
 * A simple, fast, self-hosted invoicing application
 *
 * @package		Pancake
 * @author		Pancake Dev Team
 * @copyright		Copyright (c) 2011, Pancake Payments
 * @license		http://pancakeapp.com/license
 * @link		http://pancakeapp.com
 * @since		Version 3
 */
// ------------------------------------------------------------------------

/**
 * The Upgrade Model
 *
 * @subpackage	Models
 * @category	Upgrade
 */
class Upgrade_m extends Breakfast_Model {

    private $_versions = array('1.0', '1.1', '1.1.1', '1.1.2', '1.1.3', '1.1.4', '2.0', '2.0.1', '2.0.2', '2.1.0');
    private $_current_version = 0;

    /**
     * The construct doesn't do anything useful right now.
     *
     * @access	public
     * @return	void
     */
    public function __construct() {
	parent::__construct();
	include(APPPATH . 'config/database.php');
	$this->load->dbforge();
	$this->prefix = $db['default']['dbprefix'];

	$this->_current_version = PAN::setting('version');

	if (!$this->_current_version) {
	    $this->_current_version = '1.0';
	}

	$this->load->helper('array');
    }

    function start() {
	$start = array_search($this->_current_version, $this->_versions);

	for ($i = $start + 1; $i < count($this->_versions); $i++) {
	    call_user_func(array($this, 'version_' . str_replace('.', '_', $this->_versions[$i])));

	    // Update the settings version
	    Settings::setVersion($this->_versions[$i]);
	}

	# Everything's upgraded to 2.1.0, let's run Migrations, the next generation way of upgrading. :D
	redirect('admin');
    }

    /**
     * Upgrades from v2.0.2 to v2.1.0
     *
     * @access	private
     * @return	bool
     */
    public function version_2_1_0() {
	$this->db->query("
		ALTER TABLE {$this->prefix}projects
			ADD `currency_id` int(11) NOT NULL,
			ADD `exchange_rate` float(10,5) NOT NULL DEFAULT '1.00000'");

	$this->db->query("
		ALTER TABLE {$this->prefix}invoices
		  ADD `currency_id` int(11) DEFAULT NULL,
		  ADD `exchange_rate` float(10,5) NOT NULL DEFAULT '1.00000' ");

	$this->db->query("
			CREATE TABLE {$this->prefix}currencies (
			  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
			  `name` varchar(200) DEFAULT '',
			  `code` varchar(3) NOT NULL,
			  `rate` float DEFAULT '0',
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    }

    // ------------------------------------------------------------------------

    /**
     * Upgrades from v2.0.1 to v2.0.2
     *
     * @access	private
     * @return	bool
     */
    public function version_2_0_2() {
	$this->db->insert('settings', array('slug' => 'task_time_interval', 'value' => 0.5));

	$this->db->query("ALTER TABLE {$this->prefix}project_tasks CHANGE `hours`
			`hours` decimal(10,2) NOT NULL DEFAULT '0.00' ");
    }

    // ------------------------------------------------------------------------

    /**
     * Upgrades from v2.0.0 to v2.0.1
     *
     * @access	private
     * @return	bool
     */
    private function version_2_0_1() {
	$this->db->query("ALTER TABLE {$this->prefix}invoices CHANGE `type`
			`type` enum('SIMPLE','DETAILED','ESTIMATE') collate utf8_unicode_ci NOT NULL default 'SIMPLE'");
    }

    // ------------------------------------------------------------------------

    /**
     * Upgrades from v1.1.4 to v2.0
     *
     * @access	private
     * @return	bool
     */
    private function version_2_0() {
	$this->db->query("CREATE TABLE `" . $this->prefix . "projects` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `client_id` int(11) NOT NULL,
		  `name` varchar(255) NOT NULL,
		  `due_date` int(11) NOT NULL,
		  `description` text NOT NULL,
		  `date_entered` int(11) NOT NULL,
		  `date_updated` int(11) NOT NULL,
		  `rate` decimal(10,2) NOT NULL DEFAULT '0.00',
		  `completed` tinyint(4) NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `client_id` (`client_id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");

	$this->db->query("CREATE TABLE `" . $this->prefix . "project_tasks` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `project_id` int(10) unsigned NOT NULL,
		  `name` varchar(255) NOT NULL,
		  `rate` decimal(10,2) NOT NULL DEFAULT '0.00',
		  `hours` decimal(11, 1) NOT NULL DEFAULT '0.0',
		  `due_date` int(11) DEFAULT '0',
		  `completed` tinyint(4) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");


	$this->dbforge->add_column('invoices', array(
	    'is_recurring' => array('type' => 'tinyint', 'constraint' => 1, 'default' => 0, 'null' => FALSE),
	    'frequency' => array('type' => 'enum', 'constraint' => array('w', 'm', 'y'), 'null' => TRUE),
	    'auto_send' => array('type' => 'tinyint', 'constraint' => 1, 'default' => 0, 'null' => FALSE),
	    'recur_id' => array('type' => 'int', 'constraint' => 11, 'default' => 0, 'null' => FALSE)
	));

	$this->db->where('slug', 'theme')->update('settings', array('value' => 'pancake'));
	$this->db->where('slug', 'admin_theme')->update('settings', array('value' => 'pancake'));
    }

    // ------------------------------------------------------------------------

    /**
     * Upgrades from v1.1.3 to v1.1.4
     *
     * @access	private
     * @return	bool
     */
    private function version_1_1_4() {
	$username_col = array(
	    'username' => array('name' => 'username', 'type' => 'VARCHAR', 'constraint' => 200, 'default' => ''),
	);
	$this->dbforge->modify_column('users', $username_col);

	$qty_col = array(
	    'qty' => array('name' => 'qty', 'type' => 'FLOAT', 'default' => 0),
	);
	$this->dbforge->modify_column('invoice_rows', $qty_col);

	$type_col = array(
	    'type' => array('name' => 'type', 'type' => 'enum', 'constraint' => "'SIMPLE','DETAILED','ESTIMATE'"),
	);
	$this->dbforge->modify_column('invoices', $type_col);

	return TRUE;
    }

    // ------------------------------------------------------------------------

    /**
     * Upgrades from v1.1.2 to v1.1.3
     *
     * @access	private
     * @return	bool
     */
    private function version_1_1_3() {
	return TRUE;
    }

    // ------------------------------------------------------------------------

    /**
     * Upgrades from v1.1.1 to v1.1.2
     *
     * @access	private
     * @return	bool
     */
    private function version_1_1_2() {
	return TRUE;
    }

    // ------------------------------------------------------------------------

    /**
     * Upgrades from v1.1 to v1.1.1
     *
     * @access	private
     * @return	bool
     */
    private function version_1_1_1() {
	return TRUE;
    }

    // ------------------------------------------------------------------------

    /**
     * Upgrades from v1.0 to v1.1
     *
     * @access	private
     * @return	bool
     */
    private function version_1_1() {
	$this->dbforge->rename_table($this->prefix . 'payments', $this->prefix . 'invoices');

	//Drop the unused 'note_used' column
	$this->dbforge->drop_column('invoices', 'note_used');

	// Prepair the table for updates
	$invoices = $this->db->get('invoices')->result();
	$stored_types = array();
	foreach ($invoices as $invoice) {
	    $stored_types[$invoice->id] = str_replace(array('INV', 'REQ'), array('DETAILED', 'SIMPLE'), $invoice->type);
	    $this->db->where('id', $invoice->id)->update('invoices', array('invoice_due_date' => strtotime($invoice->invoice_due_date)));
	}

	// Setup the modified invoice columns
	$invoice_mod_cols = array(
	    'unique_id' => array('name' => 'unique_id', 'type' => 'VARCHAR', 'constraint' => 10, 'default' => ''),
	    'client_id' => array('name' => 'client_id', 'type' => 'INT', 'constraint' => 11, 'default' => 0),
	    'invoice_due_date' => array('name' => 'due_date', 'type' => 'INT', 'constraint' => 11, 'default' => 0),
	    'invoice_amount' => array('name' => 'amount', 'type' => 'FLOAT', 'default' => 0),
	    'invoice_number' => array('name' => 'invoice_number', 'type' => 'VARCHAR', 'constraint' => 255, 'default' => ''),
	    'invoice_notes' => array('name' => 'notes', 'type' => 'TEXT'),
	    'invoice_work' => array('name' => 'description', 'type' => 'TEXT'),
	    'txn_id' => array('name' => 'txn_id', 'type' => 'VARCHAR', 'constraint' => 255, 'default' => ''),
	    'payment_gross' => array('name' => 'payment_gross', 'type' => 'FLOAT', 'default' => 0),
	    'item_name' => array('name' => 'item_name', 'type' => 'VARCHAR', 'constraint' => 255, 'default' => ''),
	    'payment_hash' => array('name' => 'payment_hash', 'type' => 'VARCHAR', 'constraint' => 32, 'default' => ''),
	    'payment_status' => array('name' => 'payment_status', 'type' => 'VARCHAR', 'constraint' => 255, 'default' => ''),
	    'payment_type' => array('name' => 'payment_type', 'type' => 'VARCHAR', 'constraint' => 255, 'default' => ''),
	    'payment_date' => array('name' => 'payment_date', 'type' => 'INT', 'constraint' => 11, 'default' => 0),
	    'payer_status' => array('name' => 'payer_status', 'type' => 'VARCHAR', 'constraint' => 255, 'default' => ''),
	    'type' => array('name' => 'type', 'type' => 'ENUM', 'constraint' => "'SIMPLE','DETAILED'", 'default' => 'SIMPLE'),
	    'date_entered' => array('name' => 'date_entered', 'type' => 'INT', 'constraint' => 11, 'default' => 0),
	);
	$this->dbforge->modify_column('invoices', $invoice_mod_cols);

	// Add the is_paid column and set the values
	$invoice_new_cols = array(
	    'is_paid' => array('type' => 'TINYINT', 'constraint' => 1, 'default' => 0),
	);
	$this->dbforge->add_column('invoices', $invoice_new_cols);
	$this->db->where('txn_id !=', '')->update('invoices', array('is_paid' => 1));

	// Reset the types for all the invoices
	foreach ($stored_types as $id => $type) {
	    $this->db->where('id', $id)->update('invoices', array('type' => $type));
	}

	// Add the date_format setting
	$this->db->insert('settings', array('slug' => 'date_format', 'value' => 'm/d/Y'));

	// Add the rss_password into settings
	$this->db->insert('settings', array('slug' => 'rss_password', 'value' => random_string('alnum', 12)));

	$fields = array(
	    'id' => array(
		'type' => 'INT',
		'constraint' => '5',
		'unsigned' => TRUE,
		'auto_increment' => TRUE,
	    ),
	    'name' => array(
		'type' => 'VARCHAR',
		'constraint' => '200',
		'default' => '',
	    ),
	    'value' => array(
		'type' => 'float',
		'default' => 0,
	    ),
	);
	$this->dbforge->add_field($fields);
	$this->dbforge->add_key('id', TRUE);
	$this->dbforge->create_table('taxes');

	$this->db->insert('taxes', array('name' => 'Default', 'value' => Settings::get('tax_rate')));

	$this->db->delete('settings', array('slug' => 'tax_rate'));

	$this->dbforge->modify_column('invoice_rows', array('taxable' => array(
		'name' => 'tax_id',
		'type' => 'INT',
		'constraint' => '5',
		'default' => 0,
		)));

	return TRUE;
    }

}