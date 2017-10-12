<?php

/**
* @var $installer Mage_Core_Model_Resource_Setup
*/
$installer = $this;
$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS `{$installer->getTable('getresponse_shops')}` (
		`id` int(6) NOT NULL AUTO_INCREMENT,
		`shop_id` VARCHAR(32) NOT NULL,
		`gr_shop_id` VARCHAR(32) NOT NULL,
		`is_enabled` TINYINT(1) NOT NULL,
		PRIMARY KEY (`id`),
		UNIQUE KEY `index_getresponse_shop` (`shop_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

try {
    $installer->run("
    ALTER TABLE `{$installer->getTable('getresponse_settings')}` ADD `subscription_on_checkout` TINYINT(1) NOT NULL;
    ALTER TABLE `{$installer->getTable('getresponse_settings')}` ADD `has_gr_traffic_feature_enabled` TINYINT(1)  NULL;
    ALTER TABLE `{$installer->getTable('getresponse_settings')}` ADD  `has_active_traffic_module` TINYINT(1)  NOT NULL;
    ALTER TABLE `{$installer->getTable('getresponse_settings')}` ADD  `tracking_code_snippet` TEXT  NULL  DEFAULT NULL;
");
} catch (\Exception $e) {
    Mage::log($e->getMessage());
}


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

foreach ($allStores as $id_shop) {
    $installer->run("
        INSERT INTO `{$installer->getTable('getresponse_shops')}` (
        `shop_id` 
        ) VALUES (
        " . $id_shop . "
        )
        ON DUPLICATE KEY UPDATE
        `shop_id` = `shop_id`;
	");
}

$installer->endSetup();
