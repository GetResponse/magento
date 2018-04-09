<?php

use GetresponseIntegration_Getresponse_Domain_GetresponseCartBuilder as GrCartBuilder;
use GetresponseIntegration_Getresponse_Domain_GetresponseProductHandler as GrProductHandler;

/**
 * Class GetresponseIntegration_Getresponse_Domain_GetresponseCartHandler
 */
class GetresponseIntegration_Getresponse_Domain_GetresponseCartHandler
{
    /** @var GetresponseIntegration_Getresponse_Helper_Api */
    private $api;

    /** @var GrCartBuilder */
    private $cartBuilder;

    /** @var GrProductHandler */
    private $productHandler;

    /** @var Mage_Sales_Model_Quote */
    private $quoteModel;

    /**
     * @param GetresponseIntegration_Getresponse_Helper_Api $api
     */
    public function __construct(GetresponseIntegration_Getresponse_Helper_Api $api)
    {
        $this->api = $api;
        $this->quoteModel = Mage::getModel('sales/quote');
        $this->cartBuilder = new GrCartBuilder($this->api);
        $this->productHandler = new GrProductHandler($this->api);
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param string                 $campaignId
     * @param string                 $email
     * @param string $storeId
     *
     * @return string
     * @throws Exception
     */
    public function sendCartToGetresponse(
        Mage_Sales_Model_Quote $quote,
        $campaignId,
        $email,
        $storeId
    ) {
        $grProducts = array();

        $subscriber = $this->api->getContact(
            $email,
            $campaignId
        );

        Mage::log('sendCartToGetresponse', 1, 'getresponse.log');

        if (!isset($subscriber->contactId)) {
            Mage::log('Subscriber not found during export - ' . $subscriber->email);
            return null;
        }

        /** @var Mage_Sales_Model_Quote_Item $product */
        foreach ($quote->getAllVisibleItems() as $product) {
             $grProducts[$product->getProduct()->getId()] = $this->productHandler->upsertGetresponseProduct($product->getProduct(), $storeId);
        }

        $params = $this->cartBuilder->buildGetresponseCart(
            $subscriber->contactId,
            $quote,
            $grProducts
        );

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $this->quoteModel->setStoreId($quote->getStoreId())->load($quote->getId());
        $grCartId = $quote->getData('getresponse_cart_id');

        if( !empty($grCartId) ) {
            $response = (array) $this->api->updateCart($storeId, $grCartId, $params);
        } else {
            $response = (array) $this->api->addCart($storeId, $params);
        }
        if (!isset($response['cartId'])) {
            return null;
        }

        $quote->setData('getresponse_cart_id', $response['cartId']);
        $quote->save();
        return $response['cartId'];
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param string                 $campaignId
     * @param string                 $email
     * @param string $storeId
     *
     * @return string
     * @throws Exception
     */
    public function sendCartToGetresponseFromOrder(
        Mage_Sales_Model_Order $order,
        $campaignId,
        $email,
        $storeId
    ) {
        $grProducts = array();

        $subscriber = $this->api->getContact(
            $email,
            $campaignId
        );

        if (!isset($subscriber->contactId)) {
            Mage::log('Subscriber not found during export - ' . $subscriber->email);
            return null;
        }

        /** @var Mage_Sales_Model_Order_Item $product */
        foreach ($order->getAllVisibleItems() as $product) {
            $grProducts[$product->getProduct()->getId()] = $this->productHandler->upsertGetresponseProduct($product->getProduct(), $storeId);
        }

        $params = $this->cartBuilder->buildGetresponseCartFromOrder(
            $subscriber->contactId,
            $order,
            $grProducts
        );

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $this->quoteModel->setStoreId($order->getStoreId())->load($order->getQuoteId());
        $grCartId = $quote->getData('getresponse_cart_id');

        if( !empty($grCartId) ) {
            $response = (array) $this->api->updateCart($storeId, $grCartId, $params);
        } else {
            $response = (array) $this->api->addCart($storeId, $params);
        }
        if (!isset($response['cartId'])) {
            return null;
        }

        $quote->setData('getresponse_cart_id', $response['cartId']);
        $quote->save();

        return $response['cartId'];
    }
}
