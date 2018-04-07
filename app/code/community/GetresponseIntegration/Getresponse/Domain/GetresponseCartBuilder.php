<?php

use GetresponseIntegration_Getresponse_Helper_Api as ApiHelper;

/**
 * Class GetresponseIntegration_Getresponse_Domain_GetresponseCartBuilder
 */
class GetresponseIntegration_Getresponse_Domain_GetresponseCartBuilder
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
     * @param Mage_Sales_Model_Quote $quote
     * @param array                  $gr_products
     *
     * @return array
     * @throws Exception
     */
    public function buildGetresponseCart(
        $subscriberId,
        Mage_Sales_Model_Quote $quote,
        $gr_products
    ) {
        $grVariants = array();

        /** @var Mage_Sales_Model_Order_Item $product */
        foreach ($quote->getAllItems() as $product) {

            $grProduct = $gr_products[$product->getProduct()->getId()];
            $variant = (array) reset($grProduct['variants']);

            $grVariants[] = array(
                'variantId' => $variant['variantId'],
                'price'     => (float)$product->getProduct()->getPrice(),
                'priceTax'  => (float)$product->getProduct()->getFinalPrice(),
                'quantity'  => (int)$product->getQtyOrdered()
            );
        }

        $params = array(
            'contactId'        => $subscriberId,
            'currency'         => $quote->getQuoteCurrencyCode(),
            'totalPrice'       => (float)$quote->getGrandTotal(),
            'selectedVariants' => $grVariants,
            'externalId'       => $quote->getId(),
            'totalTaxPrice'    => (float)$quote->getGrandTotal()
        );

        return $params;
    }

    /**
     * @param string                 $subscriberId
     * @param Mage_Sales_Model_Order $order
     * @param array                  $gr_products
     *
     * @return array
     * @throws Exception
     */
    public function buildGetresponseCartFromOrder(
        $subscriberId,
        Mage_Sales_Model_Order $order,
        $gr_products
    ) {
        $grVariants = array();

        /** @var Mage_Sales_Model_Order_Item $product */
        foreach ($order->getAllItems() as $product) {

            $grProduct = $gr_products[$product->getProduct()->getId()];

            $variant = (array)reset($grProduct['variants']);

            $grVariants[] = array(
                'variantId' => $variant['variantId'],
                'price'     => (float)$product->getProduct()->getPrice(),
                'priceTax'  => (float)$product->getProduct()->getFinalPrice(),
                'quantity'  => (int)$product->getQtyOrdered()
            );
        }

        $params = array(
            'contactId'        => $subscriberId,
            'currency'         => $order->getOrderCurrencyCode(),
            'totalPrice'       => (float)$order->getGrandTotal(),
            'selectedVariants' => $grVariants,
            'externalId'       => $order->getId(),
            'totalTaxPrice'    => (float)$order->getGrandTotal()
        );

        return $params;
    }
}
