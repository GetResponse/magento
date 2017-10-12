<?php

/**
 * @var $installer Mage_Core_Model_Resource_Setup
 */
$installer = $this;
$installer->startSetup();

try {
    $installer->run("
    ALTER TABLE `{$installer->getTable('getresponse_settings')}` ADD `newsletter_subscription` TINYINT(1) NOT NULL DEFAULT 0;
    ALTER TABLE `{$installer->getTable('getresponse_settings')}` ADD `newsletter_campaign_id` CHAR(5)  NULL;
");
} catch (\Exception $e) {
    Mage::log($e->getMessage());
}

$installer->endSetup();
