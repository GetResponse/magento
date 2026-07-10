<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento\Product\ReadModel;

use GetResponse\GetResponseIntegration\Domain\Magento\Product\ReadModel\Query\GetProduct;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\ObjectManagerInterface;

class ProductReadModel
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get product.
     *
     * @param GetProduct $query
     */
    public function getProduct(GetProduct $query): Product
    {
        return $this->objectManager->create(Product::class)->load($query->getId());
    }

    /**
     * Get product parents.
     *
     * @param GetProduct $query
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
