<?php
use GetresponseIntegration_Getresponse_Domain_AccountRepository as AccountRepository;
use GetresponseIntegration_Getresponse_Domain_AccountFactory as AccountFactory;
use GetresponseIntegration_Getresponse_Domain_SettingsRepository as SettingsRepository;
use GetresponseIntegration_Getresponse_Domain_SettingsFactory as SettingsFactory;

require_once 'BaseController.php';

/**
 * Class GetresponseIntegration_Getresponse_AccountController
 */
class GetresponseIntegration_Getresponse_AccountController extends GetresponseIntegration_Getresponse_BaseController
{
    /**
     * GET getresponse/account/index
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->_title($this->__('API Key settings'))->_title($this->__('GetResponse'));

        if ($this->isConnectedToGetResponse()) {
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

        if (!$this->isConnectedToGetResponse()) {
            $this->redirectToLoginPage();
            return;
        }

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

        $this->grApiInstance()->api_key = $apiKey;
        $status = $this->grApiInstance()->checkApi($apiUrl, $apiDomain);

        if (empty($status) && !empty($apiDomain)) {
            $this->_getSession()->addError('Invalid domain');
            $this->_forward('index');
            return;
        } elseif (!empty($status->codeDescription)) {
            $this->_getSession()
                ->addError('The API key seems incorrect. Please check if you typed or pasted it correctly. If you recently generated a new key, please make sure you’re using the right one');
            $this->_forward('index');
            return;
        } elseif (empty($status['accountId'])) {
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

        try {
            $features = $this->grApiInstance()->getFeatures();

            if (isset($features['feature_tracking']) && 1 == $features['feature_tracking']) {
                $featureTracking = 1;
            }

            $data = array(
                'apiKey' => $apiKey,
                'apiUrl' => $apiUrl,
                'apiDomain' => $apiDomain,
                'hasGrTrafficFeatureEnabled' => $featureTracking
            );

            $trackingCode = $this->grApiInstance()->getTrackingCode();
            $trackingCode = is_array($trackingCode) ? reset($trackingCode) : array();

            if (!empty($trackingCode) && 0 < strlen($trackingCode['snippet'])) {
                $data['trackingCodeSnippet'] = $trackingCode['snippet'];
            }

            $settingsRepository = new SettingsRepository($this->currentShopId);
            $settings = SettingsFactory::createFromArray($data);

            if (false === $settingsRepository->create($settings)) {
                $this->_getSession()->addError('Error during settings details save');
            }
            $this->_redirect('*/*/index');

        } catch (GetresponseIntegration_Getresponse_Domain_GetresponseException $e) {}
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
    public function grApiInstance()
    {
        return GetresponseIntegration_Getresponse_Helper_Api::getApiInstance();
    }

}