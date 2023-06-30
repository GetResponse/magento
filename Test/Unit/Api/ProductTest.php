<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Api;

use GetResponse\GetResponseIntegration\Api\Category;
use GetResponse\GetResponseIntegration\Api\Product;
use GetResponse\GetResponseIntegration\Api\Variant;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;

class ProductTest extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldSerializeProduct(): void
    {
        $productId = 2002;
        $name = 'TestProduct';
        $type = 'simple';
        $url = 'http://store.magento.com/img/1/12/12.jpg';
        $vendor = 'manufacture';
        $createdAt = '2021-05-01 12:48:54';
        $updatedAt = '2021-05-01 12:49:12';
        $categoryId = 1;
        $variantId = 32221;

        $categoryMock = $this->getMockWithoutConstructing(Category::class);
        $categoryMock->method('jsonSerialize')->willReturn(['category_id' => $categoryId]);
        $variantMock = $this->getMockWithoutConstructing(Variant::class);
        $variantMock->method('jsonSerialize')->willReturn(['variant_id' => $variantId]);

        $expectedData = [
            'callback_type' => 'products/update',
            'id' => $productId,
            'name' => $name,
            'type' => $type,
            'url' => $url,
            'vendor' => $vendor,
            'status' => Product::STATUS_PUBLISH,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
            'categories' => [
                ['category_id' => $categoryId]
            ],
            'variants' => [
                ['variant_id' => $variantId]
            ],
        ];

        $product = new Product(
            $productId,
            $name,
            $type,
            $url,
            $vendor,
            [$categoryMock],
            [$variantMock],
            Product::STATUS_PUBLISH,
            $createdAt,
            $updatedAt
        );

        self::assertEquals($expectedData, $product->jsonSerialize());
    }
}
