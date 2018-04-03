<?php

use GetresponseIntegration_Getresponse_Helper_Api as ApiHelper;

/**
 * Class GetresponseIntegration_Getresponse_Domain_OrderPayloadBuilder
 */
class GetresponseIntegration_Getresponse_Domain_GetresponseOrderBuilder
{
    /** @var ApiHelper */
    private $api;

    /** @var string */
    private $shopId;

    /**
     * @param ApiHelper $api
     * @param string $shopId
     */
    public function __construct(ApiHelper $api, $shopId)
    {
        $this->api = $api;
        $this->shopId = $shopId;
    }

    /**
     * @param string $subscriber_id
     * @param Mage_Sales_Model_Order $order
     * @param $cart_id
     * @param array $gr_products
     * @return array
     * @throws Exception
     */
    public function createGetresponseOrder(
        $subscriber_id,
        Mage_Sales_Model_Order $order,
        $cart_id,
        $gr_products
    ) {
        $params = [
            'contactId' => $subscriber_id,
            'totalPrice' => $order->getGrandTotal(),
            'totalPriceTax' => $order->getGrandTotal(),
            'cartId' => $cart_id,
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

        /** @var Mage_Sales_Model_Order_Item $product */
        foreach ($order->getAllItems() as $product) {

            $grProduct = $gr_products[$product->getProduct()->getId()];

            $variant = (array) reset($grProduct['variants']);

            $params['selectedVariants'][] = [
                'variantId' => $variant['variantId'],
                'price' => (float) $product->getProduct()->getPrice(),
                'priceTax' => (float) $product->getProduct()->getFinalPrice() ,
                'quantity' => (int) $product->getQtyOrdered(),
                'type' => $product->getProductType(),
            ];
        }

        $response = (array) $this->api->createOrder(
            $this->shopId,
            $params
        );

        if (!isset($response['orderId'])) {
            return [];
        }

        $order->setData('getresponse_order_id', $response['orderId']);
        $order->setData('getresponse_order_md5', $this->createOrderPayloadHash($params));
        $order->save();

        return $response;
    }

    /**
     * @param array $orderPayload
     * @return string
     */
    protected function createOrderPayloadHash(array $orderPayload)
    {
        return md5(json_encode($orderPayload));
    }
}
