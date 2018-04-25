<?php

use GetresponseIntegration_Getresponse_Domain_GetresponseCartBuilder as GrCartBuilder;
use GetresponseIntegration_Getresponse_Domain_GetresponseCartHandler as GetresponseCartHandler;
use GetresponseIntegration_Getresponse_Domain_GetresponseException as GetresponseException;
use GetresponseIntegration_Getresponse_Domain_GetresponseOrderBuilder as GrOrderBuilder;
use GetresponseIntegration_Getresponse_Domain_GetresponseOrderHandler as GetresponseOrderHandler;
use GetresponseIntegration_Getresponse_Domain_GetresponseProductHandler as GrProductHandler;
use GetresponseIntegration_Getresponse_Domain_Scheduler as Scheduler;
use GetresponseIntegration_Getresponse_Domain_SettingsRepository as SettingsRepository;
use GetresponseIntegration_Getresponse_Domain_ShopRepository as ShopRepository;

/**
 * Class GetresponseIntegration_Getresponse_Model_ECommerceObserver
 */
class GetresponseIntegration_Getresponse_Model_ECommerceObserver
{
    const CACHE_KEY = 'getresponse_cache';

    /** @var Mage_Customer_Model_Session */
    private $customerSessionModel;

    /** @var Mage_Core_Model_Abstract */
    private $shopsSettings;

    /** @var Zend_Cache_Core */
    private $cache;

    /** @var array */
    private $accountSettings;

    /** @var string */
    private $shopId;

    public function __construct()
    {
        $this->customerSessionModel = Mage::getSingleton('customer/session');
        $this->shopId = Mage::helper('getresponse')->getStoreId();
        $settingsRepository = new SettingsRepository($this->shopId);
        $this->accountSettings = $settingsRepository->getAccount();
        $shopRepository = new ShopRepository($this->shopId);
        $this->shopsSettings = $shopRepository->getShop()->toArray();
        $this->cache = Mage::app()->getCache();
    }

