<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use GetResponse\GetResponseIntegration\Domain\Magento\Product\ReadModel\ProductReadModel;
use GetResponse\GetResponseIntegration\Domain\Magento\Product\ReadModel\Query\GetProduct;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Framework\Exception\NoSuchEntityException;

class ProductFactory
{
    private $categoryRepository;
    private $productReadModel;
    private $productType;

    public function __construct(
        CategoryRepository $categoryRepository,
        ProductReadModel $productReadModel,
        ProductType $productType
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->productReadModel = $productReadModel;
        $this->productType = $productType;
    }

    /**
     * @return Product[]
     */
    public function create(MagentoProduct $product, Scope $scope): array
    {
        $magentoProducts = $this->getParentProducts($product);

        $products = [];
        foreach ($magentoProducts as $magentoProduct) {
            $products[] = $this->createFromMagentoProduct($magentoProduct, $scope);
        }

        return $products;
    }

    /**
     * @return MagentoProduct[]
     */
    private function getParentProducts(MagentoProduct $product): array
    {
        $products = [$product];

        if ((int)$product->getVisibility() === MagentoProduct\Visibility::VISIBILITY_NOT_VISIBLE) {
            $products = $this->productReadModel->getProductParents(new GetProduct($product->getId()));
        }

        return $products;
    }

    /**
     * @param MagentoProduct $product
     * @param Scope $scope
     *
     * @return Product
     * @throws NoSuchEntityException
     */
    private function createFromMagentoProduct(MagentoProduct $product, Scope $scope): Product
    {
        $variants = [];

        $extensionAttributes = $product->getExtensionAttributes();

        $productQuantity = $extensionAttributes !== null ? (int) $extensionAttributes->getStockItem() : 0;

        if ($this->productType->isProductConfigurable($product->getTypeId())) {
            $usedProducts = $product->getTypeInstance()->getUsedProducts($product);
            /** @var MagentoProduct $childProduct */
            foreach ($usedProducts as $childProduct) {
                $images = $this->getImages($childProduct);
                if (empty($images)) {
                    $images = $this->getImages($product);
                }

                $variants[] = new Variant(
                    (int)$childProduct->getId(),
                    $childProduct->getName(),
                    $childProduct->getSku(),
                    (float)$childProduct->getPrice(),
                    (float)$childProduct->getPrice(),
                    null,
                    null,
                    $productQuantity,
                    $this->getProductConfigurableUrl($product, $childProduct, (int)$scope->getScopeId()),
                    0,
                    null,
                    (string)$childProduct->getData('description'),
                    (string)$childProduct->getData('short_description'),
                    $images
                );
            }
        } else {
            $images = $this->getImages($product);

            $variants[] = new Variant(
                (int)$product->getId(),
                $product->getName(),
                $product->getSku(),
                (float)$product->getPrice(),
                (float)$product->getPrice(),
                null,
                null,
                $productQuantity,
                $product->setStoreId($scope->getScopeId())->getUrlModel()->getUrlInStore($product),
                0,
                null,
                (string)$product->getData('description'),
                (string)$product->getData('short_description'),
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

    private function getProductConfigurableUrl(
        MagentoProduct $parentProduct,
        MagentoProduct $simpleProduct,
        int $storeId
    ): string {
        $configType = $parentProduct->getTypeInstance();
        $attributes = $configType->getConfigurableAttributesAsArray($parentProduct);
        $options = [];
        foreach ($attributes as $attribute) {
            $id = $attribute['attribute_id'];
            $value = $simpleProduct->getData($attribute['attribute_code']);
            $options[$id] = $value;
        }
        $options = http_build_query($options);

        $mainUrl = $parentProduct->setStoreId($storeId)->getUrlModel()->getUrlInStore($parentProduct);

        return $mainUrl . ($options ? '#' . $options : '');
    }

    private function getImages(MagentoProduct $product): array
    {
        $images = [];
        foreach ($product->getMediaGalleryImages() as $image) {
            $images[] = new Image(
                $image->getData('url'),
                (int)$image->getData('position')
            );
        }

        return $images;
    }
}
