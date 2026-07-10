<?php

namespace GetResponse\GetResponseIntegration\Api;

use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use Magento\Catalog\Model\Product as MagentoProduct;

interface ProductFactoryInterface
{
    /**
     * Handle create.
     *
     * @param MagentoProduct $product
     * @param Scope $scope
     */
    public function create(MagentoProduct $product, Scope $scope): array;
}