    /**
     * Ten event reaguje na zmiany w koszyku
     */
    public function updateCartHandler()
    {
        try {
            if (false === $this->canHandleECommerceEvent()) {
                return;
            }

            $campaignId = $this->accountSettings['campaignId'];

            /** @var Mage_Checkout_Helper_Cart $cartModel */
            $cartModel = Mage::helper('checkout/cart');

            /** @var Mage_Sales_Model_Quote $salesQuote */
            $salesQuote = Mage::getModel('sales/quote');

            $customer = $this->customerSessionModel->getCustomer();

            if ($this->shopsSettings['isScheduleOptimizationEnabled']) {

                $scheduler = new Scheduler();
                $scheduler->addToQueue(
                    $customer->getId(),
                    Scheduler::EXPORT_CART,
                    array(
                        'quote_id' => $cartModel->getCart()->getQuote()->getId(),
                        'campaign_id' => $campaignId,
                        'subscriber_email' => $customer->getData('email'),
                        'gr_store_id' => $this->shopsSettings['grShopId'],
                        'shop_id' => $this->shopId
                    )
                );

            } else {

                $cartHandler = new GetresponseCartHandler(
                    $this->buildApiInstance(),
                    $salesQuote,
                    new GrCartBuilder(),
                    new GrProductHandler($this->buildApiInstance())
                );
                $cartHandler->sendCartToGetresponse(
                    $cartModel->getCart()->getQuote(),
                    $campaignId,
                    $customer->getData('email'),
                    $this->shopsSettings['grShopId']
                );
            }

        } catch (Exception $e) {
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function createOrderHandler($observer)
    {
        try {
            if (false === $this->canHandleECommerceEvent()) {
                return;
            }

            $campaignId = $this->accountSettings['campaignId'];

            /** @var Mage_Customer_Model_Customer $customer */
            $customer = $this->customerSessionModel->getCustomer();

            /** @var Mage_Sales_Model_Order $order */
            $order = $observer->getEvent()->getData('order');

            $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
            $getresponseCartId = $quote->getData('getresponse_cart_id');

            if ($this->shopsSettings['isScheduleOptimizationEnabled']) {

                $scheduler = new Scheduler();
                $scheduler->addToQueue(
                    $customer->getId(),
                    Scheduler::EXPORT_ORDER,
                    array(
                        'order_id' => $order->getId(),
                        'campaign_id' => $campaignId,
                        'subscriber_email' => $customer->getData('email'),
                        'gr_store_id' => $this->shopsSettings['grShopId'],
                        'shop_id' => $this->shopId

                    )
                );

            } else {

                $orderHandler = new GetresponseOrderHandler(
                    $this->buildApiInstance(),
                    new GrProductHandler($this->buildApiInstance()),
                    new GrOrderBuilder()
                );

                $orderHandler->sendOrderToGetresponse(
                    $observer->getEvent()->getData('order'),
                    $customer->getData('email'),
                    $campaignId,
                    $getresponseCartId,
                    $this->shopsSettings['grShopId'],
                    true,
                    false
                );
            }
        } catch (Exception $e) {
        }
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    private function isOrderPayloadChanged(Mage_Sales_Model_Order $order)
    {
        $hash = $this->createOrderPayloadHash(
            $this->createOrderPayload($order)
        );

        return $order->getData('getresponse_order_md5') !== $hash;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    private function orderExistsInGetresponse(Mage_Sales_Model_Order $order)
    {
        $exists = $order->getData('getresponse_order_id');
        return !empty($exists);
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function orderDetailsChangedHandler(Varien_Event_Observer $observer)
    {
        try {
            /** @var Mage_Sales_Model_Order $order */
            $order = $observer->getEvent()->getOrder();

            if (!$this->canChangeOrderDetails($order)) {
                return;
            }

            if (!$this->isOrderPayloadChanged($order) || !$this->orderExistsInGetresponse($order)) {
                return;
            }

            $campaignId = $this->accountSettings['campaignId'];

            if ($this->shopsSettings['isScheduleOptimizationEnabled']) {

                $scheduler = new Scheduler();
                $scheduler->addToQueue(
                    $order->getCustomerId(),
                    Scheduler::EXPORT_ORDER,
                    array(
                        'order_id' => $order->getId(),
                        'campaign_id' => $campaignId,
                        'subscriber_email' => $order->getCustomerEmail(),
                        'gr_store_id' => $this->shopsSettings['grShopId'],
                        'shop_id' => $this->shopId,
                        'skip_automation' => 0
                    )
                );

            } else {

                $orderHandler = new GetresponseOrderHandler(
                    $this->buildApiInstance(),
                    new GrProductHandler($this->buildApiInstance()),
                    new GrOrderBuilder()
                );

                $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
                $getresponseCartId = $quote->getData('getresponse_cart_id');

                $orderHandler->sendOrderToGetresponse(
                    $order,
                    $order->getCustomerEmail(),
                    $campaignId,
                    $getresponseCartId,
                    $this->shopsSettings['grShopId'],
                    false,
                    false
                );
            }
        } catch (Exception $e) {
            GetresponseIntegration_Getresponse_Helper_Logger::logException($e);
        }
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return bool
     * @throws Mage_Core_Model_Store_Exception
     * @throws Varien_Exception
     */
    private function canChangeOrderDetails(Mage_Sales_Model_Order $order)
    {
        return $this->isUserClientOrAdminAuthenticated()
            && $this->isStoreEnabled()
            && $this->isCampaignIdSet()
            && $this->isClientInGetResponse($order->getCustomerId());
    }

    /**
     * @param int $customerId
     * @return bool
     * @throws Varien_Exception
     */
    private function isClientInGetResponse($customerId)
    {
        $customerEmail = $this->getCustomerEmailById($customerId);
        $contact = $this->getContactFromGetResponseByEmail($customerEmail);

        return !empty($contact);
    }

    /**
     * @param string $email
     * @return array
     */
    private function getContactFromGetResponseByEmail($email)
    {
        try {
            if (empty($email) || empty($this->accountSettings['campaignId'])) {
                return array();
            }

            $cacheKey = md5($email . $this->accountSettings['campaignId']);
            $cachedContact = $this->cache->load($cacheKey);

            if (false !== $cachedContact) {
                return unserialize($cachedContact);
            }

            $api = $this->buildApiInstance();
            $response = $api->getContact($email, $this->accountSettings['campaignId']);

            $this->cache->save(serialize($response), $cacheKey, array(self::CACHE_KEY), 5 * 60);

            return (array)$response;
        } catch (GetresponseException $e) {
            return array();
        } catch (Zend_Cache_Exception $e) {
            return array();
        }
    }

    /**
     * @param int $customerId
     * @return string
     * @throws Varien_Exception
     */
    private function getCustomerEmailById($customerId)
    {
        $customerData = Mage::getModel('customer/customer')->load($customerId);

        return $customerData->getEmail();
    }

    /**
     * @return bool
     */
    private function isStoreEnabled()
    {
        $shopRepository = new ShopRepository(Mage::helper('getresponse')->getStoreId());

        return $shopRepository->getShop()->isEnabled();
    }


    /**
     * @return bool
     * @throws Mage_Core_Model_Store_Exception
     * @throws Varien_Exception
     */
    private function isUserClientOrAdminAuthenticated()
    {
        return Mage::app()->getStore()->isAdmin()
            ? Mage::getSingleton('admin/session')->isLoggedIn()
            : Mage::getSingleton('customer/session')->isLoggedIn();
    }

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return array
     */
    private function createOrderPayload(Mage_Sales_Model_Order $order)
    {
        $shippingAddress = $order->getShippingAddress();

        if (empty($shippingAddress)) {
            $shippingAddress = $order->getBillingAddress();
        }

        return array(
            'customerId'      => $order->getCustomerId(),
            'totalPrice'      => $order->getGrandTotal(),
            'totalPriceTax'   => $order->getTaxAmount(),
            'currency'        => $order->getOrderCurrencyCode(),
            'status'          => $order->getStatus(),
            'shippingPrice'   => $order->getShippingAmount(),
            'externalId'      => $order->getId(),
            'shippingAddress' => array(
                'countryCode' => $shippingAddress->getCountryModel()->getIso3Code(),
                'name'        => $shippingAddress->getStreetFull(),
                'firstName'   => $shippingAddress->getFirstname(),
                'lastName'    => $shippingAddress->getLastname(),
                'city'        => $shippingAddress->getCity(),
                'zip'         => $shippingAddress->getPostcode(),
            ),
            'billingAddress' => array(
                'countryCode' => $order->getBillingAddress()->getCountryModel()->getIso3Code(),
                'name'        => $order->getBillingAddress()->getStreetFull(),
                'firstName'   => $order->getBillingAddress()->getFirstname(),
                'lastName'    => $order->getBillingAddress()->getLastname(),
                'city'        => $order->getBillingAddress()->getCity(),
                'zip'         => $order->getBillingAddress()->getPostcode(),
            ),
        );
    }

    /**
     * @param array $orderPayload
     *
     * @return string
     */
    private function createOrderPayloadHash(array $orderPayload)
    {
        return md5(json_encode($orderPayload));
    }

    /**
     * @return bool
     * @throws Varien_Exception
     */
    private function canHandleECommerceEvent()
    {
        return $this->customerSessionModel->isLoggedIn()
            && $this->isStoreEnabled()
            && $this->isCampaignIdSet()
            && $this->isClientInGetResponse($this->customerSessionModel->getCustomer()->getId());
    }

    /**
     * @return GetresponseIntegration_Getresponse_Helper_Api
     * @throws GetresponseException
     */
    private function buildApiInstance()
    {
        if (empty($this->accountSettings['apiKey'])) {
            throw GetresponseException::create_when_api_key_not_found();
        }

        /** @var GetresponseIntegration_Getresponse_Helper_Api $api */
        $api = Mage::helper('getresponse/api');

        $api->setApiDetails(
            $this->accountSettings['apiKey'],
            $this->accountSettings['apiUrl'],
            $this->accountSettings['apiDomain']
        );

        return $api;
    }

    /**
     * @return bool
     */
    private function isCampaignIdSet()
    {
        return isset($this->accountSettings['campaignId']);
    }
}
