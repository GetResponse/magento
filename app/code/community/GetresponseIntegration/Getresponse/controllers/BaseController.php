<?php


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

    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->settingsHandler();
        $this->loadLayout();

        if (empty($this->settings->api['api_key'])) {

            if ('account' !== $this->getRequest()->getControllerName() || 'index' !== $this->getRequest()
                    ->getActionName()) {
                $this->_getSession()->addError('Access denied - module is not connected to GetResponse Account.');
                $this->getResponse()->setRedirect($this->getUrl('getresponse/account/index'))->sendResponse();
                exit;
            }
        }

        return $this;
    }

    /**
     * Main extenction settings
     */
    protected function settingsHandler()
    {
        $this->settings->main['api_url_360_com'] = 'https://api3.getresponse360.com/v3';
        $this->settings->main['api_url_360_pl'] = 'https://api3.getresponse360.pl/v3';

        $this->settings->api = Mage::getModel('getresponse/settings')->load($this->currentShopId)->getData();

        Mage::helper('getresponse/api')->setApiDetails(
            $this->settings->api['api_key'],
            $this->settings->api['api_url'],
            $this->settings->api['api_domain']
        );

        if (!empty($this->settings->api['api_key'])) {
            $this->settings->api['encrypted_api_key'] = str_repeat("*",
                    strlen($this->settings->api['api_key']) - 6) . substr($this->settings->api['api_key'], -6);
        }

        $this->settings->account = Mage::getModel('getresponse/account')->load($this->currentShopId)->getData();
        $this->settings->customs = Mage::getModel('getresponse/customs')->getCustoms($this->currentShopId);
        $this->settings->webforms_settings =
            Mage::getModel('getresponse/webforms')->load($this->currentShopId)->getData();
    }

    /**
     * @return array
     */
    protected function prepareCustomsForMapping()
    {
        $grCustoms = $grCustomValues = [];

        if (empty($this->settings->customs)) {
            return [
                'customs' => '',
                'custom_values' => ''
            ];
        }

        foreach ($this->settings->customs as $custom) {

            if (in_array($custom['custom_field'], ['firstname', 'lastname', 'email'])) {
                continue;
            }

            $grCustomValues[] = $custom['custom_field'];
            $grCustoms[] = $custom;
        }

        return [
            'customs' => json_encode($grCustoms),
            'custom_values' => json_encode($grCustomValues)
        ];
    }

}