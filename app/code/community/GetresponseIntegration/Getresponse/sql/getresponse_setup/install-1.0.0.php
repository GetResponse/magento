<?php
/**
 * Getresponse installation script
 *
 * @author Magento
 */

/**
 * @var $installer Mage_Core_Model_Resource_Setup
 */
$installer = $this;
$installer->startSetup();

$allStores = array();
$stores = Mage::app()->getStores();

if ( !empty($stores)) {
	foreach ($stores as $id => $val) {
		$allStores[] = Mage::app()->getStore($id)->getId();
	}
}
else {
	$allStores[] = Mage::helper('getresponse')->getStoreId();
}

$installer->run("
    CREATE TABLE IF NOT EXISTS `{$installer->getTable('getresponse_settings')}` (
		`id` int(6) NOT NULL AUTO_INCREMENT,
		`id_shop` char(32) NULL,
		`api_key` char(32) NOT NULL,
		`api_url` text(255),
		`api_domain` text(255),
		`active_subscription` tinyint(1) NULL DEFAULT 0,
		`update_address` tinyint(1) NULL DEFAULT 0,
		`campaign_id` char(5) NOT NULL,
		`cycle_day` char(5) NULL DEFAULT '',
		PRIMARY KEY (`id`),
		UNIQUE KEY `index_getresponse_settings_on_id_shop` (`id_shop`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `{$installer->getTable('getresponse_account')}` (
		`id` int(6) NOT NULL AUTO_INCREMENT,
		`id_shop` char(32) NULL ,
		`accountId` char(32) NULL,
		`firstName` text(255) NULL,
		`lastName` text(255) NULL,
		`email` text(255) NULL,
		`phone` text(255) NULL,
		`companyName` text(255) NULL,
		`state` text(255) NULL,
		`city` text(255) NULL,
		`street` text(255) NULL,
		`zipCode` text(255) NULL,
		`country` text(255) NULL,
		`numberOfEmployees` text(255) NULL,
		`timeFormat` text(255) NULL,
		`timeZone_name` text(255) NULL,
		`timeZone_offset` text(255) NULL,
		PRIMARY KEY (`id`),
		UNIQUE KEY `index_getresponse_account_on_id_shop` (`id_shop`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `{$installer->getTable('getresponse_customs')}` (
		`id_custom` int(11) NOT NULL AUTO_INCREMENT,
		`id_shop` int(6) NULL,
		`custom_field` char(32) NOT NULL,
		`custom_value` char(32) NOT NULL,
		`default` tinyint(1) NULL DEFAULT 0,
		`active_custom` tinyint(1) NULL DEFAULT 0,
		PRIMARY KEY (`id_custom`),
		UNIQUE KEY `index_getresponse_customs_on_id_custom` (`id_shop`, `custom_field`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `{$installer->getTable('getresponse_webforms')}` (
		`id` int(6) NOT NULL AUTO_INCREMENT,
		`id_shop` int(6) NOT NULL,
		`webform_id` char(32) NOT NULL,
		`active_subscription` tinyint(1) NULL DEFAULT 0,
		`layout_position` char(255) NOT NULL DEFAULT 'content',
		`block_position` char(255) NOT NULL DEFAULT 'after',
		`webform_title` char(255) NOT NULL DEFAULT 'Webform',
		`url` varchar(255) DEFAULT NULL,
		PRIMARY KEY (`id`),
		UNIQUE KEY `index_getresponse_customs_on_id_webforms` (`id_shop`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `{$installer->getTable('getresponse_automations')}` (
		`id` int(6) NOT NULL AUTO_INCREMENT,
		`id_shop` int(6) NOT NULL,
		`category_id` int(6) NOT NULL,
		`campaign_id` char(32) NOT NULL,
		`action` char(32) NOT NULL DEFAULT 'move',
		`cycle_day` char(5) DEFAULT '',
		`active` tinyint(1) NULL DEFAULT 1,
		PRIMARY KEY (`id`),
		UNIQUE KEY `index_getresponse_automations_on_id_shop` (`id_shop`,`category_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

foreach ($allStores as $id_shop) {
	$installer->run("
	INSERT INTO `{$installer->getTable('getresponse_settings')}` (
	`id_shop` ,
	`active_subscription` ,
	`update_address`
	)
	VALUES (
	" . $id_shop . ", 0, 0
	)
	ON DUPLICATE KEY UPDATE
	`id_shop` = `id_shop`;

	INSERT INTO `{$installer->getTable('getresponse_account')}` (
	`id_shop`
	)
	VALUES (
	" . $id_shop . "
	)
	ON DUPLICATE KEY UPDATE
	`id_shop` = `id_shop`;

	INSERT INTO `{$installer->getTable('getresponse_webforms')}` (
	`id_shop`
	)
	VALUES (
	" . $id_shop . "
	)
	ON DUPLICATE KEY UPDATE
	`id_shop` = `id_shop`;

	INSERT INTO `{$installer->getTable('getresponse_customs')}` (
	`id_shop` ,
	`custom_field`,
	`custom_value`,
	`default`,
	`active_custom`
	)
	VALUES
	(" . $id_shop . ", 'firstname', 'firstname', '1', '1'),
	(" . $id_shop . ", 'lastname', 'lastname', '1', '1'),
	(" . $id_shop . ", 'email', 'email', '1', '1'),
	(" . $id_shop . ", 'street', 'street', '0', '0'),
	(" . $id_shop . ", 'postcode', 'postcode', '0', '0'),
	(" . $id_shop . ", 'city', 'city', '0', '0'),
	(" . $id_shop . ", 'telephone', 'telephone', '0', '0'),
	(" . $id_shop . ", 'country', 'country', '0', '0'),
	(" . $id_shop . ", 'birthday', 'birthday', '0', '0'),
	(" . $id_shop . ", 'company', 'company', '0', '0')
	ON DUPLICATE KEY UPDATE
	`id_custom` = `id_custom`;
	");

}

$installer->endSetup();
