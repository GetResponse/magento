<?php

/**
 * @var $installer Mage_Core_Model_Resource_Setup
 */
$installer = $this;
$installer->startSetup();

try {

    $installer->run("
      CREATE TABLE IF NOT EXISTS `{$installer->getTable('getresponse_product_map')}` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `gr_shop_id` varchar(32) NOT NULL,
        `entity_id` int(10) unsigned NOT NULL,
        `gr_product_id` varchar(32) NOT NULL,
        PRIMARY KEY (`id`),
        KEY `entity_id` (`entity_id`),
        CONSTRAINT `getresponse_product_map_ibfk_1` FOREIGN KEY (`entity_id`) REFERENCES `catalog_product_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

    $installer->run("
        ALTER TABLE `{$installer->getTable('sales_flat_order')}` ADD `getresponse_order_id` VARCHAR(32) DEFAULT NULL;
        ALTER TABLE `{$installer->getTable('sales_flat_order')}` ADD `getresponse_order_md5` VARCHAR(64) DEFAULT NULL;
        ALTER TABLE `{$installer->getTable('sales_flat_quote')}` ADD `getresponse_cart_id` VARCHAR(32) DEFAULT NULL;
  ");

    $installer->run(
        "alter table getresponse_settings add column newsletter_cycle_day int after newsletter_campaign_id"
    );

} catch (\Exception $e) {
    Mage::helper('getresponse/logger')->logException($e);
}

$installer->endSetup();