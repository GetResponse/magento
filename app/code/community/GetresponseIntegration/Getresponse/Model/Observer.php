<?php

use GetresponseIntegration_Getresponse_Domain_GetresponseException as GetresponseException;
use GetresponseIntegration_Getresponse_Domain_SettingsRepository as SettingsRepository;
use GetresponseIntegration_Getresponse_Domain_WebformRepository as WebformRepository;
use GetresponseIntegration_Getresponse_Domain_SettingsFactory as SettingsFactory;

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
    public function createContact(Varien_Event_Observer $observer)
    {
        if (!$this->getresponseHelper->isEnabled()) {
            return;
        }

        $accountSettings = SettingsFactory::createFromArray((new SettingsRepository($this->shopId))->getAccount());
        if (!$accountSettings->hasApiKey()) {
            return;
        }

        /** @var Varien_Event $event */
        $event = $observer->getEvent();
        /** @var Mage_Newsletter_Model_Subscriber $subscriber */
        $subscriber = $event->getData('subscriber');

        if (null === $subscriber) {
            return;
        }

        if (!$subscriber->getIsStatusChanged() || !$subscriber->isSubscribed()) {
            return;
        }

        if (!Mage::app()->getStore()->isAdmin() && $this->shopId !== $subscriber->getStoreId()) {
            return;
        }

        if ($subscriber->getCustomerId()) {

            if (!$accountSettings->isTurnOnAddContactAfterCustomerRegister()) {
                return;
            }

            /** @var Mage_Customer_Model_Customer $customer */
            $customer = Mage::getModel('customer/customer')->load($subscriber->getCustomerId());
            $customs = $this->customsModel->getCustoms($subscriber->getStoreId());
            $billing = $customer->getPrimaryBillingAddress();

            $user_customs = array();
            if (is_object($billing)) {
                $user_customs = $billing->getData();
                $user_customs['country'] = $user_customs['country_id'];
            }

            $contactCustomFields = $this->customsModel->mapCustoms($user_customs, $customs);
            $contactName = $customer->getName();
            $contactEmail = $customer->getData('email');
            $contactListId = $accountSettings->getCampaignId();
            $autoresponderDay = $accountSettings->getCycleDay();

        } else {

            $session = Mage::getSingleton('core/session');
            $isSubscribedByCheckout = (bool) $session->getData('_gr_is_subscribed');
            $subscriberCheckoutDetails = (array) $session->getData('_subscriber_data');

            if (true === $isSubscribedByCheckout && $accountSettings->isTurnOnAddContactAfterCustomerRegister()) {
                // clear session
                $session->setData('_gr_is_subscribed', null);
                $session->setData('_subscriber_data', null);

                $customs = (array) $this->customsModel->getCustoms($subscriber->getStoreId());

                $subscriberCheckoutDetails['street'] = join(' ', (array)$subscriberCheckoutDetails['street']);
                $subscriberCheckoutDetails['country'] = $subscriberCheckoutDetails['country_id'];

                $contactCustomFields = $this->customsModel->mapCustoms($subscriberCheckoutDetails, $customs);
                $contactName = $subscriberCheckoutDetails['firstname'] . ' ' . $subscriberCheckoutDetails['lastname'];
                $contactEmail = $subscriberCheckoutDetails['email'];
                $contactListId = $accountSettings->getCampaignId();
                $autoresponderDay = $accountSettings->getCycleDay();

            } elseif ($accountSettings->isTurnOnAddContactAfterNewsletterSubscription()) {

                $contactName = 'Friend';
                $contactEmail = $subscriber->getEmail();
                $contactCustomFields = [];
                $contactListId = $accountSettings->getNewsletterCampaignId();
                $autoresponderDay = $accountSettings->getNewsletterCycleDay();

            } else {
                return;
            }
        }

        try {
            $api = $this->buildApiInstance();
            $api->upsertContact(
                $contactListId,
                $contactName,
                $contactEmail,
                $autoresponderDay,
                $contactCustomFields
            );
        } catch (GetresponseException $e) {
            GetresponseIntegration_Getresponse_Helper_Logger::logException($e);
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

    public function checkoutAllAfterFormSubmitted(Varien_Event_Observer $observer)
    {
        $session = Mage::getSingleton('core/session');
        $isSubscribedAtCheckout = (bool) $session->getData('_gr_is_subscribed');

        if (!$this->getresponseHelper->isEnabled() || 0 === $isSubscribedAtCheckout) {
            return;
        }

        $details = (array) $session->getData('_subscriber_data');

        if (empty($details['email'])) {
            return;
        }

        try {
            $this->newsletterModel->subscribe($details['email']);
        } catch (Exception $e) {
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
