<?php

namespace GetResponse\GetResponseIntegration\Builder;

use GetResponse\GetResponseIntegration\Api\DeletedProductFactory;
use GetResponse\GetResponseIntegration\Api\ProductFactoryInterface;
use GetResponse\GetResponseIntegration\Api\ProductFactory;
use Magento\Catalog\Model\Product as MagentoProduct;

class ProductFactoryBuilder
{
    protected $productFactory;
    protected $deletedProductFactory;

    public function __construct(
        ProductFactory $productFactory,
        DeletedProductFactory $deletedProductFactory
    )
    {
        $this->productFactory = $productFactory;
        $this->deletedProductFactory = $deletedProductFactory;
    }

    public function fromMagentoProduct(MagentoProduct $product): ProductFactoryInterface
    {
        if ($product->isDeleted()) {
            return $this->deletedProductFactory;
        }

        return $this->productFactory;
    }
}
