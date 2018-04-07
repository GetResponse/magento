<?php

use GetresponseIntegration_Getresponse_Helper_Api as ApiHelper;

/**
 * Class GetresponseIntegration_Getresponse_Domain_OrderPayloadBuilder
 */
class GetresponseIntegration_Getresponse_Domain_GetresponseOrderBuilder
{
    /** @var ApiHelper */
    private $api;

    /**
     * @param ApiHelper $api
     */
    public function __construct(ApiHelper $api)
    {
        $this->api = $api;
    }

    /**
     * @param string                 $subscriberId
     * @param Mage_Sales_Model_Order $order
     * @param $grCartId
     *
     * @return array
     * @throws Exception
     */
    public function createGetresponseOrder(
        $subscriberId,
        Mage_Sales_Model_Order $order,
        $grCartId
    ) {

        $shippingAddress = $order->getShippingAddress();

        if (empty($shippingAddress)) {
            $shippingAddress = $order->getBillingAddress();
        }

        return array(
            'contactId'       => $subscriberId,
            'totalPrice'      => $order->getGrandTotal(),
            'totalPriceTax'   => $order->getGrandTotal(),
            'cartId'          => $grCartId,
            'currency'        => $order->getOrderCurrencyCode(),
            'status'          => $order->getStatus(),
            'shippingPrice'   => $order->getShippingAmount(),
            'externalId'      => $order->getId(),
            'shippingAddress' => array(
                'countryCode' => $shippingAddress->getCountryModel()->getIso3Code(),
                'name' => $shippingAddress->getStreetFull(),
                'firstName' => $shippingAddress->getFirstname(),
                'lastName' => $shippingAddress->getLastname(),
                'city' => $shippingAddress->getCity(),
                'zip' => $shippingAddress->getPostcode(),
            ),
            'billingAddress' => array(
                'countryCode' => $order->getBillingAddress()->getCountryModel()->getIso3Code(),
                'name' => $order->getBillingAddress()->getStreetFull(),
                'firstName' => $order->getBillingAddress()->getFirstname(),
                'lastName' => $order->getBillingAddress()->getLastname(),
                'city' => $order->getBillingAddress()->getCity(),
                'zip' => $order->getBillingAddress()->getPostcode(),
            ),
        );
    }
}
