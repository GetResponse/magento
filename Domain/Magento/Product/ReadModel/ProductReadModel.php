<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento\Product\ReadModel;

use GetResponse\GetResponseIntegration\Domain\Magento\Product\ReadModel\Query\GetProduct;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\ObjectManagerInterface;

class ProductReadModel
{
    private $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function getProduct(GetProduct $query): Product
    {
        $productObject = $this->objectManager->create(Product::class);
        return $productObject->load($query->getId());
    }

    public function getProductParent(GetProduct $query)
    {
        $productObject = $this->objectManager->create(Configurable::class);
        $parentProductsIds = $productObject->getParentIdsByChild($query->getId());

        return $this->getProduct(new GetProduct($parentProductsIds[0]));
    }
}
