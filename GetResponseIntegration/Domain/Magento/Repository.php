<?php
namespace GetResponse\GetResponseIntegration\Domain\Magento;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\ObjectManagerInterface;
use GetResponse\GetResponseIntegration\Helper\Config;

use GetResponse\GetResponseIntegration\Model\Account as ModelAccount;
use GetResponse\GetResponseIntegration\Model\Automation as ModelAutomation;
use GetResponse\GetResponseIntegration\Model\Settings as ModelSettings;
use GetResponse\GetResponseIntegration\Model\Webform as ModelWebform;
use Magento\Store\Model\Store;

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

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $configWriter
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter
    )
    {
        $this->_objectManager = $objectManager;
        $this->_scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
    }

    /**
     * @return string
     */
    public function getShopId()
    {
        $id = $this->_scopeConfig->getValue(Config::SHOP_ID);
        return strlen($id) > 0 ? $id : '';
    }

    /**
     * @return string
     */
    public function getShopStatus()
    {
        $status = $this->_scopeConfig->getValue(Config::SHOP_STATUS);
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
     * @return mixed
     */
    public function getAutomations()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $settings = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Automation');
        return $settings->getCollection()
            ->addFieldToFilter('id_shop', $storeId);
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

    public function getAutomationByParam($value, $param)
    {
        $automation = $this->_objectManager->get('GetResponse\GetResponseIntegration\Model\Automation');
        return $automation->load($value, $param)->getData();
    }

    public function deleteAutomation($id)
    {
        $automation = $this->_objectManager->get('GetResponse\GetResponseIntegration\Model\Automation');
        $automation->load($id, 'id')->delete();
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
     * @return mixed
     */
    public function getAccountInfo()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $account = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Account');
        return $account->load($storeId, 'id_shop');
    }

    public function createAutomation($data)
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $automation = $this->_objectManager->get('GetResponse\GetResponseIntegration\Model\Automation');

        $cycle_day = (isset($data['gr_autoresponder']) && $data['gr_autoresponder'] == 1 && isset($data['cycle_day'])) ? $data['cycle_day'] : '';

        $automation->setIdShop($storeId)
            ->setCategoryId($data['category'])
            ->setCampaignId($data['campaign_id'])
            ->setActive(1)
            ->setCycleDay($cycle_day)
            ->setAction($data['action'])
            ->save();
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
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();

        /** @var ModelAccount $account */
        $account = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Account');
        $account->load($storeId, 'id_shop')->delete();
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
        $automations = $automation->getCollection()->addFieldToFilter('id_shop', $storeId);
        foreach ($automations as $automation) {
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
            Config::SHOP_STATUS,
            $status,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID

        );
    }

    public function saveShopId($shopId)
    {
        $this->configWriter->save(
            Config::SHOP_ID,
            $shopId,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID

        );
    }
}

