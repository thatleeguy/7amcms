<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Initial_Setup extends CI_Migration {
	
	public function up() 
	{
		$this->db->query("
CREATE TABLE `7am_cms_clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(64) DEFAULT '',
  `last_name` varchar(64) DEFAULT '',
  `title` varchar(64) DEFAULT '',
  `email` varchar(128) DEFAULT '',
  `company` varchar(128) DEFAULT '',
  `address` text,
  `phone` varchar(64) DEFAULT '',
  `fax` varchar(64) DEFAULT '',
  `mobile` varchar(64) DEFAULT '',
  `website` varchar(128) DEFAULT '',
  `profile` text,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
");

		$this->db->query("
CREATE TABLE `7am_cms_groups` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `description` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
");

		$this->db->insert('groups', array(
			'id' => 1,
			'name' => 'admin',
			'description' => 'Administrators',
		));

		$this->db->query("
CREATE TABLE `7am_cms_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(40) NOT NULL,
  `level` int(2) NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `date_created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
");

		$this->db->query("
CREATE TABLE `7am_cms_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uri` varchar(255) NOT NULL,
  `method` varchar(6) NOT NULL,
  `params` text NOT NULL,
  `api_key` varchar(40) NOT NULL,
  `ip_address` varchar(15) NOT NULL,
  `time` int(11) NOT NULL,
  `authorized` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
");

		$this->db->query("
CREATE TABLE `7am_cms_meta` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) unsigned NOT NULL,
  `first_name` varchar(50) DEFAULT '',
  `last_name` varchar(50) DEFAULT '',
  `company` varchar(100) DEFAULT '',
  `phone` varchar(20) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
");

/*		$this->db->query("
CREATE TABLE `7am_cms_settings` (
  `slug` varchar(100) NOT NULL DEFAULT '',
  `value` text,
  PRIMARY KEY (`slug`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
"); */

		$this->db->query("
INSERT INTO `7am_cms_settings` VALUES 
('license_key','foo'),('mailing_address',''),
('notify_email','email@philsturgeon.co.uk'),
('paypal_email','email@philsturgeon.co.uk'),
('site_name','KPI Adverts'),
('admin_name','Phil Sturgeon'),
('theme','white'),
('date_format','m/d/Y');
");

		$this->db->query("
CREATE TABLE `7am_cms_users` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` mediumint(8) unsigned NOT NULL,
  `ip_address` char(16) NOT NULL,
  `username` varchar(200) NOT NULL,
  `password` varchar(40) NOT NULL,
  `salt` varchar(40) DEFAULT '',
  `email` varchar(40) NOT NULL,
  `activation_code` varchar(40) DEFAULT '',
  `forgotten_password_code` varchar(40) DEFAULT '',
  `remember_code` varchar(40) DEFAULT '',
  `created_on` int(11) unsigned NOT NULL,
  `last_login` int(11) unsigned DEFAULT NULL,
  `active` tinyint(1) unsigned DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
");

		$this->db->query("
INSERT INTO `7am_cms_users` VALUES (1,1,'127.0.0.1','admin','54571265f323b8dd1486b6023999b214c4da652f','d586a02e38','admin@example.com','',NULL,'5c21bf01ce26051c13bb739f7c11ebb35dfa5c63',1268889823,1307938945,1),
(2, 2, '127.0.0.1', 'sales', 'bf364565a5d2f53268565194022bd12fb3b73e93', 'b672459e51', 'sales@example.com', '', '', '', 1311381704, 1311634149, 1);
");

	}

	public function down() 
	{
	}
}
