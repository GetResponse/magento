<?php

/**
 * @var $installer Mage_Core_Model_Resource_Setup
 */
$installer = $this;
$installer->startSetup();

try {

    $installer->run("
        alter table getresponse_settings add column newsletter_cycle_day int after newsletter_campaign_id;
  ");

} catch (\Exception $e) {
    Mage::helper('getresponse/logger')->logException($e);
}

$installer->endSetup();