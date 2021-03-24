<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class ProductFactory
{
    private $categoryRepository;
    private $stockRepository;

    public function __construct(
        CategoryRepository $categoryRepository,
        StockItemRepository $stockRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->stockRepository = $stockRepository;
    }

    public function create(MagentoProduct $product, Scope $scope): Product
    {
        $variants = [];

        if ($product->getTypeId() === Configurable::TYPE_CODE) {

            $usedProducts = $product->getTypeInstance()->getUsedProducts($product);
            /** @var MagentoProduct $childProduct */
            foreach ($usedProducts as $childProduct) {
                $images = [];
                foreach ($childProduct->getMediaGalleryImages() as $image) {
                    $images[] = new Image(
                        $image->getData('url'),
                        (int)$image->getData('position')
                    );
                }

                $stockItem = $this->stockRepository->get($childProduct->getId());

                $variants[] = new Variant(
                    (int)$childProduct->getId(),
                    $childProduct->getName(),
                    $childProduct->getSku(),
                    (float)$childProduct->getPrice(),
                    (float)$childProduct->getPrice(),
                    null,
                    null,
                    (int) $stockItem->getQty(),
                    0,
                    null,
                    $childProduct->getData('short_description') ?? '',
                    $images
                );
            }
        } else {

            $images = [];
            foreach ($product->getMediaGalleryImages() as $image) {
                $images[] = new Image(
                    $image->getData('url'),
                    (int)$image->getData('position')
                );
            }

            $stockItem = $this->stockRepository->get($product->getId());

            $variants[] = new Variant(
                (int)$product->getId(),
                $product->getName(),
                $product->getSku(),
                (float)$product->getPrice(),
                (float)$product->getPrice(),
                null,
                null,
                (int) $stockItem->getQty(),
                0,
                null,
                $product->getData('short_description') ?? '',
                $images
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
            $product->getName(),
            $product->getTypeId(),
            $product->setStoreId($scope->getScopeId())->getUrlModel()->getUrlInStore($product),
            '',
            $categories,
            $variants,
            $product->getCreatedAt(),
            $product->getUpdatedAt()
        );
    }
}
