<?php
use GetresponseIntegration_Getresponse_Domain_AccountRepository as AccountRepository;
use GetresponseIntegration_Getresponse_Domain_AccountFactory as AccountFactory;
use GetresponseIntegration_Getresponse_Domain_SettingsRepository as SettingsRepository;
use GetresponseIntegration_Getresponse_Domain_SettingsFactory as SettingsFactory;
use GetresponseIntegration_Getresponse_Domain_CustomFieldsCollectionFactory as CustomFieldsCollectionFactory;


require_once Mage::getModuleDir('controllers',
        'GetresponseIntegration_Getresponse') . DIRECTORY_SEPARATOR . 'BaseController.php';

class GetresponseIntegration_Getresponse_AccountController extends GetresponseIntegration_Getresponse_BaseController
{

    protected function _isAllowed()
    {
        return true;
    }

    /**
     * GET getresponse/account/index
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->_title($this->__('API Key settings'))->_title($this->__('GetResponse'));

        if ((!empty($this->settings->api['apiKey']))) {
            $this->displayAccountDataPage();
        } else {
            $this->displayConnectPage();
        }
    }

    /**
     * GET getresponse/account/disconnect
     */
    public function disconnectAction()
    {
        $this->_initAction();
        Mage::helper('getresponse')->disconnectIntegration($this->currentShopId);
        $this->_getSession()->addSuccess('GetResponse account disconnected');
        $this->_redirect('*/*/index');
    }

    /**
     * POST getresponse/account/connect
     */
    public function connectAction()
    {
        $apiKey = $this->getRequest()->getParam('api_key', '');

        if (empty($apiKey)) {
            $this->_getSession()->addError('You need to enter API key. This field can\'t be empty');
            $this->_forward('index');
            return;
        }

        $isMX = $this->getRequest()->getParam('getresponse_360_account');
        $apiUrl = ($isMX) ? $this->getRequest()->getParam('api_url') : null;
        $apiDomain = ($isMX) ? $this->getRequest()->getParam('api_domain') : null;

        $this->grapi()->api_key = $apiKey;
        $status = $this->grapi()->check_api($apiUrl, $apiDomain);

        $status_array = (array)$status;

        if (empty($status_array) && !empty($apiDomain)) {
            $this->_getSession()->addError('Invalid domain');
            $this->_forward('index');
            return;
        } elseif (!empty($status->codeDescription)) {
            $this->_getSession()
                ->addError('The API key seems incorrect. Please check if you typed or pasted it correctly. If you recently generated a new key, please make sure you’re using the right one');
            $this->_forward('index');
            return;
        } elseif (empty($status->accountId)) {
            $this->_getSession()->addError('Error - please try again');
            $this->_forward('index');
            return;
        }

        $accountRepository = new AccountRepository($this->currentShopId);
        $account = AccountFactory::createFromArray((array)$status);
        $accountRepository->create($account);

        Mage::register('api_key', $apiKey);
        Mage::getModel('getresponse/customs')->connectCustoms($this->currentShopId);
        
        $this->_getSession()->addSuccess('GetResponse account connected');

        $featureTracking = 0;
        $features = $this->grapi()->get_features();

        if ($features instanceof stdClass && 1 == $features->feature_tracking) {
            $featureTracking = 1;
        }

        $data = [
            'apiKey' => $apiKey,
            'apiUrl' => $apiUrl,
            'apiDomain' => $apiDomain,
            'hasGrTrafficFeatureEnabled' => $featureTracking
        ];

        // getting tracking code
        $trackingCode = (array)$this->grapi()->get_tracking_code();

        if (!empty($trackingCode) && is_object($trackingCode[0]) && 0 < strlen($trackingCode[0]->snippet)) {
            $data['trackingCodeSnippet'] = $trackingCode[0]->snippet;
        }

        $settingsRepository = new SettingsRepository($this->currentShopId);
        $settings = SettingsFactory::createFromArray($data);

        if (false === $settingsRepository->create($settings)) {
            $this->_getSession()->addError('Error during settings details save');
        }

        $this->_redirect('*/*/index');
    }

    protected function displayAccountDataPage()
    {
        $this->_addContent($this->getLayout()
            ->createBlock('Mage_Core_Block_Template', 'getresponse_content')
            ->setTemplate('getresponse/account.phtml')
            ->assign('settings', $this->settings)
        );
        $this->renderLayout();
    }

    protected function displayConnectPage()
    {
        $this->_addContent($this->getLayout()
            ->createBlock('Mage_Core_Block_Template', 'getresponse_content')
            ->setTemplate('getresponse/apikey.phtml')
            ->assign('settings', $this->settings)
        );
        $this->renderLayout();
    }

    /**
     * Getresponse API instance
     */
    public function grapi()
    {
        return GetresponseIntegration_Getresponse_Helper_Api::instance();
    }

}