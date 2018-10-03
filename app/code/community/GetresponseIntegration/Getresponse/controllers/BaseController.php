<?php

use GetresponseIntegration_Getresponse_Domain_AccountRepository as AccountRepository;
use GetresponseIntegration_Getresponse_Domain_SettingsRepository as SettingsRepository;
use GetresponseIntegration_Getresponse_Domain_WebformRepository as WebformRepository;

/**
 * Class GetresponseIntegration_Getresponse_BaseController
 */
class GetresponseIntegration_Getresponse_BaseController extends Mage_Adminhtml_Controller_Action
{
    /** @var int */
    public $currentShopId;

    /** @var array */
    public $settings;

    /** @var GetresponseIntegration_Getresponse_Helper_Api */
    protected $api;

    protected function _construct()
    {
        $this->currentShopId = Mage::helper('getresponse')->getStoreId();
        $this->settings = new stdClass();
        $this->api = Mage::helper('getresponse/api');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }

    protected function _initAction()
    {
        $this->settingsHandler();
        $this->loadLayout();
    }

    /**
     * @return bool
     */
    protected function isConnectedToGetResponse()
    {
        return !empty($this->settings->api['apiKey']);
    }

    protected function redirectToLoginPage()
    {
        $this->_getSession()->addError('Access denied - module is not connected to GetResponse Account');
        $this->getResponse()->setRedirect($this->getUrl('getresponse/account/index'))->sendResponse();
    }

    /**
     * Main extenction settings
     */
    protected function settingsHandler()
    {
        $this->settings->main['api_url_360_com'] = 'https://api3.getresponse360.com/v3';
        $this->settings->main['api_url_360_pl'] = 'https://api3.getresponse360.pl/v3';

        $shopId = Mage::helper('getresponse')->getStoreId();
        $settingsRepository = new SettingsRepository($shopId);
        $webformRepository = new WebformRepository($shopId);

        $this->settings->api = $settingsRepository->getAccount();

        Mage::helper('getresponse/api')->setApiDetails(
            $this->settings->api['apiKey'],
            $this->settings->api['apiUrl'],
            $this->settings->api['apiDomain']
        );

        if (!empty($this->settings->api['apiKey'])) {
            $this->settings->api['encrypted_api_key'] = str_repeat("*",
                    strlen($this->settings->api['apiKey']) - 6) . substr($this->settings->api['apiKey'], -6);
        }

        $accountRepository = new AccountRepository($this->currentShopId);
        $this->settings->account = $accountRepository->getAccount()->toArray();
        $this->settings->customs = Mage::getModel('getresponse/customs')->getCustoms($this->currentShopId);
        $this->settings->webforms_settings = $webformRepository->getWebform()->toArray();
    }

    /**
     * @return array
     */
    protected function prepareCustomsForMapping()
    {
        $grCustoms = $grCustomValues = array();

        if (empty($this->settings->customs)) {
            return array(
                'customs' => '',
                'custom_values' => ''
            );
        }

        foreach ($this->settings->customs as $custom) {

            if (in_array($custom['custom_field'], array('firstname', 'lastname', 'email')
            )) {
                continue;
            }

            $grCustomValues[] = $custom['custom_field'];
            $grCustoms[] = $custom;
        }

        return array(
            'customs' => json_encode($grCustoms),
            'custom_values' => json_encode($grCustomValues)
        );
    }
}
