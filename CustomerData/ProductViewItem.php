<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\CustomerData;

use Magento\Catalog\Block\Product\View as ProductView;
use \Magento\Catalog\Model\Product;

class ProductViewItem
{
    const DISPLAY_BLOCK = 'product.info';

    public function __construct()
    {
    }

    public function afterToHtml(ProductView $subject, string $html): string
    {
        if ($subject->getNameInLayout() !== self::DISPLAY_BLOCK) {
            return $html;
        }

        $product = $subject->getProduct();

        $payload = [
            'shop' => ['id' => $product->getStoreId()],
            'product' => [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'sku' => $product->getSku(),
                'vendor' => null,
                'price' => number_format((float)$product->getPrice(), 2),
                'currency' => ''
            ],
        ];

        $html .= $this->addRecommendationPayload($payload);

        return $html;
    }

    private function addRecommendationPayload(array $payload): string
    {
        return "";
    }
}
