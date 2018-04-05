<?php

use GetresponseIntegration_Getresponse_Domain_GetresponseOrderBuilder as GrOrderBuilder;
use GetresponseIntegration_Getresponse_Domain_GetresponseProductHandler as GrProductHandler;

/**
 * Class GetresponseIntegration_Getresponse_Domain_GetresponseOrderHandler
 */
class GetresponseIntegration_Getresponse_Domain_GetresponseOrderHandler
{
    /** @var GetresponseIntegration_Getresponse_Helper_Api */
    private $api;

    /** @var string */
    private $shopId;

    /** @var GrProductHandler */
    private $productHandler;

    /** @var GrOrderBuilder */
    private $orderBuilder;

    /**
     * @param string $shopId
     */
    public function __construct($shopId)
    {
        $this->api = Mage::helper('getresponse/api');
        $this->shopId = $shopId;
        $this->productHandler = new GrProductHandler($this->api, $shopId);
        $this->orderBuilder = new GrOrderBuilder($this->api, $shopId);
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param string $email
     * @param string $campaignId
     * @param string $cartId
     * @throws Exception
     */
    public function sendOrderToGetresponse(
        Mage_Sales_Model_Order $order,
        $email,
        $campaignId,
        $cartId
    ) {
        $grProducts = array();
        $subscriber = $this->api->getContact(
            $email,
            $campaignId
        );

        if (!isset($subscriber->contactId)) {
            Mage::log('Subscriber not found during export - ' . $email);
            return;
        }

        /** @var Mage_Sales_Model_Order_Item $product */
        foreach ($order->getAllItems() as $product) {
            $grProducts[$product->getProduct()->getId()] = $this->productHandler->createGetresponseProduct($product);
        }

        $params = $this->orderBuilder->createGetresponseOrder(
            $subscriber->contactId,
            $order,
            $cartId
        );

        /** @var Mage_Sales_Model_Order_Item $product */
        foreach ($order->getAllItems() as $product) {

            $grProduct = $grProducts[$product->getProduct()->getId()];

            $variant = (array) reset($grProduct['variants']);

            $params['selectedVariants'][] = array(
                'variantId' => $variant['variantId'],
                'price' => (float) $product->getProduct()->getPrice(),
                'priceTax' => (float) $product->getProduct()->getFinalPrice() ,
                'quantity' => (int) $product->getQtyOrdered(),
                'type' => $product->getProductType(),
            );
        }

        $grOrderId = $order->getData('getresponse_order_id');

        if ( !empty($grOrderId) ) {
            $response = (array) $this->api->updateOrder(
                $this->shopId,
                $grOrderId,
                $params
            );
        } else {
            $response = (array) $this->api->createOrder(
                $this->shopId,
                $params
            );
        }

        if (!isset($response['orderId'])) {
            return;
        }

        $order->setData('getresponse_order_id', $response['orderId']);
        $order->setData('getresponse_order_md5', $this->createOrderPayloadHash($params));
        $order->save();
    }


    /**
     * @param array $orderPayload
     * @return string
     */
    private function createOrderPayloadHash(array $orderPayload)
    {
        return md5(json_encode($orderPayload));
    }
}
