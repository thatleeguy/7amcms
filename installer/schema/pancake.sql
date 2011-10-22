DROP TABLE IF EXISTS `{DBPREFIX}action_logs`, `{DBPREFIX}migrations`;

-- split --

CREATE TABLE `{DBPREFIX}action_logs` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`timestamp` INT( 11 ) NOT NULL ,
`user_id` INT( 11 ) NOT NULL ,
`action` VARCHAR( 255 ) NOT NULL ,
`message` TEXT NOT NULL ,
`item_id` INT( 11 ) NOT NULL
) ENGINE = MYISAM DEFAULT CHARSET=utf8;

-- split --

DROP TABLE IF EXISTS `{DBPREFIX}clients`;

-- split --

CREATE TABLE `{DBPREFIX}clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(64) DEFAULT '',
  `last_name` varchar(64) DEFAULT '',
  `title` varchar(64) DEFAULT '',
  `email` varchar(128) DEFAULT '',
  `company` varchar(128) DEFAULT '',
  `address` TEXT DEFAULT '',
  `phone` varchar(64) DEFAULT '',
  `fax` varchar(64) DEFAULT '',
  `mobile` varchar(64) DEFAULT '',
  `website` varchar(128) DEFAULT '',
  `profile` text DEFAULT '',
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

-- split --

DROP TABLE IF EXISTS `{DBPREFIX}currencies`;

-- split --

CREATE TABLE `{DBPREFIX}currencies` (
	`id` int(5) unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(200) DEFAULT '',
	`code` varchar(3) NOT NULL,
	`rate` float DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `code` (`code`)
) DEFAULT CHARSET=utf8;

-- split --

DROP TABLE IF EXISTS `{DBPREFIX}files`;

-- split --

CREATE TABLE `{DBPREFIX}files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_unique_id` varchar(255) NOT NULL,
  `orig_filename` varchar(255) NOT NULL,
  `real_filename` text NOT NULL,
  `download_count` int(5) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `invoice_unique_id` (`invoice_unique_id`)
) DEFAULT CHARSET=utf8;

-- split --

DROP TABLE IF EXISTS `{DBPREFIX}gateway_fields`;

-- split --

CREATE TABLE `{DBPREFIX}gateway_fields` (
	`gateway` varchar(255) NOT NULL,
	`field` varchar(255) NOT NULL,
	`value` text NOT NULL,
	`type` varchar(255) NOT NULL,
	KEY `gateway` (`gateway`),
	KEY `field` (`field`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- split --

DROP TABLE IF EXISTS `{DBPREFIX}groups`;

-- split --

CREATE TABLE `{DBPREFIX}groups` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `description` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
)  DEFAULT CHARSET=utf8;

-- split --

DROP TABLE IF EXISTS `{DBPREFIX}items`;

-- split --

CREATE TABLE {DBPREFIX}items (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `qty` float unsigned NOT NULL DEFAULT '1',
  `rate` float unsigned NOT NULL DEFAULT '0',
  `tax_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- split --

DROP TABLE IF EXISTS `{DBPREFIX}keys`;

-- split --

CREATE TABLE `{DBPREFIX}keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(40) NOT NULL,
  `level` int(2) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `date_created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

-- split --

DROP TABLE IF EXISTS `{DBPREFIX}logs`;

-- split --

CREATE TABLE `{DBPREFIX}logs` (
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

-- split --

DROP TABLE IF EXISTS `{DBPREFIX}taxes`;

-- split --

CREATE TABLE `{DBPREFIX}taxes` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT '',
  `value` float DEFAULT '0',
  `reg` varchar(100) DEFAULT '',
  PRIMARY KEY (`id`)
)  DEFAULT CHARSET=utf8;

-- split --

INSERT INTO `{DBPREFIX}taxes` (name, value) VALUES ('Default', '{TAX_RATE}');

-- split --

DROP TABLE IF EXISTS `{DBPREFIX}hidden_notifications`;

-- split --

CREATE TABLE `{DBPREFIX}hidden_notifications` (
`user_id` INT( 11 ) NOT NULL ,
`notification_id` INT( 11 ) NOT NULL ,
INDEX (  `user_id` ,  `notification_id` )
) ENGINE = MYISAM DEFAULT CHARSET=utf8;

-- split --

DROP TABLE IF EXISTS `{DBPREFIX}invoice_rows`;

-- split --

