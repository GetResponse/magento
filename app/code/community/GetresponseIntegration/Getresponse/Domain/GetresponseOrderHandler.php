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
     * @param GetresponseIntegration_Getresponse_Helper_Api                       $api
     * @param GetresponseIntegration_Getresponse_Domain_GetresponseProductHandler $productHandler
     * @param GetresponseIntegration_Getresponse_Domain_GetresponseOrderBuilder   $orderBuilder
     */
    public function __construct(
        GetresponseIntegration_Getresponse_Helper_Api $api,
        GrProductHandler $productHandler,
        GrOrderBuilder $orderBuilder
    ) {
        $this->api = $api;
        $this->productHandler = $productHandler;
        $this->orderBuilder = $orderBuilder;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param string                 $email
     * @param string                 $campaignId
     * @param string                 $grCartId
     * @param string                 $storeId
     * @param bool                   $newOrder
     * @param bool                   $skipAutomation
     *
     * @return string|null
     */
    public function sendOrderToGetresponse(
        Mage_Sales_Model_Order $order,
        $email,
        $campaignId,
        $grCartId,
        $storeId,
        $newOrder = false,
        $skipAutomation = false
    ) {

        try {
            $subscriber = $this->api->getContact(
                $email,
                $campaignId
            );

            if (!isset($subscriber['contactId'])) {
                GetresponseIntegration_Getresponse_Helper_Logger::log('Subscriber not found during export - ' . $email);
                return;
            }

            $params = $this->orderBuilder->createGetresponseOrder(
                $subscriber['contactId'],
                $order,
                $grCartId
            );

            /** @var Mage_Sales_Model_Order_Item $product */
            foreach ($order->getAllVisibleItems() as $product) {

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
                unset($params['cartId']);
                $grOrder = $this->api->updateOrder(
                    $storeId,
                    $grOrderId,
                    $params,
                    $skipAutomation
                );
            } else {
                $grOrder = $this->api->createOrder(
                    $storeId,
                    $params,
                    $skipAutomation
                );

                $this->api->deleteCart($storeId, $grCartId);
            }

            if (!isset($grOrder['orderId'])) {
                return;
            }

            if ($newOrder) {
                $order->setData('getresponse_order_id', $grOrder['orderId']);
                $order->setData(
                    'getresponse_order_hash', $this->createOrderPayloadHash($params)
                );
                $order->save();
            }

        } catch (Exception $e) {
            GetresponseIntegration_Getresponse_Helper_Logger::logException($e);
        }
        return null;
    }


    /**
     * @param array $orderPayload
     * @return string
     */
    private function createOrderPayloadHash(array $orderPayload)
    {
        return hash('sha512', json_encode($orderPayload));
    }
}
