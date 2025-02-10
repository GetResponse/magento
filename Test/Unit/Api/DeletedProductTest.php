<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Tests\Unit\Api;

use GetResponse\GetResponseIntegration\Api\CallbackType;
use GetResponse\GetResponseIntegration\Api\DeletedProduct;
use PHPUnit\Framework\TestCase;

class DeletedProductTest extends TestCase
{
    public function testJsonSerialize()
    {
        $productId = 42;
        $deletedProduct = new DeletedProduct($productId);

        $expectedJson = [
            'callback_type' => CallbackType::PRODUCT_DELETE,
            'id' => $productId
        ];

        $this->assertEquals($expectedJson, $deletedProduct->jsonSerialize());
    }

    public function testConstructorSetsCorrectProductId()
    {
        $productId = 123;
        $deletedProduct = new DeletedProduct($productId);

        $reflectionProperty = new \ReflectionProperty(DeletedProduct::class, 'id');
        $reflectionProperty->setAccessible(true);
        $actualProductId = $reflectionProperty->getValue($deletedProduct);

        $this->assertSame($productId, $actualProductId);
    }
}
