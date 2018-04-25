<?php

/**
 * @var $installer Mage_Core_Model_Resource_Setup
 */
$installer = $this;
$installer->startSetup();

try {
    $installer->run("
        CREATE TABLE `{$installer->getTable('getresponse_schedule_jobs_queue')}` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `customer_id` varchar(8) DEFAULT NULL,
          `type` varchar(16) DEFAULT NULL,
          `payload` text,
          `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
} catch (\Exception $e) {
    Mage::helper('getresponse/logger')->logException($e);
}

try {
    $installer->run("ALTER TABLE `{$installer->getTable('getresponse_product_map')}` ADD UNIQUE INDEX `unique_product_in_shop` (`gr_shop_id`, `entity_id`);");
} catch (\Exception $e) {
    Mage::helper('getresponse/logger')->logException($e);
}

$installer->endSetup();
