<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class ProductType
{
    public function isProductConfigurable(string $productType): bool
    {
        return Configurable::TYPE_CODE === $productType;
    }
}
