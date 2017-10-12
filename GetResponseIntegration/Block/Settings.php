<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Helper\GetResponseAPI3;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Settings
 * @package GetResponse\GetResponseIntegration\Block
 */
class Settings extends GetResponse
{
    /**
     * Settings constructor.
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(Context $context, ObjectManagerInterface $objectManager)
    {
        parent::__construct($context, $objectManager);
    }

    /**
     * @return mixed
     */
    public function getCustomers()
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

    /**
     * @return array
     */
    public function getAllFormsFromGr()
    {
        $settings = $this->getSettings();
        $forms = [];

        if (!isset($settings['api_key'])) {
            return $forms;
        }

        $newForms = $this->getclient()->getForms(['query' => ['status' => 'enabled']]);
        foreach ($newForms as $form) {
            if ($form->status == 'published') {
                $forms['forms'][] = $form;
            }
        }
        $oldWebforms = $this->getclient()->getWebForms();
        foreach ($oldWebforms as $webform) {
            if ($webform->status == 'enabled') {
                $forms['webforms'][] = $webform;
            }
        }

        return $forms;
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
     * @return bool
     */
    public function getLastPostedApiKey()
    {
        $data = $this->getRequest()->getPostValue();
        if (!empty($data)) {
            if (isset($data['getresponse_api_key'])) {
                return $data['getresponse_api_key'];
            }
        }
        return false;
    }

    /**
     * @return string
     */
    public function getHiddenApiKey()
    {
        $apiKey = $this->getApiKey();
        return strlen($apiKey) > 0 ? str_repeat("*", strlen($apiKey) - 6) . substr($apiKey, -6) : '';
    }

    /**
     * @return int
     */
    public function getLastPostedApiAccount()
    {
        $data = $this->getRequest()->getPostValue();
        if (!empty($data['getresponse_360_account']) && 1 == $data['getresponse_360_account']) {
            return $data['getresponse_360_account'];
        }
        return 0;
    }

    /**
     * @return bool
     */
    public function getLastPostedApiUrl()
    {
        $data = $this->getRequest()->getPostValue();
        if (!empty($data['getresponse_api_url'])) {
            return $data['getresponse_api_url'];
        }
        return false;
    }

    /**
     * @return bool
     */
    public function getLastPostedApiDomain()
    {
        $data = $this->getRequest()->getPostValue();
        if (!empty($data['getresponse_api_domain'])) {
            return $data['getresponse_api_domain'];
        }
        return false;
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
     * @return bool|int
     */
    public function checkApiKey()
    {
        if (empty($this->getApiKey())) {
            return 0;
        }

        $response = $this->getClient()->ping();

        if (isset($response->accountId)) {
            return true;
        } else {
            return false;
        }
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
}