CREATE TABLE `{DBPREFIX}invoice_rows` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unique_id` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT '',
  `description` text,
  `qty` float DEFAULT '0',
  `tax_id` int(5) DEFAULT '0',
  `rate` varchar(255) DEFAULT '',
  `total` varchar(255) DEFAULT '',
  `sort` smallint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
)  DEFAULT CHARSET=utf8;

-- split --

DROP TABLE IF EXISTS `{DBPREFIX}meta`;

-- split --

CREATE TABLE `{DBPREFIX}meta` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) unsigned NOT NULL,
  `first_name` varchar(50) DEFAULT '',
  `last_name` varchar(50) DEFAULT '',
  `company` varchar(100) DEFAULT '',
  `phone` varchar(20) DEFAULT '',
  `last_visited_version` varchar(48) DEFAULT '',
  PRIMARY KEY (`id`)
)  DEFAULT CHARSET=utf8;

-- split --

DROP TABLE IF EXISTS `{DBPREFIX}notes`;

-- split --

CREATE TABLE `{DBPREFIX}notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `note` text NOT NULL,
  `submitted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`)
) DEFAULT CHARSET=utf8;

-- split --

DROP TABLE IF EXISTS `{DBPREFIX}invoices`;

-- split --

CREATE TABLE `{DBPREFIX}invoices` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`unique_id` varchar(10) DEFAULT '',
	`client_id` int(11) DEFAULT '0',
	`amount` float DEFAULT '0',
	`due_date` int(11) DEFAULT '0',
	`invoice_number` varchar(255) DEFAULT '',
	`notes` text,
	`description` text,
	`txn_id` varchar(255) DEFAULT '',
	`payment_gross` float DEFAULT '0',
	`item_name` varchar(255) DEFAULT '',
	`payment_hash` varchar(32) DEFAULT '',
	`payment_status` varchar(255) DEFAULT '',
	`payment_type` varchar(255) DEFAULT '',
	`payment_date` int(11) DEFAULT '0',
	`payer_status` varchar(255) DEFAULT '',
	`type` enum('SIMPLE','DETAILED','ESTIMATE') DEFAULT 'SIMPLE',
	`date_entered` int(11) DEFAULT '0',
	`is_paid` tinyint(1) DEFAULT '0',
	`is_recurring` tinyint(1) DEFAULT '0',
	`frequency` enum('w', 'm', 'y'),
	`auto_send` tinyint(1) NOT NULL DEFAULT '0',
	`recur_id` int(11) NOT NULL DEFAULT '0',
	`currency_id` int(11) NOT NULL,
	`exchange_rate` float(10,5) NOT NULL DEFAULT '1.00000',
	`proposal_id` int(20) NOT NULL DEFAULT '0',
	`send_x_days_before` int(11) NOT NULL DEFAULT '7',
    `has_sent_notification` int(1) NOT NULL DEFAULT '0',
	`last_sent` int(11) NOT NULL DEFAULT '0',
	`next_recur_date` int(11) NOT NULL DEFAULT '0',
	`last_viewed` int(20) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
)  DEFAULT CHARSET=utf8;

-- split --

DROP TABLE IF EXISTS `{DBPREFIX}partial_payments`;

-- split --

CREATE TABLE `{DBPREFIX}partial_payments` (
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
  `payment_method` varchar(255) NOT NULL,
  `key` int(11) NOT NULL,
  `transaction_fee` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- split --

DROP TABLE IF EXISTS `{DBPREFIX}projects`;

-- split --

CREATE TABLE `{DBPREFIX}projects` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`client_id` int(11) NOT NULL,
	`name` varchar(255) NOT NULL,
	`due_date` int(11) NOT NULL,
	`description` text NOT NULL,
	`date_entered` int(11) NOT NULL,
	`date_updated` int(11) NOT NULL,
	`rate` decimal(10,2) NOT NULL DEFAULT '0.00',
	`completed` tinyint(4) NOT NULL,
	`currency_id` int(11) NOT NULL,
	`exchange_rate` float(10,5) NOT NULL DEFAULT '1.00000',
	`unique_id` varchar(10) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	KEY `client_id` (`client_id`)
)  DEFAULT CHARSET=utf8;

-- split --

DROP TABLE IF EXISTS `{DBPREFIX}project_tasks`

-- split --

