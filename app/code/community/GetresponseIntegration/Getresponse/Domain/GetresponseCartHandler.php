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

    /** @var string */
    private $shopId;

    /** @var GrCartBuilder */
    private $cartBuilder;

    /** @var GrProductHandler */
    private $productHandler;

    /** @var Mage_Sales_Model_Quote */
    private $quoteModel;

    /**
     * @param string $shopId
     */
    public function __construct($shopId)
    {
        $this->api = Mage::helper('getresponse/api');
        $this->quoteModel = Mage::getModel('sales/quote');
        $this->shopId = $shopId;
        $this->cartBuilder = new GrCartBuilder($this->api, $shopId);
        $this->productHandler = new GrProductHandler($this->api, $shopId);
    }

    /**
     * Mage_Sales_Model_Order @param $order
     * @param string $campaignId
     * @param string $email
     *
     * @return string
     * @throws Exception
     */
    public function sendCartToGetresponse(
        Mage_Sales_Model_Order $order,
        $campaignId,
        $email
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
        foreach ($order->getAllItems() as $product) {
            $grProducts[$product->getProduct()->getId()] = $this->productHandler->createGetresponseProduct($product);
        }

        $params = $this->cartBuilder->buildGetresponseCart(
            $subscriber->contactId,
            $order,
            $grProducts
        );

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $this->quoteModel->setStoreId($order->getStoreId())->load($order->getQuoteId());
        $grCartId = $quote->getData('getresponse_cart_id');

        if( !empty($grCartId) ) {
            $response = (array) $this->api->updateCart($this->shopId, $grCartId, $params);
        } else {
            $response = (array) $this->api->addCart($this->shopId, $params);
        }
        if (!isset($response['cartId'])) {
            return null;
        }

        $quote->setData('getresponse_cart_id', $response['cartId']);
        $quote->save();

        return $response['cartId'];
    }
}
