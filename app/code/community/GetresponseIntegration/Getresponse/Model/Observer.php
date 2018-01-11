<?php

use GetresponseIntegration_Getresponse_Domain_SettingsRepository as SettingsRepository;
use GetresponseIntegration_Getresponse_Domain_WebformRepository as WebformRepository;
use GetresponseIntegration_Getresponse_Domain_AutomationRuleFactory as AutomationRuleFactory;
use GetresponseIntegration_Getresponse_Domain_AutomationRulesCollectionRepository as AutomationRulesCollectionRepository;
use GetresponseIntegration_Getresponse_Domain_AutomationRulesCollectionFactory as AutomationRulesCollectionFactory;

/**
 * Getresponse module observer
 *
 * @author Magento
 */
class GetresponseIntegration_Getresponse_Model_Observer
{
    /**
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function addTrackingCodeToHeader(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('getresponse')->isEnabled()) {
            return $this;
        }

        $shopId = Mage::helper('getresponse')->getStoreId();
        $settingsRepository = new SettingsRepository($shopId);
        $accountSettings = $settingsRepository->getAccount();

        if (empty($accountSettings['apiKey']) || 0 === (int)$accountSettings['hasActiveTrafficModule']) {
            return $this;
        }

        $layout = Mage::app()->getLayout();
        /* @var $block Mage_Page_Block_Html_Head */
        $block = $observer->getEvent()->getBlock();

        if ("head" == $block->getNameInLayout()) {
            $myBlock = $layout->createBlock('core/text');
            $myBlock->setText($accountSettings['trackingCodeSnippet']);

            $block->append($myBlock);
        }

        if ("footer" == $block->getNameInLayout()) {

            if (Mage::getSingleton('customer/session')->isLoggedIn()) {

                $customer = Mage::getSingleton('customer/session')->getCustomer();

                if (strlen($customer->email) > 0) {

                    $myBlock = $layout->createBlock('core/text');

                    $myBlock->setText('<script type="text/javascript">gaSetUserId("' . $customer->email . '");</script>');

                    $block->append($myBlock);
                }
            }
        }

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function addJQueryToHeader(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('getresponse')->isEnabled()) {
            return;
        }

        /* @var $block Mage_Page_Block_Html_Head */
        $block = $observer->getEvent()->getBlock();

        if ("head" == $block->getNameInLayout()) {
            foreach (Mage::helper('getresponse')->getFiles() as $file) {
                $block->addJs(Mage::helper('getresponse')->getJQueryPath($file));
            }
        }

        return;
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function set_block(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('getresponse')->isEnabled()) {
            return;
        }

        $shopId = Mage::helper('getresponse')->getStoreId();
        $settingsRepository = new SettingsRepository($shopId);
        $accountSettings = $settingsRepository->getAccount();
        $webformRepository = new WebformRepository($shopId);
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
							name="getresponse_webform_' . $webforms['layoutPosition'] . '"
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
            $layout = $observer->getEvent()->getLayout();

            $layout->getUpdate()->addUpdate($myXml);
            $layout->generateXml();

        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function addCssToHeader(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('getresponse')->isEnabled()) {
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
        if (!Mage::helper('getresponse')->isEnabled()) {
            return;
        }

        $event = $observer->getEvent();
        $customer = $event->getCustomer();

        $shopId = Mage::helper('getresponse')->getStoreId();
        $settingsRepository = new SettingsRepository($shopId);
        $accountSettings = $settingsRepository->getAccount();

        if (empty($accountSettings['apiKey']) || $accountSettings['activeSubscription'] != '1' || empty($accountSettings['campaignId'])) {
            return;
        }

        $subscriberModel = Mage::getModel('newsletter/subscriber')->loadByEmail($customer->getEmail());
        if (false === $subscriberModel->isSubscribed()) {
            return;
        }

        Mage::helper('getresponse/api')->setApiDetails(
            $accountSettings['apiKey'],
            $accountSettings['apiUrl'],
            $accountSettings['apiDomain']
        );

        Mage::helper('getresponse/api')->addContact(
            $accountSettings['campaignId'],
            $customer->getName(),
            $customer->getEmail(),
            $accountSettings['cycleDay'],
            array()
        );
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function createAccountOrder(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('getresponse')->isEnabled()) {
            return;
        }

        $order = $observer->getEvent()->getOrder();

        $categories = Mage::helper('getresponse')->getCategoriesByOrder($order);
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());

        $shopId = Mage::helper('getresponse')->getStoreId();
        $settingsRepository = new SettingsRepository($shopId);
        $accountSettings = $settingsRepository->getAccount();


        if (empty($accountSettings['apiKey']) || $accountSettings['activeSubscription'] != '1' || empty($accountSettings['campaignId'])) {
            return;
        }

        $subscriberModel = Mage::getModel('newsletter/subscriber')->loadByEmail($customer->getEmail());
        if (false === $subscriberModel->isSubscribed()) {
            //return;
        }

        $customs = Mage::getModel('getresponse/customs')->getCustoms($shopId);

        $billing = $customer->getPrimaryBillingAddress();

        $user_customs = [];

        if (is_object($billing)) {
            $user_customs = $billing->getData();
            $user_customs['country'] = $user_customs['country_id'];
        }

        Mage::helper('getresponse/api')->setApiDetails(
            $accountSettings['apiKey'],
            $accountSettings['apiUrl'],
            $accountSettings['apiDomain']
        );

        Mage::helper('getresponse/api')->addContact(
            $accountSettings['campaignId'],
            $customer->getName(),
            $customer->getEmail(),
            $accountSettings['cycleDay'],
            Mage::getModel('getresponse/customs')->mapCustoms($user_customs, $customs)
        );