CREATE TABLE `{DBPREFIX}project_tasks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `rate` decimal(10,2) NOT NULL DEFAULT '0.00',
  `hours` decimal(10, 2) NOT NULL DEFAULT '0.0',
  `notes` TEXT NOT NULL,
  `due_date` int(11) DEFAULT '0',
  `completed` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
)  DEFAULT CHARSET=utf8;

-- split --

DROP TABLE IF EXISTS `{DBPREFIX}project_times`;

-- split --

CREATE TABLE `{DBPREFIX}project_times` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(10) unsigned NOT NULL,
  `task_id` int(10) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `start_time` varchar(5) NOT NULL DEFAULT '',
  `end_time` varchar(5) NOT NULL DEFAULT '',
  `minutes` decimal(16,8) NOT NULL,
  `date` int(11) DEFAULT NULL,
  `note` text,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `user_id` (`user_id`),
  KEY `date` (`date`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- split --

DROP TABLE IF EXISTS `{DBPREFIX}proposals`;

-- split --

CREATE TABLE `{DBPREFIX}proposals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unique_id` varchar(10) NOT NULL,
  `created` int(11) NOT NULL,
  `last_sent` int(11) NOT NULL DEFAULT '0',
  `last_status_change` int(20) NOT NULL DEFAULT '0',
  `last_viewed` int(20) NOT NULL DEFAULT '0',
  `invoice_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL, 
  `proposal_number` int(20) NOT NULL DEFAULT '0',
  `client_company` varchar(255) NOT NULL DEFAULT '',
  `client_address` text NOT NULL DEFAULT '',
  `client_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- split --

DROP TABLE IF EXISTS `{DBPREFIX}proposal_sections`;

-- split --

CREATE TABLE `{DBPREFIX}proposal_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `proposal_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) NOT NULL,
  `contents` text NOT NULL,
  `key` int(11) NOT NULL,
  `parent_id` INT( 11 ) NOT NULL ,
  `page_key` INT( 11 ) NOT NULL,
  `section_type` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- split --

DROP TABLE IF EXISTS `{DBPREFIX}settings`;

-- split --

CREATE TABLE `{DBPREFIX}settings` (
  `slug` varchar(100) NOT NULL DEFAULT '',
  `value` TEXT DEFAULT '',
  PRIMARY KEY (`slug`)
) DEFAULT CHARSET=utf8;

-- split --

INSERT INTO `{DBPREFIX}settings` VALUES
('admin_theme', 'pancake'),
('currency', '{CURRENCY}'),
('email_new_invoice', 'Hi {invoice:first_name} {invoice:last_name}\n\nYour invoice #{invoice:invoice_number} is ready, after review if you would like to pay it immediately using your credit card (via PayPal) please click <a href=\"{invoice:url}\">{invoice:url}</a>\n\nThanks,\n{settings:admin_name}'),
('email_paid_notification', '{ipn:first_name} {ipn:last_name} has paid Invoice #{invoice:invoice_number}\n\nThe total paid was ${ipn:payment_gross}.'),
('email_receipt', 'Thank you for your payment.\n\nInvoice #{invoice:invoice_number}\nTotal Paid: {ipn:payment_gross}\n\nYou may have files available for download. Click here to view your invoice:  {invoice:url}.\n\nThanks,\n{settings:admin_name}\n'),
('email_new_proposal', 'Hi {proposal:client_name}\n\nA new proposal is ready for you on {settings:site_name}:\n\n{proposal:url}\n\nThanks,\n{settings:admin_name}'),
('license_key', '{LICENSE_KEY}'),
('mailing_address', '{MAILING_ADDRESS}'),
('notify_email', '{NOTIFY_EMAIL}'),
('paypal_email', '{PAYPAL_EMAIL}'),
('rss_password', '{RSS_PASSWORD}'),
('site_name', '{SITE_NAME}'),
('admin_name', '{FIRST_NAME} {LAST_NAME}'),
('theme', '{THEME}'),
('version', '{VERSION}'),
('latest_version_fetch', '0'),
('auto_update', '0'),
('ftp_host', ''),
('ftp_user', ''),
('ftp_pass', ''),
('ftp_path', '/'),
('bcc', '0'),
('email_type', 'mail'),
('smtp_host', ''),
('smtp_user', ''),
('smtp_pass', ''),
('smtp_port', ''),
('mailpath', '/usr/sbin/sendmail'),
('ftp_port', '21'),
('ftp_pasv', '1'),
('latest_version', '0'),
('date_format', 'm/d/Y'),
('time_format', 'H:i'),
('timezone', '{TIMEZONE}'),
('language', 'english'),
('task_time_interval', '0.5'),
('frontend_css', ''),
('backend_css', ''),
('logo_url', '');

-- split --

DROP TABLE IF EXISTS `{DBPREFIX}users`;

-- split --

CREATE TABLE IF NOT EXISTS `{DBPREFIX}users` (
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
)  DEFAULT CHARSET=utf8;

-- split --

INSERT INTO `{DBPREFIX}meta` VALUES (1, 1, '{FIRST_NAME}', '{LAST_NAME}', '{SITE_NAME}', '0', '{VERSION}');

-- split --

CREATE TABLE `{DBPREFIX}migrations` (
  `version` int(3) DEFAULT NULL
);

-- split --

INSERT INTO `{DBPREFIX}migrations` VALUES ('{MIGRATION}');

-- split --

INSERT INTO `{DBPREFIX}groups` VALUES (1, 'admin', 'Administrator'), (2, 'members', 'General User');

-- split --

INSERT INTO `{DBPREFIX}users` VALUES (1, 1, '127.0.0.1', '{USERNAME}', '{PASSWORD}', '{SALT}', '{NOTIFY_EMAIL}', '', NULL, NULL, 1268889823, 1281291575, 1);