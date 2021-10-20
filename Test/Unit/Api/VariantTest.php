<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Api;

use GetResponse\GetResponseIntegration\Api\Image;
use GetResponse\GetResponseIntegration\Api\Variant;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;

class VariantTest extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldCreateVariant(): void
    {
        $id = 5001;
        $name = 'ProductVariantName';
        $sku = 'sku_5001';
        $price = 9.99;
        $priceTax = 12.23;
        $previousPrice = 9.00;
        $previousPriceTax = 11.99;
        $quantity = 90;
        $url = 'http://store.magento.com/product/1/variant/5001';
        $position = 0;
        $barcode = 110202;
        $description = 'description for product variant';
        $shortDescription = 'short description for product variant';
        $imageSrc = 'http://store.magento.com/product/1/image/22929';
        $imagePosition = 0;

        $image = new Image($imageSrc, $imagePosition);

        $expectedVariant = [
            'id' => $id,
            'name' => $name,
            'sku' => $sku,
            'price' => $price,
            'price_tax' => $priceTax,
            'previous_price' => $previousPrice,
            'previous_price_tax' => $previousPriceTax,
            'quantity' => $quantity,
            'url' => $url,
            'position' => $position,
            'barcode' => $barcode,
            'description' => $description,
            'short_description' => $shortDescription,
            'images' => [
                [
                    'src' => $imageSrc,
                    'position' => $imagePosition
                ]
            ]
        ];

        $variant = new Variant(
            $id, 
            $name,
            $sku,
            $price,
            $priceTax,
            $previousPrice,
            $previousPriceTax,
            $quantity,
            $url,
            $position,
            $barcode,
            $description,
            $shortDescription,
            [$image]
        );
        self::assertEquals($expectedVariant, $variant->jsonSerialize());
    }
}
