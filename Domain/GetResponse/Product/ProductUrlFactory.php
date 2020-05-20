<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Product;

use GetResponse\GetResponseIntegration\Domain\Magento\Product\ReadModel\ProductReadModel;
use GetResponse\GetResponseIntegration\Domain\Magento\Product\ReadModel\Query\GetProduct;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;

class ProductUrlFactory
{
    private $productReadModel;

    public function __construct(ProductReadModel $productReadModel)
    {
        $this->productReadModel = $productReadModel;
    }

    public function fromProduct(Product $magentoProduct)
    {
        if ($magentoProduct->getVisibility() !== Visibility::VISIBILITY_NOT_VISIBLE) {
            return $magentoProduct->getProductUrl();
        }

        $magentoParentProduct = $this->productReadModel->getProductParent(
            new GetProduct($magentoProduct->getId())
        );

        if ($magentoParentProduct === null) {
            return null;
        }

        return $magentoParentProduct->getProductUrl();
    }
}
