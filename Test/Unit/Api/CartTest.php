<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Api;

use GetResponse\GetResponseIntegration\Api\CallbackType;
use GetResponse\GetResponseIntegration\Api\Cart;
use GetResponse\GetResponseIntegration\Api\Customer;
use GetResponse\GetResponseIntegration\Api\Line;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class CartTest extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldSerializeCart(): void
    {
        $callback_type = CallbackType::CHECKOUT_UPDATE;
        $id = 3393;
        $customerEmail = 'some@example.com';
        $customerAsArray = ['id' => 4949, 'email' => $customerEmail];
        $lineAsArray = ['variant_id' => 493, 'price' => 23.99, 'price_tax' => 30.21];
        $totalPrice = 9.99;
        $totalPriceTax = 12.22;
        $currency = 'EUR';
        $url = 'http://shop.magento.com';
        $createdAt = '2021-05-12 12:23:55';
        $updatedAt = '2021-05-13 16:43:21';

        /** @var Customer|MockObject $customerMock */
        $customerMock = $this->getMockWithoutConstructing(Customer::class);
        /** @var Line|MockObject $lineMock */
        $lineMock = $this->getMockWithoutConstructing(Line::class);
        $lines = [$lineMock];

        $cart = new Cart($id, $customerMock, $lines, $totalPrice, $totalPriceTax, $currency, $url, $createdAt, $updatedAt);

        $expectedData = [
            'callback_type' => $callback_type,
            'id' => $id,
            'contact_email' => $customerEmail,
            'customer' => $customerAsArray,
            'lines' => [$lineAsArray],
            'total_price' => $totalPrice,
            'total_price_tax' => $totalPriceTax,
            'currency' => $currency,
            'url' => $url,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];

        $customerMock->method('getEmail')->willReturn($customerEmail);
        $customerMock->method('jsonSerialize')->willReturn($customerAsArray);
        $lineMock->method('jsonSerialize')->willReturn($lineAsArray);

        $data = $cart->jsonSerialize();

        self::assertEquals($expectedData, $data);
    }
}
