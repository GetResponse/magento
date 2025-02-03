<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use GetResponse\GetResponseIntegration\Domain\Magento\Product\ReadModel\ProductReadModel;
use GetResponse\GetResponseIntegration\Domain\Magento\Product\ReadModel\Query\GetProduct;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Framework\Exception\NoSuchEntityException;

class DeletedProductFactory extends ProductFactory
{
    /**
     * @param MagentoProduct $product
     * @param Scope $scope
     *
     * @return Product
     * @throws NoSuchEntityException
     */
    protected function createFromMagentoProduct(MagentoProduct $product, Scope $scope): Product
    {
        $variants = [];

        if ($this->productType->isProductConfigurable($product->getTypeId())) {
            $usedProducts = $product->getTypeInstance()->getUsedProducts($product);
            /** @var MagentoProduct $childProduct */
            foreach ($usedProducts as $childProduct) {
                $variants[] = new Variant(
                    (int)$childProduct->getId(),
                    "DELETED",
                    $childProduct->getSku(),
                    0.00,
                    0.00,
                    null,
                    null,
                    0,
                    "",
                    0,
                    null,
                    "",
                    "",
                    [],
                    Product::STATUS_DELETED,
                    null
                );
            }
        } else {
            $variants[] = new Variant(
                (int)$product->getId(),
                "DELETED",
                $product->getSku(),
                0.00,
                0.00,
                null,
                null,
                0,
                "",
                0,
                null,
                "",
                "",
                [],
                Product::STATUS_DELETED,
                null
            );
        }

        $categories = [];

        foreach ($product->getCategoryIds() as $id) {
            $category = $this->categoryRepository->get($id, (int)$scope->getScopeId());

            $categories[] = new Category(
                (int)$category->getId(),
                (int)$category->getParentId(),
                $category->getName()
            );
        }

        return new Product(
            (int)$product->getId(),
            "DELETED",
            $product->getTypeId(),
            $product->setStoreId($scope->getScopeId())->getUrlModel()->getUrlInStore($product),
            '',
            $categories,
            $variants,
            Product::STATUS_DELETED,
            $product->getCreatedAt(),
            $product->getUpdatedAt()
        );
    }

}
