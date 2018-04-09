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

    /** @var GrProductHandler */
    private $productHandler;

    /** @var GrOrderBuilder */
    private $orderBuilder;

    /**
     * @param GetresponseIntegration_Getresponse_Helper_Api $api
     */
    public function __construct(GetresponseIntegration_Getresponse_Helper_Api $api)
    {
        $this->api = $api;
        $this->productHandler = new GrProductHandler($this->api);
        $this->orderBuilder = new GrOrderBuilder($this->api);
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param string                 $email
     * @param string                 $campaignId
     * @param string                 $grCartId
     * @param string                 $storeId
     * @param bool                   $newOrder
     *
     * @throws Exception
     */
    public function sendOrderToGetresponse(
        Mage_Sales_Model_Order $order,
        $email,
        $campaignId,
        $grCartId,
        $storeId,
        $newOrder = false
    ) {
        $subscriber = $this->api->getContact(
            $email,
            $campaignId
        );

        if (!isset($subscriber->contactId)) {
            Mage::log('Subscriber not found during export - ' . $email);
            return;
        }

        $params = $this->orderBuilder->createGetresponseOrder(
            $subscriber->contactId,
            $order,
            $grCartId
        );

        /** @var Mage_Sales_Model_Order_Item $product */
        foreach ($order->getAllItems() as $product) {

            $grProduct = $this->productHandler->upsertGetresponseProduct($product->getProduct(), $storeId);
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
                $storeId,
                $grOrderId,
                $params
            );
        } else {
            $response = (array) $this->api->createOrder(
                $storeId,
                $params
            );
        }

        if (!isset($response['orderId'])) {
            return;
        }

        if ($newOrder) {
            $order->setData('getresponse_order_id', $response['orderId']);
            $order->setData(
                'getresponse_order_md5', $this->createOrderPayloadHash($params)
            );
            $order->save();
        }
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
