<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Api;

use GetResponse\GetResponseIntegration\Api\Customer;
use GetResponse\GetResponseIntegration\Api\Line;
use GetResponse\GetResponseIntegration\Api\Order;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GetResponse\GetResponseIntegration\Test\Unit\ApiFaker;

class OrderTest extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldSerializeOrder(): void
    {
        $address = ApiFaker::createAddress();

        $line = new Line(40003, 9.99, 12.22, 40, 'variant_40003');

        $id = 10001;
        $orderNumber = 'order_100001';
        $cartId = 10030;
        $contactEmail = 'some@example.com';
        $url = 'https://store.magento.com';
        $totalPrice = 9.99;
        $totalPriceTax = 12.22;
        $shippingPrice = 5;
        $currency = 'EUR';
        $status = 'pending';
        $billingStatus = 'new';
        $createdAt = '2021-05-12 23:01:22';
        $updatedAt = '2021-05-12 23:05:40';

        $customerId = 3002;
        $customerEmail = 'some@example2.com';
        $customerFirstName = 'John';
        $customerLastName = 'Smith';
        $customerIsMarketingAccepted = true;
        $customerTags = ['magento', 'api'];
        $customerCustomFields = ['source' => 'api'];

        $expectedData = [
            'callback_type' => 'orders/update',
            'id' => $id,
            'order_number' => $orderNumber,
            'cart_id' => $cartId,
            'contact_email' => $contactEmail,
            'customer' => [
                'callback_type' => 'customers/update',
                'id' => $customerId,
                'email' => $customerEmail,
                'first_name' => $customerFirstName,
                'last_name' => $customerLastName,
                'accepts_marketing' => $customerIsMarketingAccepted,
                'address' => $address->jsonSerialize(),
                'tags' => $customerTags,
                'customFields' => $customerCustomFields
            ],
            'lines' => [
                $line->jsonSerialize()
            ],
            'url' => $url,
            'total_price' => $totalPrice,
            'total_price_tax' => $totalPriceTax,
            'shipping_price' => $shippingPrice,
            'currency' => $currency,
            'status' => $status,
            'billing_status' => $billingStatus,
            'shipping_address' => $address->jsonSerialize(),
            'billing_address' => $address->jsonSerialize(),
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];

        $order = new Order(
            $id,
            $orderNumber,
            $cartId,
            $contactEmail,
            new Customer(
                $customerId,
                $customerEmail,
                $customerFirstName,
                $customerLastName,
                $customerIsMarketingAccepted,
                $address,
                $customerTags,
                $customerCustomFields
            ),
            [$line],
            $url,
            $totalPrice,
            $totalPriceTax,
            $shippingPrice,
            $currency,
            $status,
            $billingStatus,
            $address,
            $address,
            $createdAt,
            $updatedAt
        );

        self::assertEquals($expectedData, $order->jsonSerialize());
    }
}
