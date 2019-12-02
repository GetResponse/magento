<?php

/**
 * @var $installer Mage_Core_Model_Resource_Setup
 */
$installer = $this;
$installer->startSetup();

try {
    $installer->run("ALTER TABLE `{$installer->getTable('sales_flat_order')}` CHANGE `getresponse_order_md5` `getresponse_order_hash` VARCHAR(256);");
} catch (\Exception $e) {
    Mage::helper('getresponse/logger')->logException($e);
}

$installer->endSetup();
