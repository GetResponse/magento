<?php

use GetresponseIntegration_Getresponse_Domain_SettingsRepository as SettingsRepository;
use GetresponseIntegration_Getresponse_Domain_WebformRepository as WebformRepository;
use GetresponseIntegration_Getresponse_Domain_GetresponseException as GetresponseException;

/**
 * Getresponse module observer
 *
 * @author Magento
 */
class GetresponseIntegration_Getresponse_Model_Observer
{
    /** @var string */
    private $shopId;

    /** @var GetresponseIntegration_Getresponse_Helper_Data */
    private $getresponseHelper;

    /** @var Mage_Newsletter_Model_Subscriber */
    private $newsletterModel;

    /** @var GetresponseIntegration_Getresponse_Model_Customs  */
    private $customsModel;

    public function __construct()
    {
        $this->getresponseHelper = Mage::helper('getresponse');
        $this->shopId = $this->getresponseHelper->getStoreId();
        $this->newsletterModel = Mage::getModel('newsletter/subscriber');
        $this->customsModel = Mage::getModel('getresponse/customs');
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function addTrackingCodeToHeader(Varien_Event_Observer $observer)
    {
        if (!$this->getresponseHelper->isEnabled()) {
            return;
        }

        $settingsRepository = new SettingsRepository($this->shopId);
        $accountSettings = $settingsRepository->getAccount();

        if (empty($accountSettings['apiKey']) || 0 === (int) $accountSettings['hasGrTrafficFeatureEnabled'] || 0 === (int) $accountSettings['hasActiveTrafficModule']) {
            return;
        }

        $layout = Mage::app()->getLayout();
        /* @var $block Mage_Page_Block_Html_Head */
        $block = $observer->getEvent()->getBlock();

        if ("head" == $block->getNameInLayout()) {
            /** @var Mage_Core_Block_Text $myBlock */
            $myBlock = $layout->createBlock('core/text');
            $myBlock->setText($accountSettings['trackingCodeSnippet']);

            $block->append($myBlock);
        }

        if ("footer" == $block->getNameInLayout() && Mage::getSingleton('customer/session')->isLoggedIn()) {

            $customer = Mage::getSingleton('customer/session')->getCustomer();

            if (strlen($customer->email) > 0) {
                /** @var Mage_Core_Block_Text $myBlock */
                $myBlock = $layout->createBlock('core/text');
                $myBlock->setText('<script type="text/javascript">
				if(window.addEventListener){
				  window.addEventListener("load", function() { gaSetUserId("' . $customer->email . '"); })
				}else{
				  window.attachEvent("onload", function() { gaSetUserId("' . $customer->email . '"); } )
				}
			</script>');
                $block->append($myBlock);
            }
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function addJQueryToHeader(Varien_Event_Observer $observer)
    {
        if (!$this->getresponseHelper->isEnabled()) {
            return;
        }

        /* @var $block Mage_Page_Block_Html_Head */
        $block = $observer->getEvent()->getBlock();

        if ("head" == $block->getNameInLayout()) {
            foreach ($this->getresponseHelper->getFiles() as $file) {
                $block->addJs($this->getresponseHelper->getJQueryPath($file));
            }
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function set_block(Varien_Event_Observer $observer)
    {
        if (!$this->getresponseHelper->isEnabled()) {
            return;
        }

        $settingsRepository = new SettingsRepository($this->shopId);
        $accountSettings = $settingsRepository->getAccount();
        $webformRepository = new WebformRepository($this->shopId);
        $webformSettings = $webformRepository->getWebform()->toArray();

        if (empty($accountSettings['apiKey'])) {
            return;
        }

        if (!empty($webformSettings) && $webformSettings['activeSubscription'] == 1 && !empty($webformSettings['url'])) {
            $sub_position = ($webformSettings['blockPosition'] == 'before') ? 'before="-"' : 'after="-"';

            $myXml = '<reference name="' . $webformSettings['layoutPosition'] . '">';
            $myXml .= '<block type="core/text_list"
							name="' . $webformSettings['layoutPosition'] . '.content"
							as="getresponse_webform_' . $webformSettings['layoutPosition'] . '"
							translate="label" ' . $sub_position . '>';
            $myXml .= '<block type="core/template"
							name="getresponse_webform_' . $webformSettings['layoutPosition'] . '"
							template="getresponse/webform.phtml">';
            $myXml .= '<action method="setData">
							<name>getresponse_active_subscription</name>
							<value>' . $webformSettings['activeSubscription'] . '</value></action>';
            $myXml .= '<action method="setData">
							<name>getresponse_webform_title</name>
							<value>' . $webformSettings['webformTitle'] . '</value></action>';
            $myXml .= '<action method="setData">
							<name>getresponse_webform_url</name>
							<value>' . str_replace('&', '&amp;', $webformSettings['url']) . '</value></action>';
            $myXml .= '</block></block>';
            $myXml .= '</reference>';

            /** @var Mage_Core_Model_Layout $layout */
            $layout = $observer->getEvent()->getData('layout');

            $layout->getUpdate()->addUpdate($myXml);
            $layout->generateXml();
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function addCssToHeader(Varien_Event_Observer $observer)
    {
        if (!$this->getresponseHelper->isEnabled()) {
            return;
        }

        /* @var $block Mage_Page_Block_Html_Head */
        $block = $observer->getEvent()->getBlock();

        if ("head" == $block->getNameInLayout()) {
            $block->addCss('css/getresponse.css');
            $block->addCss('css/getresponse-custom-field.css');
            $block->addCss('css/jquery-ui.min.css');
            $block->addCss('css/jquery.switchButton.css');
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function createAccount(Varien_Event_Observer $observer)
    {
        if (!$this->getresponseHelper->isEnabled()) {
            return;
        }

        /** @var Varien_Event $event */
        $event = $observer->getEvent();
        $customer = $event->getData('customer');
        $settingsRepository = new SettingsRepository($this->shopId);
        $accountSettings = $settingsRepository->getAccount();

        if (empty($accountSettings['apiKey']) || (int) $accountSettings['activeSubscription'] !== 1 || empty($accountSettings['campaignId'])) {
            return;
        }


        $subscriber = $this->newsletterModel->setStoreId($this->shopId)->loadByEmail($customer->getData('email'));

        if (false === $subscriber->isSubscribed()) {
            return;
        }

        try {
            $api = $this->buildApiInstance();
            $api->upsertContact(
                $accountSettings['campaignId'],
                $customer->getName(),
                $customer->getData('email'),
                $accountSettings['cycleDay'],
                array()
            );
        } catch (GetresponseException $e) {
            GetresponseIntegration_Getresponse_Helper_Logger::logException($e);
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function createAccountOrder(Varien_Event_Observer $observer)
    {
        if (!$this->getresponseHelper->isEnabled()) {
            return;
        }

        /** @var Varien_Event $event */
        $event = $observer->getEvent();

        /** @var Mage_Sales_Model_Order $order */
        $order = $event->getData('order');

        if ($order->isEmpty()) {
            return;
        }

        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());

        $settingsRepository = new SettingsRepository($this->shopId);
        $accountSettings = $settingsRepository->getAccount();


        if (empty($accountSettings['apiKey']) || $accountSettings['activeSubscription'] != '1' || empty($accountSettings['campaignId'])) {
            return;
        }

        $subscriberModel = $this->newsletterModel->loadByEmail($customer->getData('email'));
        if (false === $subscriberModel->isSubscribed()) {
            return;
        }

        $customs = $this->customsModel->getCustoms($this->shopId);

        $billing = $customer->getPrimaryBillingAddress();

        $user_customs = array();

        if (is_object($billing)) {
            $user_customs = $billing->getData();
            $user_customs['country'] = $user_customs['country_id'];
        }

        try {
            $api = $this->buildApiInstance();
            $api->upsertContact(
                $accountSettings['campaignId'],
                $customer->getName(),
                $customer->getData('email'),
                $accountSettings['cycleDay'],
                $this->customsModel->mapCustoms($user_customs, $customs)
            );
        } catch (GetresponseException $e) {
        }
    }

    public function initBeforeEventAction()
    {
        if (!$this->getresponseHelper->isEnabled()) {
            return;
        }

        $settingsRepository = new SettingsRepository($this->shopId);
        $accountSettings = $settingsRepository->getAccount();

        if (empty($accountSettings['apiKey'])) {
            return;
        }

        // display Signup to Newsletter checkbox on checkout page
        try {
            Mage::register(
                '_subscription_on_checkout',
                (bool)$accountSettings['subscriptionOnCheckout']
            );
        } catch (Mage_Core_Exception $e) {
        }
    }

    public function checkoutSaveAddress()
    {
        if (!$this->getresponseHelper->isEnabled()) {
            return;
        }

        $post = Mage::app()->getRequest()->getPost();

        if (empty($post) || empty($post['billing']) || (!isset($post['is_subscribed']))) {
            return;
        }

        $session = Mage::getSingleton('core/session');

        if (1 === (int) $post['is_subscribed']) {
            $session->setData('_gr_is_subscribed', true);
            $session->setData('_subscriber_data', $post['billing']);
        } else {
            $session->setData('_gr_is_subscribed', false);
            $session->setData('_subscriber_data', null);
        }
    }

    public function checkoutAllAfterFormSubmitted()
    {
        $session = Mage::getSingleton('core/session');
        $isSubscribed = (bool) $session->getData('_gr_is_subscribed');

        if (!$this->getresponseHelper->isEnabled() || 0 === $isSubscribed) {
            return;
        }

        $details = (array) $session->getData('_subscriber_data');

        // clear session
        $session->setData('_gr_is_subscribed', null);
        $session->setData('_subscriber_data', null);

        if (empty($details['email'])) {
            return;
        }

        try {
            $this->newsletterModel->subscribe($details['email']);
        } catch (Exception $e) {
            return;
        }

        $settingsRepository = new SettingsRepository($this->shopId);
        $accountSettings = $settingsRepository->getAccount();

        if (empty($accountSettings['apiKey'])) {
            return;
        }

        $customs = (array) $this->customsModel->getCustoms($this->shopId);

        $details['street'] = join(' ', (array)$details['street']);
        $details['country'] = $details['country_id'];

        try {
            $api = $this->buildApiInstance();
            $api->upsertContact(
                $accountSettings['campaignId'],
                $details['firstname'] . ' ' . $details['lastname'],
                $details['email'],
                $accountSettings['cycleDay'],
                $this->customsModel->mapCustoms($details, $customs)
            );
        } catch (GetresponseException $e) {
            return;
        }
    }

    public function initBeforeAddToNewsletterAction()
    {
        if (!$this->getresponseHelper->isEnabled()) {
            return;
        }

        $settingsRepository = new SettingsRepository($this->shopId);
        $accountSettings = $settingsRepository->getAccount();

        if (empty($accountSettings['apiKey']) || 1 !== (int) $accountSettings['newsletterSubscription'] || empty($accountSettings['newsletterCampaignId'])) {
            return;
        }

        $name = $email = null;
        $post = Mage::app()->getRequest()->getPost();

        $customer = Mage::getSingleton('customer/session')->getCustomer();

        // only, if customer is logged in.
        if (!$customer->isEmpty() && strlen($customer->email) > 0 && isset($post['is_subscribed']) && $post['is_subscribed'] === 1) {
            $name = $customer->firstname . ' ' . $customer->lastname;
            $email = $customer->email;
        } else if (isset($post['email']) && !empty($post['email'])) {
            $name = 'Friend';
            $email = $post['email'];
        }

        if (empty($email)) {
            return;
        }

        $subscriberModel = $this->newsletterModel->loadByEmail($email);

        if (false === $subscriberModel->isSubscribed()) {
            return;
        }

        try {
            $api = $this->buildApiInstance();
            $api->upsertContact(
                $accountSettings['newsletterCampaignId'],
                $name,
                $email,
                $accountSettings['newsletterCycleDay'],
                array()
            );
        } catch (GetresponseException $e) {
            return;
        }
    }

    /**
     * @return GetresponseIntegration_Getresponse_Helper_Api
     * @throws GetresponseException
     */
    private function buildApiInstance()
    {
        $settingsRepository = new SettingsRepository($this->shopId);
        $accountSettings = $settingsRepository->getAccount();

        if (empty($accountSettings['apiKey'])) {
            throw GetresponseException::create_when_api_key_not_found();
        }

        /** @var GetresponseIntegration_Getresponse_Helper_Api $api */
        $api = Mage::helper('getresponse/api');

        $api->setApiDetails(
            $accountSettings['apiKey'],
            $accountSettings['apiUrl'],
            $accountSettings['apiDomain']
        );

        return $api;
    }
}
