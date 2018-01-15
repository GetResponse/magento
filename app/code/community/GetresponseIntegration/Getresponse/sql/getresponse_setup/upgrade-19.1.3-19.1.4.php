<?php

use GetresponseIntegration_Getresponse_Domain_AccountFactory as AccountFactory;
use GetresponseIntegration_Getresponse_Domain_AutomationRulesCollectionFactory as AutomationRulesCollectionFactory;
use GetresponseIntegration_Getresponse_Domain_CustomFieldsCollectionFactory as CustomFieldsCollectionFactory;
use GetresponseIntegration_Getresponse_Domain_SettingsFactory as SettingsFactory;
use GetresponseIntegration_Getresponse_Domain_ShopFactory as ShopFactory;
use GetresponseIntegration_Getresponse_Domain_WebformFactory as WebformFactory;
use GetresponseIntegration_Getresponse_Domain_AccountRepository as AccountRepository;
use GetresponseIntegration_Getresponse_Domain_AutomationRulesCollectionRepository as AutomationRulesCollectionRepository;
use GetresponseIntegration_Getresponse_Domain_CustomFieldsCollectionRepository as CustomFieldsCollectionRepository;
use GetresponseIntegration_Getresponse_Domain_SettingsRepository as SettingsRepository;
use GetresponseIntegration_Getresponse_Domain_ShopRepository as ShopRepository;
use GetresponseIntegration_Getresponse_Domain_WebformRepository as WebformRepository;

/**
 * @var $installer Mage_Core_Model_Resource_Setup
 */
$installer = $this;
$installer->startSetup();
$resource = Mage::getSingleton('core/resource');
$currentShopId = Mage::app()->getStore()->getStoreId();

/**
 * Retrieve the read connection
 */
$readConnection = $resource->getConnection('core_read');

$accountDb = $readConnection->fetchAll("SELECT * FROM " . 'getresponse_account');
$account = AccountFactory::createFromArray($accountDb[0]);

$automationsDb = $readConnection->fetchAll("SELECT * FROM " . 'getresponse_automations');

foreach ($automationsDb as $key => $automation) {
    $automationsDb[$key]['categoryId'] = $automation['category_id'];
    $automationsDb[$key]['campaignId'] = $automation['campaign_id'];
    $automationsDb[$key]['cycleDay'] = $automation['cycle_day'];
}
$automations = AutomationRulesCollectionFactory::createFromArray($automationsDb);

$customsDb = $readConnection->fetchAll("SELECT * FROM " . 'getresponse_customs');

foreach ($customsDb as $key => $custom) {
    $customsDb[$key]['id'] = $custom['id_custom'];
    $customsDb[$key]['customField'] = $custom['custom_field'];
    $customsDb[$key]['customValue'] = $custom['custom_value'];
    $customsDb[$key]['isDefault'] = $custom['default'];
    $customsDb[$key]['isActive'] = $custom['active_custom'];
}
$customs = CustomFieldsCollectionFactory::createFromArray($customsDb);

$settingsDb = $readConnection->fetchAll("SELECT * FROM " . 'getresponse_settings');
$settings = SettingsFactory::createFromArray(array(
    'apiKey' => $settingsDb[0]['api_key'],
    'apiUrl' => $settingsDb[0]['api_url'],
    'apiDomain' => $settingsDb[0]['api_domain'],
    'activeSubscription' => $settingsDb[0]['active_subscription'],
    'updateAddress' => $settingsDb[0]['update_address'],
    'campaignId' => $settingsDb[0]['campaign_id'],
    'cycleDay' => $settingsDb[0]['cycle_day'],
    'subscriptionOnCheckout' => $settingsDb[0]['subscription_on_checkout'],
    'hasGrTrafficFeatureEnabled' => $settingsDb[0]['has_gr_traffic_feature_enabled'],
    'hasActiveTrafficModule' => $settingsDb[0]['has_active_traffic_module'],
    'trackingCodeSnippet' => $settingsDb[0]['tracking_code_snippet'],
    'newsletterSubscription' => $settingsDb[0]['newsletter_subscription'],
    'newsletterCampaignId' => $settingsDb[0]['newsletter_campaign_id'],
    'newsletterCycleDay' => $settings[0]['newsletter_cycle_day']
));

$shopsDb = $readConnection->fetchAll("SELECT * FROM " . 'getresponse_shops');
$shops = ShopFactory::createFromArray(array(
    'grShopId' => $shopsDb['gr_shop_id'],
    'isEnabled' => $shopsDb['is_enabled']
));

$webformsDb = $readConnection->fetchAll("SELECT * FROM " . 'getresponse_webforms');
$webforms = WebformFactory::createFromArray(array(
    'webformId' => $webformsDb[0]['webform_id'],
    'activeSubscription' => $webformsDb[0]['active_subscription'],
    'layoutPosition'    => $webformsDb[0]['layout_position'],
    'blockPosition'     => $webformsDb[0]['block_position'],
    'webformTitle'      => $webformsDb[0]['webform_title'],
    'url'               => $webformsDb[0]['url']
));

$accountRepository = new AccountRepository($currentShopId);
$accountRepository->create($account);

$automationRepository = new AutomationRulesCollectionRepository($currentShopId);
$automationRepository->create($automations);

$customsRepository = new CustomFieldsCollectionRepository($currentShopId);
$customsRepository->create($customs);

$settingsRepository = new SettingsRepository($currentShopId);
$settingsRepository->create($settings);

$shopRepository = new ShopRepository($currentShopId);
$shopRepository->create($shops);

$webformRepository = new WebformRepository($currentShopId);
$webformRepository->create($webforms);

$installer->run('DROP TABLE getresponse_account');
$installer->run('DROP TABLE getresponse_automations');
$installer->run('DROP TABLE getresponse_customs');
$installer->run('DROP TABLE getresponse_settings');
$installer->run('DROP TABLE getresponse_shops');
$installer->run('DROP TABLE getresponse_webforms');
