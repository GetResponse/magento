<?php

use GetresponseIntegration_Getresponse_Domain_SettingsRepository as SettingsRepository;
use GetresponseIntegration_Getresponse_Domain_ShopRepository as ShopRepository;
use GetresponseIntegration_Getresponse_Domain_GetresponseException as GetresponseException;
use GetresponseIntegration_Getresponse_Domain_GetresponseOrderHandler as GetresponseOrderHandler;
use GetresponseIntegration_Getresponse_Domain_GetresponseCartHandler as GetresponseCartHandler;
use GetresponseIntegration_Getresponse_Domain_Scheduler as Scheduler;

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
        Mage::log(
            'EcommerceObserver::updateCartHandler', 1, 'getresponse.log'
        );

        try {
            if (false === $this->canHandleECommerceEvent()) {
                return;
            }

            $campaignId = isset($this->accountSettings['campaignId'])
                ? $this->accountSettings['campaignId'] : '';


            /** @var Mage_Checkout_Helper_Cart $cartModel */
            $cartModel = Mage::helper('checkout/cart');

            $customer = $this->customerSessionModel->getCustomer();

            if ($this->shopsSettings['isScheduleOptimizationEnabled']) {
                $scheduler = new Scheduler();
                $scheduler->addToQueue(
                    $customer->getId(),
                    Scheduler::UPSERT_CART,
                    array(
                        'quote_id'         => $cartModel->getCart()->getQuote()
                            ->getId(),
                        'campaign_id'      => $campaignId,
                        'subscriber_email' => $customer->getData('email'),
                        'store_id'         => $this->shopsSettings['grShopId']

                    )
                );
            } else {
                $cartHandler = new GetresponseCartHandler(
                    $this->buildApiInstance()
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
        Mage::log(
            'EcommerceObserver::createOrderHandler', 7, 'getresponse.log'
        );

        try {
            if (false === $this->canHandleECommerceEvent()) {
                return;
            }

            $campaignId = isset($this->accountSettings['campaignId'])
                ? $this->accountSettings['campaignId'] : '';

            /** @var Mage_Customer_Model_Customer $customer */
            $customer = $this->customerSessionModel->getCustomer();

            $orderHandler = new GetresponseOrderHandler(
                $this->buildApiInstance()
            );

            /** @var Mage_Sales_Model_Order $order */
            $order = $observer->getEvent()->getData('order');

            $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
            $getresponseCartId = $quote->getData('getresponse_cart_id');

            $orderHandler->sendOrderToGetresponse(
                $observer->getEvent()->getData('order'),
                $customer->getData('email'),
                $campaignId,
                $getresponseCartId,
                $this->shopsSettings['grShopId']
            );
        } catch (Exception $e) {
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     *
     * @return GetresponseIntegration_Getresponse_Model_ECommerceObserver
     */
    public function orderDetailsChangedHandler(Varien_Event_Observer $observer)
    {
        Mage::log(
            'EcommerceObserver::orderDetailsChangedHandler', 7,
            'getresponse.log'
        );

        try {
            if (false === $this->canHandleECommerceEvent()) {
                return $this;
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
                return $this;
            }

            /** @var Mage_Customer_Model_Customer $customer */
            $customer = $this->customerSessionModel->getCustomer();

            $orderHandler = new GetresponseOrderHandler(
                $this->buildApiInstance()
            );

            /** @var Mage_Sales_Model_Order $order */
            $order = $observer->getEvent()->getData('order');

            $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
            $getresponseCartId = $quote->getData('getresponse_cart_id');

            $orderHandler->sendOrderToGetresponse(
                $observer->getEvent()->getData('order'),
                $customer->getData('email'),
                $campaignId,
                $getresponseCartId,
                $this->shopsSettings['grShopId']
            );

            return $this;

        } catch (Exception $e) {
            Mage::log('Error: ' . $e->getMessage(), 1, 'getresponse.log');

        }

        return $this;
    }

    public function export_jobs_to_getresponse()
    {
        try {
            $api = $this->buildApiInstance();

            $scheduler = new Scheduler();
            $customerHandler
                = new GetresponseIntegration_Getresponse_Domain_GetresponseCustomerHandler(
                $api
            );
            $cartHandler
                = new GetresponseIntegration_Getresponse_Domain_GetresponseCartHandler(
                $api
            );
            $orderHandler
                = new GetresponseIntegration_Getresponse_Domain_GetresponseOrderHandler(
                $api
            );
            /** @var array $jobs */
            $jobs = $scheduler->getAllJobs();

            print PHP_EOL . 'ilosc zadan: ' . count($jobs);

            /** @var GetresponseIntegration_Getresponse_Model_ScheduleJobsQueue $job */
            foreach ($jobs as $job) {

                print PHP_EOL . 'send job: ' . $job->getData('type');

                switch ($job->getData('type')) {
                    case Scheduler::UPSERT_CUSTOMER:

                        $payload = json_decode($job->getData('payload'), true);

                        $customerHandler->sendCustomerToGetResponse(
                            $payload['campaign_id'],
                            $payload['cycle_day'],
                            $payload['gr_custom_fields'],
                            $payload['custom_fields'],
                            $payload['subscriber_email']
                        );

                        break;

                    case Scheduler::UPSERT_CART:

                        $payload = json_decode($job->getData('payload'), true);
                        /** @var Mage_Sales_Model_Quote $quote */
                        $quote = Mage::getModel('sales/quote')->load(
                            $payload['quote_id']
                        );

                        $cartHandler->sendCartToGetresponse(
                            $quote,
                            $payload['campaign_id'],
                            $payload['subscriber_email'],
                            $payload['store_id']
                        );

                        break;

                    case Scheduler::UPSERT_ORDER:

                        $payload = json_decode($job->getData('payload'), true);

                        /** @var Mage_Sales_Model_Order $order */
                        $order = Mage::getResourceModel(
                            'sales/order_collection'
                        )
                            ->addFieldToSelect('*')
                            ->addFieldToFilter(
                                'entity_id', $payload['order_id']
                            )
                            ->getFirstItem();

                        if ($order->isEmpty()) {
                            $job->delete();
                            break;
                        }

                        $quote = Mage::getModel('sales/quote')->load(
                            $order->getQuoteId()
                        );

                        $orderHandler->sendOrderToGetresponse(
                            $order,
                            $payload['subscriber_email'],
                            $payload['campaign_id'],
                            $quote->getData('getresponse_cart_id'),
                            $payload['store_id']
                        );

                        break;
                }
                $job->delete();
            }

        } catch (GetresponseException $e) {
            Mage::log($e->getMessage(), 1, 'getresponse.log');
        } catch (Exception $e) {
            Mage::log(
                'Cannot remove job - ' . $e->getMessage(), 1, 'getresponse.log'
            );
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
        } catch (GetresponseIntegration_Getresponse_Domain_GetresponseException $e) {
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
