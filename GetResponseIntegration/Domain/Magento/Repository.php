<?php
namespace GetResponse\GetResponseIntegration\Domain\Magento;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Account;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Rule;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\ObjectManagerInterface;
use GetResponse\GetResponseIntegration\Helper\Config;
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
    ) {
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

    /**
     * @return mixed
     */
    public function getCustomers()
    {
        $customers = $this->_objectManager->get('Magento\Customer\Model\Customer');

        return $customers->getCollection()->getData();
    }

    /**
     * @param int $category_id
     * @return mixed
     */
    public function getCategoryName($category_id)
    {
        $_categoryHelper = $this->_objectManager->get('\Magento\Catalog\Model\Category');

        return $_categoryHelper->load($category_id)->getName();
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
            ->joinTable(
                'newsletter_subscriber',
                'customer_id=entity_id',
                ['subscriber_status'],
                '{{table}}.subscriber_status=1'
            );

        return $customers;
    }

    /**
     * @return array
     */
    public function getAccountInfo()
    {
        return (array)json_decode($this->_scopeConfig->getValue(Config::CONFIG_DATA_ACCOUNT));
    }

    /**
     * @return string
     */
    public function getMagentoCountryCode()
    {
        return $this->_scopeConfig->getValue('general/locale/code');
    }

    /**
     * @return string
     */
    public function getMagentoCurrencyCode()
    {
        $storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');

        return $storeManager->getStore()->getCurrentCurrencyCode();
    }

    /**
     * @param string $email
     *
     * @return mixed
     */
    public function loadSubscriberByEmail($email)
    {
        $subscriber = $this->_objectManager
            ->create('Magento\Newsletter\Model\Subscriber')
            ->loadByEmail($email);

        return $subscriber;
    }

    /**
     * @param int $id
     *
     * @return mixed
     */
    public function loadOrder($id)
    {
        $order_object = $this->_objectManager->get('Magento\Sales\Model\Order');

        return $order_object->load($id);
    }

    /**
     * @param int $id
     *
     * @return mixed
     */
    public function loadCustomer($id)
    {
        $customer_object = $this->_objectManager->get('Magento\Customer\Model\Customer');

        return $customer_object->load($id);
    }

    /**
     * @param int $id
     *
     * @return mixed
     */
    public function loadCustomerAddress($id)
    {
        $address_object = $this->_objectManager->get('Magento\Customer\Model\Address');

        return $address_object->load($id);
    }

    /**
     * @param ConnectionSettings $settings
     */
    public function saveConnectionSettings(ConnectionSettings $settings)
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_CONNECTION_SETTINGS,
            json_encode($settings->toArray()),
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );

        $this->cacheManager->clean(['config']);
    }

    /**
     * @return array
     */
    public function getConnectionSettings()
    {
        return (array)json_decode($this->_scopeConfig->getValue(Config::CONFIG_DATA_CONNECTION_SETTINGS));
    }

    /**
     * @param int $id
     */
    public function deleteRule($id)
    {
        if (empty($id)) {
            return;
        }

        $rules = $this->getRules();

        if (0 === count($rules)) {
            return;
        }

        foreach ($rules as $i => $rule) {
            if ($rule->id == $id) {
                unset($rules[$i]);
            }
        }

        $this->configWriter->save(
            Config::CONFIG_DATA_RULES,
            json_encode($rules),
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );

        $this->cacheManager->clean(['config']);
    }

    /**
     * @param Rule $rule
     */
    public function createRule(Rule $rule)
    {
        $rules = $this->getRules();
        $rules[] = $rule->asArray();

        $this->configWriter->save(
            Config::CONFIG_DATA_RULES,
            json_encode($rules),
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );

        $this->cacheManager->clean(['config']);
    }

    /**
     * @return array
     */
    public function getRules()
    {
        return (array)json_decode($this->_scopeConfig->getValue(Config::CONFIG_DATA_RULES));
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
     * @throws RepositoryException
     */
    public function updateRule($id, Rule $rule)
    {
        $rules = $this->getRules();

        if (empty($rules)) {
            return;
        }

        /** @var  $_rule */
        foreach ($rules as $_rule) {
            if ($_rule->id === $id) {
                $_rule->category = $rule->getCategory();
                $_rule->action = $rule->getAction();
                $_rule->campaign = $rule->getCampaign();
                $_rule->cycle_day = $rule->getAutoresponder();
            }
        }

        $this->configWriter->save(
            Config::CONFIG_DATA_RULES,
            json_encode($rules),
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );

        $this->cacheManager->clean(['config']);
    }

    /**
     * @param WebEventTrackingSettings $webEventTracking
     */
    public function saveWebEventTracking(WebEventTrackingSettings $webEventTracking)
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_WEB_EVENT_TRACKING,
            json_encode($webEventTracking->toArray()),
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );

        $this->cacheManager->clean(['config']);
    }

    /**
     * @return array
     */
    public function getWebEventTracking()
    {
        return (array)json_decode($this->_scopeConfig->getValue(Config::CONFIG_DATA_WEB_EVENT_TRACKING));
    }

    /**
     * @param string $status
     */
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

    /**
     * @param string $shopId
     *
     */
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

    /**
     * @param Account $account
     */
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

    /**
     * @return array
     */
    public function getRegistrationSettings()
    {
        return (array)json_decode($this->_scopeConfig->getValue(Config::CONFIG_DATA_REGISTRATION_SETTINGS));
    }

    /**
     * @param RegistrationSettings $registrationSettings
     */
    public function saveRegistrationSettings(RegistrationSettings $registrationSettings)
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_REGISTRATION_SETTINGS,
            json_encode($registrationSettings->toArray()),
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );
        $this->cacheManager->clean(['config']);
    }

    /**
     * @return array
     */
    public function getCustoms()
    {
        return (array)json_decode($this->_scopeConfig->getValue(Config::CONFIG_DATA_REGISTRATION_CUSTOMS));
    }

    /**
     * @param array $data
     */
    public function setCustomsOnInit(array $data)
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_REGISTRATION_CUSTOMS,
            json_encode($data),
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );

        $this->cacheManager->clean(['config']);
    }

    /**
     * @param CustomFieldsCollection $customsCollection
     */
    public function updateCustoms(CustomFieldsCollection $customsCollection)
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_REGISTRATION_CUSTOMS,
            json_encode($customsCollection->toArray()),
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );

        $this->cacheManager->clean(['config']);
    }

    public function clearAccount()
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_ACCOUNT,
            null,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );
    }

    public function clearWebEventTracking()
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_WEB_EVENT_TRACKING,
            null,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );
    }

    public function clearWebforms()
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_WEBFORMS,
            null,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );
    }

    public function clearConnectionSettings()
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_CONNECTION_SETTINGS,
            '',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );
    }

    public function clearRules()
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_RULES,
            '',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );
    }

    public function clearAccountDetails()
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_ACCOUNT,
            null,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );
    }

    public function clearRegistrationSettings()
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_REGISTRATION_SETTINGS,
            null,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );
    }

    public function clearCustoms()
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_REGISTRATION_CUSTOMS,
            null,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );
    }

    /**
     * @param WebformSettings $webform
     */
    public function saveWebformSettings(WebformSettings $webform)
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_WEBFORMS,
            json_encode($webform->toArray()),
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );
    }

    /**
     * @return array
     */
    public function getWebformSettings()
    {
        return (array)json_decode($this->_scopeConfig->getValue(Config::CONFIG_DATA_WEBFORMS));
    }

    /**
     * @return string
     */
    public function getUnauthorizedApiCallDate()
    {
        return $this->_scopeConfig->getValue(Config::CONFIG_DATA_UNAUTHORIZED_API_CALL_DATE);
    }


    /**
     * @param string $value
     */
    public function setUnauthorizedApiCallDate($value)
    {
        $this->configWriter->save(
            Config::CONFIG_DATA_UNAUTHORIZED_API_CALL_DATE,
            $value,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );
    }

    public function clearDatabase()
    {
        $this->clearConnectionSettings();
        $this->clearRegistrationSettings();
        $this->clearAccountDetails();
        $this->clearWebforms();
        $this->clearRules();
        $this->clearWebEventTracking();
        $this->clearCustoms();
    }
}
