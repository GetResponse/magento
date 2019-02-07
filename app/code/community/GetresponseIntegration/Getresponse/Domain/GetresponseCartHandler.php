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
     * @param GetresponseIntegration_Getresponse_Helper_Api                       $api
     * @param Mage_Sales_Model_Quote                                              $quoteModel
     * @param GetresponseIntegration_Getresponse_Domain_GetresponseCartBuilder    $cartBuilder
     * @param GetresponseIntegration_Getresponse_Domain_GetresponseProductHandler $productBuilder
     */
    public function __construct(
        GetresponseIntegration_Getresponse_Helper_Api $api,
        Mage_Sales_Model_Quote $quoteModel,
        GrCartBuilder $cartBuilder,
        GrProductHandler $productBuilder
    ) {
        $this->api = $api;
        $this->quoteModel = $quoteModel;
        $this->cartBuilder = $cartBuilder;
        $this->productHandler = $productBuilder;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param string                 $campaignId
     * @param string                 $email
     * @param string                 $storeId
     *
     * @return string
     */
    public function sendCartToGetresponse(
        Mage_Sales_Model_Quote $quote,
        $campaignId,
        $email,
        $storeId
    ) {
        $grProducts = array();

        try {
            $subscriber = $this->api->getContact(
                $email,
                $campaignId
            );

            if (empty($subscriber)) {
                GetresponseIntegration_Getresponse_Helper_Logger::log('Subscriber not found during export - ' . $email);
                return null;
            }

            /** @var Mage_Sales_Model_Quote_Item $product */
            foreach ($quote->getAllVisibleItems() as $product) {
                $grProducts[$product->getProduct()->getId()]
                    = $this->productHandler->upsertGetresponseProduct(
                    $product->getProduct(), $storeId
                );
            }

            $params = $this->cartBuilder->buildGetresponseCart(
                $subscriber['contactId'],
                $quote,
                $grProducts
            );

            /** @var Mage_Sales_Model_Quote $quote */
            $quote = $this->quoteModel->setStoreId($quote->getStoreId())->load($quote->getId());
            $grCartId = $quote->getData('getresponse_cart_id');

            if( !empty($grCartId) ) {
                $cart = $this->api->updateCart($storeId, $grCartId, $params);
            } else {
                $cart = $this->api->addCart($storeId, $params);
            }
            if (!isset($cart['cartId'])) {
                return null;
            }

            $quote->setData('getresponse_cart_id', $cart['cartId']);
            $quote->save();
            return $cart['cartId'];

        } catch (Exception $e) {
            GetresponseIntegration_Getresponse_Helper_Logger::logException($e);
        }

        return null;
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

        try {
            $subscriber = $this->api->getContact(
                $email,
                $campaignId
            );
        } catch (GetresponseIntegration_Getresponse_Domain_GetresponseException $e) {
            GetresponseIntegration_Getresponse_Helper_Logger::logException($e);
            return null;
        }

        if (empty($subscriber)) {
            GetresponseIntegration_Getresponse_Helper_Logger::log('Subscriber not found during export - ' . $email);
            return null;
        }

        /** @var Mage_Sales_Model_Order_Item $product */
        foreach ($order->getAllVisibleItems() as $product) {

            if (!$product->getProduct()->getId()) {
                GetresponseIntegration_Getresponse_Helper_Logger::log(sprintf('Product id: %s name: %s in order id %s not found', $product->getId(), $product->getName(), $order->getId()));
                return null;
            }

            $grProducts[$product->getProduct()->getId()] = $this->productHandler->upsertGetresponseProduct($product->getProduct(), $storeId);
        }

        $params = $this->cartBuilder->buildGetresponseCartFromOrder(
            $subscriber['contactId'],
            $order,
            $grProducts
        );

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $this->quoteModel->setStoreId($order->getStoreId())->load($order->getQuoteId());
        $grCartId = $quote->getData('getresponse_cart_id');

        if( empty($grCartId) ) {
            $cart = $this->api->addCart($storeId, $params);

            if (!isset($cart['cartId'])) {
                return null;
            }

            $quote->setData('getresponse_cart_id', $cart['cartId']);
            $quote->save();

            return $cart['cartId'];
        }

        return $grCartId;
    }
}
