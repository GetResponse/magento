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
    private const PRODUCT_STATUS_ACTIVE = 1;
    private const PRODUCT_INVISIBLE = 1;
    private const MAX_DESC_LENGTH = 10000;

    /** @var CategoryRepository */
    private $categoryRepository;
    /** @var ProductReadModel */
    private $productReadModel;
    /** @var ProductType */
    protected $productType;

    /**
     * @param CategoryRepository $categoryRepository
     * @param ProductReadModel $productReadModel
     * @param ProductType $productType
     */
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
     * Handle create.
     *
     * @param MagentoProduct $product
     * @param Scope $scope
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
     * Get parent products.
     *
     * @param MagentoProduct $product
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
     * Create from magento product.
     *
     * @param MagentoProduct $product
     * @param Scope $scope
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
                    $this->reduceDescription(
                        (string)$childProduct->getData('description'),
                        self::MAX_DESC_LENGTH
                    ),
                    $this->reduceDescription(
                        (string)$childProduct->getData('short_description'),
                        self::MAX_DESC_LENGTH
                    ),
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
                $this->reduceDescription((string)$product->getData('description'), self::MAX_DESC_LENGTH),
                $this->reduceDescription((string)$product->getData('short_description'), self::MAX_DESC_LENGTH),
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

    /**
     * Get product configurable url.
     *
     * @param MagentoProduct $parentProduct
     * @param MagentoProduct $simpleProduct
     * @param int $storeId
     */
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

    /**
     * Get images.
     *
     * @param MagentoProduct $product
     */
    private function getImages(MagentoProduct $product): array
    {
        $images = [];
        foreach ($product->getMediaGalleryImages() as $image) {
            $imagePosition = (int)$image->getData('position');
            $images[$imagePosition] = new Image($image->getData('url'), $imagePosition);
        }

        ksort($images);

        return empty($images) ? [] : [reset($images)];
    }

    /**
     * Get product quantity.
     *
     * @param int $productId
     */
    private function getProductQuantity(int $productId): int
    {
        $product = $this->productReadModel->getProduct(new GetProduct($productId));
        $extensionAttributes = $product->getExtensionAttributes();

        if (null === $extensionAttributes || !method_exists($extensionAttributes, 'getStockItem')) {
            return 0;
        }

        return (int) $extensionAttributes->getStockItem()->getQty();
    }

    /**
     * Get product status.
     *
     * @param MagentoProduct $product
     */
    private function getProductStatus(MagentoProduct $product): string
    {
        $isStatusActive = (int) $product->getStatus() === self::PRODUCT_STATUS_ACTIVE;
        $isVisible = (int) $product->getVisibility() !== self::PRODUCT_INVISIBLE;

        return $isStatusActive && $isVisible ? Product::STATUS_PUBLISH : Product::STATUS_DRAFT;
    }

    /**
     * Get product variant status.
     *
     * @param MagentoProduct $product
     */
    private function getProductVariantStatus(MagentoProduct $product): string
    {
        return (int) $product->getStatus() === self::PRODUCT_STATUS_ACTIVE
            ? Product::STATUS_PUBLISH
            : Product::STATUS_DRAFT;
    }

    /**
     * Get sales price.
     *
     * @param MagentoProduct $product
     */
    private function getSalesPrice(MagentoProduct $product): ?ProductSalePrice
    {
        $price = $product->getSpecialPrice();
        $fromDate = $product->getSpecialFromDate();
        $toDate = $product->getSpecialToDate();

        return null !== $price ? new ProductSalePrice((float)$price, $fromDate, $toDate) : null;
    }

    /**
     * Handle reduce description.
     *
     * @param string $description
     * @param int $maxLength
     */
    private function reduceDescription(string $description, int $maxLength): string
    {
        $cleanDescription = (string) preg_replace('#<style(.*?)>(.*?)</style>#is', '', $description);
        $cleanDescription = (string) preg_replace('#<script(.*?)>(.*?)</script>#is', '', $cleanDescription);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
        $cleanDescription = html_entity_decode($cleanDescription, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
        $cleanDescription = html_entity_decode($cleanDescription, ENT_COMPAT);
        $cleanDescription = strip_tags($cleanDescription);
        $cleanDescription = trim($cleanDescription);
        $cleanDescription = (string) preg_replace('/\s+/', ' ', $cleanDescription);

        if (mb_strlen($cleanDescription) <= $maxLength) {
            return $cleanDescription;
        }

        return mb_substr($cleanDescription, 0, $maxLength - 3) . '...';
    }
}