        $this->automationHandler($categories, $shopId, $customer, $user_customs, $customs, $accountSettings);
    }

    /**
     * @param $categories
     * @param $shop_id
     * @param $customer
     * @param $user_customs
     * @param $customs
     * @param $settings
     */
    public function automationHandler($categories, $shop_id, $customer, $user_customs, $customs, $settings)
    {
        $automations = [];
        $ruleRepository = new AutomationRulesCollectionRepository($shop_id);
        $ruleCollectionDb = $ruleRepository->getCollection();

        foreach ($ruleCollectionDb as $rule) {
            if (false !== array_search($rule['categoryId'], $categories)) {
                $automations[] = $rule;
            }
        }

        if (empty($automations)) {
            return;
        }

        $delete_contact = false;

        foreach ($automations as $automation) {

            Mage::helper('getresponse/api')->addContact(
                $automation['campaignId'],
                $customer->getName(),
                $customer->getEmail(),
                $automation['cycleDay'],
                Mage::getModel('getresponse/customs')->mapCustoms($user_customs, $customs)
            );

            if ($automation['action'] == 'move') {
                $delete_contact = true;
            }
        }

        if ($delete_contact === true) {
            $contact = Mage::helper('getresponse/api')->getContact($customer->getEmail(), $settings['campaign_id']);
            if (isset($contact->contactId)) {
                Mage::helper('getresponse/api')->deleteContact($contact->contactId);
            }
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function initBeforeEventAction(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('getresponse')->isEnabled()) {
            return;
        }

        $shopId = Mage::helper('getresponse')->getStoreId();
        $settingsRepository = new SettingsRepository($shopId);
        $accountSettings = $settingsRepository->getAccount();

        if (empty($accountSettings['apiKey'])) {
            return;
        }

        Mage::register('_subscription_on_checkout', (bool)$accountSettings['subscriptionOnCheckout']);
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function checkoutSaveAddress(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('getresponse')->isEnabled()) {
            return;
        }

        $post = Mage::app()->getRequest()->getPost();

        if (empty($post) || empty($post['billing']) || (isset($post['is_subscribed']) && $post['is_subscribed'] != 1)) {
            return;
        }

        /** @var Mage_Core_Model_Session $session */
        $session = Mage::getSingleton('core/session');

        $session->setData('_is_subscribed', true);
        $session->setData('_subscriber_data', $post['billing']);
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function checkoutAllAfterFormSubmitted(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('getresponse')->isEnabled()) {
            return;
        }

        /** @var Varien_Event $event */
        $event = $observer->getEvent();

        /** @var Mage_Sales_Model_Quote $Quote */
        $Quote = $event->getQuote();

        if (false === in_array($Quote->getCheckoutMethod(true), array('register', 'guest'))) {
            return;
        }

        /** @var Mage_Core_Model_Session $session */
        $session = Mage::getSingleton('core/session');

        $isSubscribed = (bool)$session->getData('_is_subscribed');

        if (0 === $isSubscribed) {
            return;
        }

        $details = (array)$session->getData('_subscriber_data');

        // clear session
        $session->setData('_is_subscribed', null);
        $session->setData('_subscriber_data', null);

        if (empty($details['email'])) {
            return;
        }

        Mage::getModel('newsletter/subscriber')->subscribe($details['email']);

        $shopId = Mage::helper('getresponse')->getStoreId();
        $settingsRepository = new SettingsRepository($shopId);
        $accountSettings = $settingsRepository->getAccount();

        if (empty($accountSettings['apiKey'])) {
            return;
        }

        Mage::helper('getresponse/api')->setApiDetails(
            $accountSettings['apiKey'],
            $accountSettings['apiUrl'],
            $accountSettings['apiDomain']
        );

        $customs = (array)Mage::getModel('getresponse/customs')->getCustoms($shopId);

        $details['street'] = join(' ', (array)$details['street']);
        $details['country'] = $details['country_id'];

        Mage::helper('getresponse/api')->addContact(
            $accountSettings['campaignId'],
            $details['firstname'] . ' ' . $details['lastname'],
            $details['email'],
            $accountSettings['cycleDay'],
            Mage::getModel('getresponse/customs')->mapCustoms($details, $customs)
        );
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function initBeforeAddToNewsletterAction(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('getresponse')->isEnabled()) {
            return;
        }

        $shopId = Mage::helper('getresponse')->getStoreId();
        $settingsRepository = new SettingsRepository($shopId);
        $accountSettings = $settingsRepository->getAccount();

        if (empty($accountSettings['apiKey']) || 1 !== $accountSettings['newsletterSubscription'] || empty($accountSettings['newsletterCampaignId'])) {
            return;
        }

        $name = $email = null;
        $post = Mage::app()->getRequest()->getPost();

        /** @var Mage_Customer_Model_Session $customerSession */
        $customerSession = Mage::getSingleton('customer/session');
        $customer = $customerSession->getCustomer();

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

        $subscriberModel = Mage::getModel('newsletter/subscriber')->loadByEmail($email);

        if (false === $subscriberModel->isSubscribed()) {
            return;
        }

        Mage::helper('getresponse/api')->setApiDetails(
            $accountSettings['apiKey'],
            $accountSettings['apiUrl'],
            $accountSettings['apiDomain']
        );

        Mage::helper('getresponse/api')->addContact(
            $accountSettings['newsletterCampaignId'],
            $name,
            $email,
            $accountSettings['newsletterCycleDay'],
            []
        );
    }
}
