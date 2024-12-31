<?php

namespace GetResponse\GetResponseIntegration\Helper;

abstract class ProductsPayloadDeserializer
{
    /**
     * Deserializes abandoned cart payload from format "product_id:qty;product_id:qty"
     * @param string $payload
     * @return array
     */
    public static function fromAbandonedCart(string $payload): array
    {
        $result = [];
        $items = explode(';', $payload);

        foreach ($items as $item) {
            list($productId, $qty) = explode(':', $item);
            $result[] = [
                'product_id' => (int)$productId,
                'qty' => (int)$qty,
            ];
        }

        return $result;
    }
}
