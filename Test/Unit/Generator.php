<?php
namespace GetResponse\GetResponseIntegration\Test\Unit;

use GrShareCode\Address\Address;
use GrShareCode\Order\Order;
use GrShareCode\Product\ProductsCollection;

/**
 * Class Generator
 * @package GetResponse\GetResponseIntegration\Test\Unit
 */
class Generator
{
    /**
     * @return Address
     */
    public static function createAddress()
    {
        return new Address('POL', 'default-address');
    }

    /**
     * @return Order
     */
    public static function createGrOrder($orderId = '')
    {
        return new Order(
            '1000021',
            new ProductsCollection(),
            80.00,
            99.99,
            '',
            'PLN',
            'pending',
            '20000043',
            '',
            20.00,
            '',
            '2018-09-22T12:01:01+0000',
            self::createAddress(),
            self::createAddress()
        );
    }
}
