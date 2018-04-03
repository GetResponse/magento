<?php

use GetresponseIntegration_Getresponse_Helper_Api as ApiHelper;

/**
 * Class GetresponseIntegration_Getresponse_Domain_GetresponseCartBuilder
 */
class GetresponseIntegration_Getresponse_Domain_GetresponseCartBuilder
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
     * @param string $subscriberId
     * @param Mage_Sales_Model_Order $order
     * @param array $gr_products
     *
     * @return array
     * @throws Exception
     */
    public function buildGetresponseCart($subscriberId, Mage_Sales_Model_Order $order, $gr_products)
    {
        $grVariants = [];

        /** @var Mage_Sales_Model_Order_Item $product */
        foreach ($order->getAllItems() as $product) {

            $grProduct = $gr_products[$product->getProduct()->getId()];

            $variant = (array) reset($grProduct['variants']);

            $grVariants[] = [
                'variantId' => $variant['variantId'],
                'price' => (float) $product->getProduct()->getPrice(),
                'priceTax' => (float) $product->getProduct()->getFinalPrice(),
                'quantity' => (int) $product->getQtyOrdered()
            ];
        }

        $params = [
            'contactId' => $subscriberId,
            'currency' => $order->getOrderCurrencyCode(),
            'totalPrice' => (float) $order->getGrandTotal(),
            'selectedVariants' => $grVariants,
            'externalId' => $order->getId(),
            'totalTaxPrice' => (float) $order->getGrandTotal()
        ];

        $response = (array) $this->api->addCart($this->shopId, $params);

        if (!isset($response['cartId'])) {
            return [];
        }

        $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
        $quote->setData('getresponse_cart_id', $response['cartId']);
        $quote->save();

        return $response;
    }
}
