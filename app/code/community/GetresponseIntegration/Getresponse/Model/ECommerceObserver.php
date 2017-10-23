<?php

/**
 * Class GetresponseIntegration_Getresponse_Model_ECommerceObserver
 */
class GetresponseIntegration_Getresponse_Model_ECommerceObserver
{
    const CACHE_KEY = 'getresponse_cache';

    /** @var GetresponseIntegration_Getresponse_Helper_Api */
    protected $api;
    /** @var GetresponseIntegration_Getresponse_Model_Settings */
    protected $getresponseSettings;
    /** @var Mage_Core_Model_Abstract  */
    protected $getresponseShopsSettings;
    /** @var Zend_Cache_Core  */
    protected $cache;

    public function __construct()
    {
        $this->getresponseSettings = Mage::getModel('getresponse/settings')->load(
            Mage::helper('getresponse')->getStoreId()
        );

        $this->getresponseShopsSettings = Mage::getModel('getresponse/shop')->load(
            Mage::helper('getresponse')->getStoreId()
        );

        $this->api = Mage::helper('getresponse/api');

        $this->api->setApiDetails(
            $this->getresponseSettings['api_key'],
            $this->getresponseSettings['api_url'],
            $this->getresponseSettings['api_domain']
        );

        $this->cache = Mage::app()->getCache();
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function addProductToCartHandler(Varien_Event_Observer $observer)
    {
        if (false === $this->canHandleECommerceEvent()) {
            return;
        }

        /** @var Mage_Sales_Model_Quote $magentoCart */
        $magentoCart = Mage::helper('checkout/cart')->getCart()->getQuote();

        $requestToGr = [
            'contactId' => $this->getContactFromGetResponse()->contactId,
            'currency' => $magentoCart->getStoreCurrencyCode(),
            'totalPrice' => $magentoCart->getSubtotal(),
            'totalTaxPrice' => $magentoCart->getGrandTotal(),
            'selectedVariants' => []
        ];

        /** @var Mage_Sales_Model_Quote_Item $magentoCartItem */
        foreach ($magentoCart->getAllVisibleItems() as $magentoCartItem) {

            if (false === $this->_isProductTypeSupported($magentoCartItem->getProductType())) {
                continue;
            }

            $grProductId = $this->getProductId($this->getresponseShopsSettings->getGrShopId(), $magentoCartItem);
            if (false === $grProductId) {
                continue;
            }

            $requestToGr['selectedVariants'][] = [
                'variantId' => $grProductId,
                'price' => $magentoCartItem->getProduct()->getPrice(),
                'priceTax' => $magentoCartItem->getPriceInclTax(),
                'quantity' => $magentoCartItem->getQty(),
            ];
        }

        if (empty($requestToGr['selectedVariants'])) {
            if (!empty($magentoCart['getresponse_cart_id'])) {
                $this->api->deleteCart(
                    $this->getresponseShopsSettings['gr_shop_id'],
                    $magentoCart['getresponse_cart_id']
                );
                $magentoCart->setGetresponseCartId('');
                $magentoCart->save();
            }
            return;
        }


        if (empty($magentoCart['getresponse_cart_id'])) {
            $response = $this->api->addCart($this->getresponseShopsSettings['gr_shop_id'], $requestToGr);
            $magentoCart->setGetresponseCartId($response->cartId);
            $magentoCart->save();
        } else {
            $response = $this->api->updateCart(
                $this->getresponseShopsSettings->getGrShopId(),
                $magentoCart['getresponse_cart_id'],
                $requestToGr
            );
        }

    }

    /**
     * @param string $shopId
     * @param Mage_Sales_Model_Quote_Item $magentoCartItem
     * @return bool|string
     */
    protected function getProductId($shopId, $magentoCartItem)
    {
        $productMapCollection = Mage::getModel('getresponse/ProductMap')->getCollection();

        $productMap = $productMapCollection
            ->addFieldToFilter('entity_id', $magentoCartItem->getProductId())
            ->addFieldToFilter('gr_shop_id', $shopId)
            ->getFirstItem();

        if (!is_null($productMap->getGrProductId())) {
            return $productMap->getGrProductId();
        }

        $productId = $this->createProductInGetResponse($shopId, $magentoCartItem);

        if (false === $productId) {
            return false;
        }

        $productMap = Mage::getModel('getresponse/ProductMap');
        $productMap->setData([
            'gr_shop_id' => $shopId,
            'entity_id' => $magentoCartItem->getProductId(),
            'gr_product_id' => $productId
        ]);
        $productMap->save();

        return $productId;
    }

    /**
     * @param string $shopId
     * @param Mage_Sales_Model_Quote_Item $magentoCartItem
     * @return bool
     */
    protected function createProductInGetResponse($shopId, $magentoCartItem)
    {
        $params = [
            'name' => $magentoCartItem->getProduct()->getName(),
            'categories' => [],
            'externalId' => $magentoCartItem->getProductId(),
            'variants' => [
                [
                    'name' => $magentoCartItem->getName(),
                    'price'=> $magentoCartItem->getProduct()->getPrice(),
                    'priceTax' => $magentoCartItem->getProduct()->getPrice(),
                    'sku' => $magentoCartItem->getProduct()->getSku(),
                ],
            ],
        ];

        $response = $this->api->addProduct($shopId, $params);

        return $this->handleProductResponse($response);
    }

    protected function handleProductResponse($response)
    {
        if (!isset($response->productId)) {
            return false;
        } else {
            return $response->variants[0]->variantId;
        }
    }

    /**
     * @return mixed
     */
    protected function getContactFromGetResponse()
    {
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getSingleton('customer/session')->getCustomer();

        $cacheKey = md5($customer->getEmail().$this->getresponseSettings['campaign_id']);
        $cachedContact = $this->cache->load($cacheKey);

        if (false !== $cachedContact) {
            return unserialize($cachedContact);
        }

        $response = $this->api->getContact(
            $customer->getEmail(),
            $this->getresponseSettings['campaign_id']
        );

        $this->cache->save(serialize($response), $cacheKey, [self::CACHE_KEY], 5*60);

        return $response;
    }

    protected function canHandleECommerceEvent()
    {
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            return false;
        }

        $current_shop_id = Mage::helper('getresponse')->getStoreId();
        $data = Mage::getModel('getresponse/shop')->load($current_shop_id);

        if (1 != $data->is_enabled) {
            return false;
        }

        $contact = $this->getContactFromGetResponse();

        if (!isset($contact->contactId)) {
            return false;
        }

    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function createOrderHandler($observer)
    {
        if (false === $this->canHandleECommerceEvent()) {
            return;
        }

        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getEvent()->getOrder();
        $requestToGr = $this->createOrderPayload($order);
        $requestToGr['cartId'] = $order->getQuote()->getGetresponseCartId();

        $response = $this->api->createOrder(
            $this->getresponseShopsSettings->getGrShopId(),
            $requestToGr
        );

        $order->setGetresponseOrderId($response->orderId);
        $order->setGetresponseOrderMd5($this->createOrderPayloadHash($requestToGr));
        $order->save();
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    protected function createOrderPayload(Mage_Sales_Model_Order $order)
    {
        $requestToGr = [
            'contactId' => $this->getContactFromGetResponse()->contactId,
            'totalPrice' => $order->getGrandTotal(),
            'totalPriceTax' => $order->getTaxAmount(),
            'cartId' => $order->getQuote()->getGetresponseCartId(),
            'currency' => $order->getOrderCurrencyCode(),
            'status' => $order->getStatus(),
            'shippingPrice'  => $order->getShippingAmount(),
            'externalId' => $order->getId(),
            'shippingAddress' => [
                'countryCode' => $order->getShippingAddress()->getCountryModel()->getIso3Code(),
                'name' => $order->getShippingAddress()->getStreetFull(),
                'firstName' => $order->getShippingAddress()->getFirstname(),
                'lastName' => $order->getShippingAddress()->getLastname(),
                'city' => $order->getShippingAddress()->getCity(),
                'zip' => $order->getShippingAddress()->getPostcode(),
            ],
            'billingAddress' => [
                'countryCode' => $order->getBillingAddress()->getCountryModel()->getIso3Code(),
                'name' => $order->getBillingAddress()->getStreetFull(),
                'firstName' => $order->getBillingAddress()->getFirstname(),
                'lastName' => $order->getBillingAddress()->getLastname(),
                'city' => $order->getBillingAddress()->getCity(),
                'zip' => $order->getBillingAddress()->getPostcode(),
            ],
        ];

        /** @var Mage_Sales_Model_Order_Item $item */
        foreach ($order->getAllItems() as $item) {

            if (0 == $item->getQtyOrdered()) {
                continue;
            }

            $grProductId = $this->getProductId($this->getresponseShopsSettings->getGrShopId(), $item);
            if (false === $grProductId) {
                continue;
            }

            $requestToGr['selectedVariants'][] = [
                'variantId' => $grProductId,
                'price' => $item->getPrice(),
                'priceTax' => round($item->getTaxAmount() / $item->getQtyOrdered(), 2),
                'quantity' => $item->getQtyOrdered(),
                'type' => $item->getProductType(),
            ];

        }

        //echo "<pre>"; print_r($requestToGr); die;

        return $requestToGr;
    }

    /**
     * @param $productType
     * @return bool
     */
    protected function _isProductTypeSupported($productType)
    {
        return true;
        /*
        if ('simple' !== $productType) {
            return false;
        }
        return true;
        */
    }

    /**
     * @param array $orderPayload
     * @return string
     */
    protected function createOrderPayloadHash(array $orderPayload)
    {
        return md5(json_encode($orderPayload));
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function orderDetailsChangedHandler(Varien_Event_Observer $observer)
    {
        if (false === $this->canHandleECommerceEvent()) {
            return;
        }

        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getEvent()->getOrder();
        $requestToGr = $this->createOrderPayload($order);

        if ($order->getGetresponseOrderMd5() == $this->createOrderPayloadHash($requestToGr) || '' == $order->getGetresponseOrderId()) {
            Mage::log('[Order Details Changed Event] - Nothing important to GR', 1, 'getresponse.log');
            return;
        }

        $this->api->updateOrder(
            $this->getresponseShopsSettings->getGrShopId(),
            $order->getGetresponseOrderId(),
            $requestToGr
        );
        $order->setGetresponseOrderMd5($this->createOrderPayloadHash($requestToGr));
        $order->save();

        Mage::log('[Order Details Changed Event] - Important to GR. Request sent.', 1, 'getresponse.log');


    }


}