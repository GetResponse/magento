<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit;

use GetResponse\GetResponseIntegration\Api\Address;
use GetResponse\GetResponseIntegration\Api\Cart;
use GetResponse\GetResponseIntegration\Api\Customer;
use GetResponse\GetResponseIntegration\Api\Product;
use GetResponse\GetResponseIntegration\Domain\Magento\Visitor;

class ApiFaker
{
    public static function createAddress(): Address
    {
        return new Address(
            'Brian Sings',
            'OK',
            'Brian',
            'Sings',
            '4508  Memory Lane',
            '',
            'GUTHRIE',
            '73044',
            'Oklahoma',
            '',
            '544404400',
            ''
        );
    }

    public static function createCustomer(): Customer
    {
        return new Customer(
            391,
            'bsings0@house.gov',
            'Brian',
            'Sings',
            true,
            self::createAddress(),
            [],
            self::createAddress()->toCustomFieldsArray('shipping')
        );
    }

    public static function createCart(): Cart
    {
        return new Cart(
            320,
            self::createCustomer(),
            null,
            [],
            104.29,
            129.99,
            'EUR',
            'http://magento.com/cart/3d938d9ff',
            '2020-03-22 06:04:22',
            '2020-03-22 06:04:22'
        );
    }

    public static function createCartWithVisitor(): Cart
    {
        return new Cart(
            320,
            null,
            self::createVisitor(),
            [],
            104.29,
            129.99,
            'EUR',
            'http://magento.com/cart/3d938d9ff',
            '2020-03-22 06:04:22',
            '2020-03-22 06:04:22'
        );
    }

    public static function createProduct(): Product
    {
        return new Product(
            659,
            'Josef Seibel Men - Graphite',
            'configurable',
            'https://magento.com/product/3fu#3fuf9',
            'product',
            [],
            [],
            '2020-03-22 06:04:22',
            '2020-03-22 06:04:22'
        );
    }

    public static function createVisitor(): Visitor
    {
        return new Visitor('8452299b-5ca4-4ac4-9a1b-643645f02526');
    }
}
