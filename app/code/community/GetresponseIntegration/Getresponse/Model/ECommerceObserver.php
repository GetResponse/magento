<?php

use GetresponseIntegration_Getresponse_Domain_SettingsRepository as SettingsRepository;
use GetresponseIntegration_Getresponse_Domain_ShopRepository as ShopRepository;
use GetresponseIntegration_Getresponse_Domain_GetresponseException as GetresponseException;
use GetresponseIntegration_Getresponse_Domain_GetresponseOrderHandler as GetresponseOrderHandler;
use GetresponseIntegration_Getresponse_Domain_GetresponseCartHandler as GetresponseCartHandler;
use GetresponseIntegration_Getresponse_Domain_Scheduler as Scheduler;
use GetresponseIntegration_Getresponse_Domain_GetresponseCartBuilder as GrCartBuilder;
use GetresponseIntegration_Getresponse_Domain_GetresponseProductHandler as GrProductHandler;
use GetresponseIntegration_Getresponse_Domain_GetresponseOrderBuilder as GrOrderBuilder;

/**
 * Class GetresponseIntegration_Getresponse_Model_ECommerceObserver
 */
class GetresponseIntegration_Getresponse_Model_ECommerceObserver
{
    const CACHE_KEY = 'getresponse_cache';

    /** @var Mage_Customer_Model_Session */
    private $customerSessionModel;

    /** @var SettingsRepository */
    private $getresponseSettings;

    /** @var Mage_Core_Model_Abstract */
    private $shopsSettings;

    /** @var Zend_Cache_Core */
    private $cache;

    /** @var array */
    private $accountSettings;

    /** @var GetresponseIntegration_Getresponse_Helper_Data */
    private $getresponseHelper;

    /** @var string */
    private $shopId;

    public function __construct()
    {
        $this->customerSessionModel = Mage::getSingleton('customer/session');
        $this->getresponseHelper = Mage::helper('getresponse');
        $this->shopId = $this->getresponseHelper->getStoreId();
        $this->getresponseSettings = new SettingsRepository($this->shopId);
        $this->accountSettings = $this->getresponseSettings->getAccount();
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

            $campaignId = '';

            if (isset($this->accountSettings['campaignId'])) {
                $campaignId = $this->accountSettings['campaignId'];
            }

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
                        'quote_id'         => $cartModel->getCart()->getQuote()
                            ->getId(),
                        'campaign_id'      => $campaignId,
                        'subscriber_email' => $customer->getData('email'),
                        'gr_store_id'         => $this->shopsSettings['grShopId'],
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

            $campaignId = isset($this->accountSettings['campaignId'])
                ? $this->accountSettings['campaignId'] : '';

            /** @var Mage_Customer_Model_Customer $customer */
            $customer = $this->customerSessionModel->getCustomer();

            $orderHandler = new GetresponseOrderHandler(
                $this->buildApiInstance(),
                new GrProductHandler($this->buildApiInstance()),
                new GrOrderBuilder()
            );

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
     * @param Varien_Event_Observer $observer
     */
    public function orderDetailsChangedHandler(Varien_Event_Observer $observer)
    {
        try {
            if (false === $this->canHandleECommerceEvent()) {
                return;
            }

            $campaignId = isset($this->accountSettings['campaignId'])
                ? $this->accountSettings['campaignId'] : '';

            /** @var Mage_Sales_Model_Order $order */
            $order = $observer->getEvent()->getData('order');
            $orderPayload = $this->createOrderPayload($order);

            $hash = $this->createOrderPayloadHash($orderPayload);

            if ($order->getData('getresponse_order_md5') == $hash
                || '' == $order->getData('getresponse_order_id')
            ) {
                return;
            }

            /** @var Mage_Customer_Model_Customer $customer */
            $customer = $this->customerSessionModel->getCustomer();

            $orderHandler = new GetresponseOrderHandler(
                $this->buildApiInstance(),
                new GrProductHandler($this->buildApiInstance()),
                new GrOrderBuilder()
            );

            /** @var Mage_Sales_Model_Order $order */
            $order = $observer->getEvent()->getData('order');

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
                        'shop_id' => $this->shopId,
                        'skip_automation' => 0
                    )
                );
            } else {

                $quote = Mage::getModel('sales/quote')->load(
                    $order->getQuoteId()
                );
                $getresponseCartId = $quote->getData('getresponse_cart_id');

                $orderHandler->sendOrderToGetresponse(
                    $observer->getEvent()->getData('order'),
                    $customer->getData('email'),
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
                'countryCode' => $shippingAddress->getCountryModel()
                    ->getIso3Code(),
                'name'        => $shippingAddress->getStreetFull(),
                'firstName'   => $shippingAddress->getFirstname(),
                'lastName'    => $shippingAddress->getLastname(),
                'city'        => $shippingAddress->getCity(),
                'zip'         => $shippingAddress->getPostcode(),
            ),
            'billingAddress'  => array(
                'countryCode' => $order->getBillingAddress()->getCountryModel()
                    ->getIso3Code(),
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
     */
    private function canHandleECommerceEvent()
    {
        if (!$this->customerSessionModel->isLoggedIn()) {
            return false;
        }

        if (1 != $this->shopsSettings['isEnabled']) {
            return false;
        }

        $contact = $this->getContactFromGetResponse(
            $this->customerSessionModel->getCustomer()
        );

        if (!isset($contact['contactId'])) {
            return false;
        }

        return true;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     *
     * @return array
     */
    private function getContactFromGetResponse(Mage_Customer_Model_Customer $customer
    ) {
        try {
            $cacheKey = md5(
                $customer->getData('email')
                . $this->accountSettings['campaignId']
            );
            $cachedContact = $this->cache->load($cacheKey);

            if (false !== $cachedContact) {
                return (array)unserialize($cachedContact);
            }

            $api = $this->buildApiInstance();

            $response = $api->getContact(
                $customer->getData('email'),
                $this->accountSettings['campaignId']
            );

            $this->cache->save(
                serialize($response), $cacheKey, array(self::CACHE_KEY), 5 * 60
            );

            return (array)$response;
        } catch (GetresponseException $e) {
            return array();
        } catch (Zend_Cache_Exception $e) {
            return array();
        }
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
}
