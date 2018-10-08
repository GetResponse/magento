<?php
namespace GetResponse\GetResponseIntegration\Test\Unit;

use GrShareCode\Address\Address;

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
}
