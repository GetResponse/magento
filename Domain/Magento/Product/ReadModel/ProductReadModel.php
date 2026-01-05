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
        return $this->objectManager->create(Product::class)->load($query->getId());
    }

    /**
     * @return Product[]
     */
    public function getProductParents(GetProduct $query): array
    {
        $productObject = $this->objectManager->create(Configurable::class);
        $parentProductsIds = $productObject->getParentIdsByChild($query->getId());

        $products = [];
        foreach ($parentProductsIds as $parentProductsId) {
            $products[] = $this->getProduct(new GetProduct($parentProductsId));
        }

        return $products;
    }
}
