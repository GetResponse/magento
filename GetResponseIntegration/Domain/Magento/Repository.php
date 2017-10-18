<?php
namespace GetResponse\GetResponseIntegration\Domain\Magento;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Account;
use GetResponse\GetResponseIntegration\Domain\GetResponse\GetResponseRepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Rule;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\ObjectManagerInterface;
use GetResponse\GetResponseIntegration\Helper\Config;

use GetResponse\GetResponseIntegration\Model\Automation as ModelAutomation;
use GetResponse\GetResponseIntegration\Model\Settings as ModelSettings;
use GetResponse\GetResponseIntegration\Model\Webform as ModelWebform;
use Magento\Store\Model\Store;
use Magento\Framework\App\Cache\Manager;

/**
 * Class Repository
 * @package GetResponse\GetResponseIntegration\Domain\Magento
 */
class Repository
{
    /** @var ObjectManagerInterface */
    private $_objectManager;

    /** @var ScopeConfigInterface */
    private $_scopeConfig;

    /** @var WriterInterface */
    private $configWriter;

    /** @var Manager */
    private $cacheManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $configWriter
     * @param Manager $cacheManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter,
        Manager $cacheManager
    )
    {
        $this->_objectManager = $objectManager;
        $this->_scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->cacheManager = $cacheManager;
    }

    /**
     * @return string
     */
    public function getShopId()
    {
        $id = $this->_scopeConfig->getValue(Config::CONFIG_DATA_SHOP_ID);
        return strlen($id) > 0 ? $id : '';
    }

    /**
     * @return string
     */
    public function getShopStatus()
    {
        $status = $this->_scopeConfig->getValue(Config::CONFIG_DATA_SHOP_STATUS);
        return 'enabled' === $status ? 'enabled' : 'disabled';
    }

    public function getCustomers()
    {
        $customers = $this->_objectManager->get('Magento\Customer\Model\Customer');
        return $customers->getCollection()->getData();
    }

    /**
     * @return mixed
     */
    public function getActiveCustoms()
    {
        $customs = $this->_objectManager->get('GetResponse\GetResponseIntegration\Model\Customs');
        return $customs->getCollection()->addFieldToFilter('active_custom', true);
    }

    /**
     * @return mixed
     */
    public function getDefaultCustoms()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $customs = $this->_objectManager->get('GetResponse\GetResponseIntegration\Model\Customs');
        return $customs->getCollection($storeId, 'id_shop');
    }

    /**
     * @return array
     */
    public function getRules()
    {
        return (array) json_decode($this->_scopeConfig->getValue(Config::CONFIG_DATA_AUTOMATION));
    }

    /**
     * @param Rule $rule
     */
    public function createRule(Rule $rule)
    {
        $rules = $this->getRules();
        $rule->setId(count($rules));
        $rules[] = $rule->asArray();

        $this->configWriter->save(
            Config::CONFIG_DATA_AUTOMATION,
            json_encode($rules),
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );

        $this->cacheManager->clean(['config']);
    }

    /**
     * @param $category_id
     * @return mixed
     */
    public function getCategoryName($category_id)
    {
        $_categoryHelper = $this->_objectManager->get('\Magento\Catalog\Model\Category');
        return $_categoryHelper->load($category_id)->getName();
    }

    /**
     * @param $category_id
     * @return array
     */
    public function getCategory($category_id)
    {
        $_categoryHelper = $this->_objectManager->get('\Magento\Catalog\Model\Category');
        $category = $_categoryHelper->load($category_id)->getData();
    }

    /**
     * @return mixed
     */
    public function getStoreCategories()
    {
        $_categoryHelper = $this->_objectManager->get('\Magento\Catalog\Helper\Category');
        $categories = $_categoryHelper->getStoreCategories(true, false, true);

        return $categories;
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $model = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Settings');
        return $model->load($storeId, 'id_shop')->getApiKey();
    }

    /**
     * @return array
     */
    public function getSnippetCode()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $settings = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Settings');

        return $settings->load($storeId, 'id_shop')->getData();
    }

    /**
     * @return mixed
     */
    public function getFullCustomersDetails()
    {
        $customers = $this->_objectManager->get('Magento\Customer\Model\Customer');
        $customers = $customers->getCollection()
            ->joinAttribute('street', 'customer_address/street', 'default_billing', null, 'left')
            ->joinAttribute('postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('country', 'customer_address/country_id', 'default_billing', null, 'left')
            ->joinAttribute('company', 'customer_address/company', 'default_billing', null, 'left')
            ->joinAttribute('birthday', 'customer/dob', 'entity_id', null, 'left')
            ->joinTable('newsletter_subscriber', 'customer_id=entity_id', ['subscriber_status'], '{{table}}.subscriber_status=1');
        return $customers;
    }

    /**
     * @return mixed
     */
    public function getSettings()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $settings = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Settings');
        return $settings->load($storeId, 'id_shop')->getData();
    }

    /**
     * @return mixed
     */
    public function getWebformSettings()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $webform_settings = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Webform');
        return $webform_settings->load($storeId, 'id_shop')->getData();
    }

    /**
     * @return array
     */
    public function getAccountInfo()
    {
        return (array) json_decode($this->_scopeConfig->getValue(Config::CONFIG_DATA_ACCOUNT));
    }

    public function updateAutomationStatus($id, $status)
    {
        $automation = $this->_objectManager->get('GetResponse\GetResponseIntegration\Model\Automation');
        $automation->load($id)->setActive($status)->save();
    }

    public function clearSettings()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();

        /** @var ModelSettings $settings */
        $settings = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Settings');
        $settings->load($storeId, 'id_shop')->delete();
    }

    public function clearAccount()
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_ACCOUNT,
            null,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );

        $this->cacheManager->clean(['config']);
    }

    public function clearWebforms()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();

        /** @var ModelWebform $webform */
        $webform = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Webform');
        $webform->load($storeId, 'id_shop')->delete();
    }

    public function clearAutomation()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();

        /** @var ModelAutomation $automation */
        $automation = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Automation');
        $rules = $automation->getCollection()->addFieldToFilter('id_shop', $storeId);
        foreach ($rules as $automation) {
            $automation->delete();
        }
    }

    public function getMagentoCountryCode()
    {
        return $this->_scopeConfig->getValue('general/locale/code');
    }

    public function getMagentoCurrencyCode()
    {
        $storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
        return $storeManager->getStore()->getCurrentCurrencyCode();

    }

    public function saveShopStatus($status)
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_SHOP_STATUS,
            $status,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );

        $this->cacheManager->clean(['config']);
    }

    public function saveShopId($shopId)
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_SHOP_ID,
            $shopId,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );

        $this->cacheManager->clean(['config']);
    }

    public function saveAccountDetails(Account $account)
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_ACCOUNT,
            json_encode($account->toArray()),
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );

        $this->cacheManager->clean(['config']);
    }

    public function updateWebform($publish, $webformUrl, $webformId, $sidebar)
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();

        $webform = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Webform');

        $webform->load($storeId, 'id_shop')
            ->setIdShop($storeId)
            ->setActiveSubscription($publish)
            ->setUrl($webformUrl)
            ->setWebformId($webformId)
            ->setSidebar($sidebar)
            ->save();
    }

    public function saveAllSettings($apiKey, $apiUrl, $domain, $trackingEnabled, $trackingCode)
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $settings = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Settings');

        $settings->load($storeId, 'id_shop')
            ->setApiKey($apiKey)
            ->setApiUrl($apiUrl)
            ->setApiDomain($domain)
            ->setIdShop($storeId)
            ->setFeatureTracking($trackingEnabled)
            ->setTrackingCodeSnippet($trackingCode)
            ->save();
    }

    public function getCustomFields($isDefault = false)
    {
        $model = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Customs');
        return $model->getCollection()->addFieldToFilter('default', $isDefault)->getData();
    }

    public function updateSettings($campaignId, $hasActiveSubscription, $isUpdated, $cycleDay)
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();

        $settings = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Settings');

        $settings->load($storeId, 'id_shop')
            ->setCampaignId($campaignId)
            ->setActiveSubscription($hasActiveSubscription)
            ->setUpdate($isUpdated)
            ->setCycleDay($cycleDay)
            ->save();
    }

    public function updateCustomField($id, $name, $isActive)
    {
        $custom = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Customs');

        $custom->load($id)->setCustomName($name)->setActiveCustom($isActive)->save();
    }

    public function updateWebTrafficStatus($status)
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $settings = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Settings');

        $settings->load($storeId, 'id_shop')
            ->setWebTraffic($status)
            ->save();
    }

    public function loadSubscriberByEmail($email)
    {
        $subscriber = $this->_objectManager
            ->create('Magento\Newsletter\Model\Subscriber')
            ->loadByEmail($email);

        return $subscriber;
    }

    public function loadOrder($id)
    {
        $order_object = $this->_objectManager->get('Magento\Sales\Model\Order');
        return $order_object->load($id);
    }

    public function loadCustomer($id)
    {
        $customer_object = $this->_objectManager->get('Magento\Customer\Model\Customer');
        return $customer_object->load($id);
    }

    public function loadCustomerAddress($id)
    {
        $address_object = $this->_objectManager->get('Magento\Customer\Model\Address');
        return $address_object->load($id);
    }

    /**
     * @param $id
     *
     * @return mixed|null
     */
    public function getRuleById($id)
    {
        if (empty($id)) {
            return null;
        }

        $rules = $this->getRules();

        if (0 === count($rules)) {
            return null;
        }

        foreach ($rules as $rule) {
            if ($rule->id == $id) {
                return $rule;
            }
        }

        return null;
    }

    /**
     * @param int $id
     * @param Rule $rule
     * @throws GetResponseRepositoryException
     */
    public function updateRule($id, Rule $rule)
    {
        $rules = $this->getRules();

        if (!isset($rules[$id])) {
            throw GetResponseRepositoryException::buildForInvalidRuleId();
        }

        $rules[$id]->category =$rule->getCategory();
        $rules[$id]->action =$rule->getAction();
        $rules[$id]->campaign =$rule->getCampaign();
        $rules[$id]->cycle_day =$rule->getAutoresponder();

        $this->configWriter->save(
            Config::CONFIG_DATA_AUTOMATION,
            json_encode($rules),
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );

        $this->cacheManager->clean(['config']);
    }

    /**
     * @param int $id
     * @throws GetResponseRepositoryException
     */
    public function deleteRule($id)
    {
        $rules = $this->getRules();

        if (!isset($rules[$id])) {
            throw GetResponseRepositoryException::buildForInvalidRuleId();
        }

        unset($rules[$id]);

        $this->configWriter->save(
            Config::CONFIG_DATA_AUTOMATION,
            json_encode($rules),
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );

        $this->cacheManager->clean(['config']);
    }
}
