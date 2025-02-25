<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use GetResponse\GetResponseIntegration\Domain\Magento\Product\ReadModel\ProductReadModel;
use GetResponse\GetResponseIntegration\Domain\Magento\Product\ReadModel\Query\GetProduct;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Framework\Exception\NoSuchEntityException;
use JsonSerializable;

class ProductFactory
{
    private const PRODUCT_STATUS_ACTIVE = 1;
    private const PRODUCT_INVISIBLE = 1;

    private $categoryRepository;
    private $productReadModel;
    protected $productType;

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
    protected function createFromMagentoProduct(MagentoProduct $product, Scope $scope): Product
    {
        $variants = [];

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
                    $this->getProductQuantity((int)$childProduct->getId()),
                    $this->getProductConfigurableUrl($product, $childProduct, (int)$scope->getScopeId()),
                    0,
                    null,
                    (string)$childProduct->getData('description'),
                    (string)$childProduct->getData('short_description'),
                    $images,
                    $this->getProductVariantStatus($childProduct),
                    $this->getSalesPrice($childProduct)
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
                $this->getProductQuantity((int)$product->getId()),
                $product->setStoreId($scope->getScopeId())->getUrlModel()->getUrlInStore($product),
                0,
                null,
                (string)$product->getData('description'),
                (string)$product->getData('short_description'),
                $images,
                $this->getProductStatus($product),
                $this->getSalesPrice($product)
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
            $this->getProductStatus($product),
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

    private function getProductQuantity(int $productId): int
    {
        $product = $this->productReadModel->getProduct(new GetProduct($productId));
        $extensionAttributes = $product->getExtensionAttributes();

        if (null === $extensionAttributes || !method_exists($extensionAttributes, 'getStockItem')) {
            return 0;
        }

        return (int) $extensionAttributes->getStockItem()->getQty();
    }

    private function getProductStatus(MagentoProduct $product): string
    {
        $isStatusActive = (int) $product->getStatus() === self::PRODUCT_STATUS_ACTIVE;
        $isVisible = (int) $product->getVisibility() !== self::PRODUCT_INVISIBLE;

        return $isStatusActive && $isVisible ? Product::STATUS_PUBLISH : Product::STATUS_DRAFT;
    }

    private function getProductVariantStatus(MagentoProduct $product): string
    {
        return (int) $product->getStatus() === self::PRODUCT_STATUS_ACTIVE ? Product::STATUS_PUBLISH : Product::STATUS_DRAFT;
    }

    private function getSalesPrice(MagentoProduct $product): ?ProductSalePrice
    {
        $price = $product->getSpecialPrice();
        $fromDate = $product->getSpecialFromDate();
        $toDate = $product->getSpecialToDate();

        return null !== $price ? new ProductSalePrice((float)$price, $fromDate, $toDate) : null;
    }
}
