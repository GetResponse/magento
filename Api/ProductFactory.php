<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use GetResponse\GetResponseIntegration\Domain\Magento\Product\ReadModel\ProductReadModel;
use GetResponse\GetResponseIntegration\Domain\Magento\Product\ReadModel\Query\GetProduct;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Logger\Logger;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\CatalogRule\Model\ResourceModel\Rule as CatalogRuleResource;
use Magento\Customer\Model\Group as CustomerGroup;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

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
    /** @var CatalogRuleResource */
    private $catalogRuleResource;
    /** @var StoreManagerInterface */
    private $storeManager;
    /** @var TimezoneInterface */
    private $timezone;
    /** @var Logger */
    private $logger;

    /**
     * @param CategoryRepository $categoryRepository
     * @param ProductReadModel $productReadModel
     * @param ProductType $productType
     * @param CatalogRuleResource $catalogRuleResource
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $timezone
     * @param Logger $logger
     */
    public function __construct(
        CategoryRepository $categoryRepository,
        ProductReadModel $productReadModel,
        ProductType $productType,
        CatalogRuleResource $catalogRuleResource,
        StoreManagerInterface $storeManager,
        TimezoneInterface $timezone,
        Logger $logger
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->productReadModel = $productReadModel;
        $this->productType = $productType;
        $this->catalogRuleResource = $catalogRuleResource;
        $this->storeManager = $storeManager;
        $this->timezone = $timezone;
        $this->logger = $logger;
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
                    $this->getSalesPrice($childProduct, (int)$scope->getScopeId())
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
                $this->getSalesPrice($product, (int)$scope->getScopeId())
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
     * Computes the store-accurate effective price, including catalog price rules
     * and special price. The catalog price rule is looked up directly via the
     * catalog rule resource model
     *
     * @param MagentoProduct $product
     * @param int $storeId
     */
    private function getSalesPrice(MagentoProduct $product, int $storeId): ?ProductSalePrice
    {
        $basePrice = (float) $product->getPrice();

        $candidates = [
            new SalePriceCandidate($basePrice, null, null),
        ];

        $specialPrice = $product->getSpecialPrice();
        $specialFromDate = $product->getSpecialFromDate();
        $specialToDate = $product->getSpecialToDate();
        if (null !== $specialPrice
            && $this->timezone->isScopeDateInInterval($storeId, $specialFromDate, $specialToDate)
        ) {
            $candidates[] = new SalePriceCandidate((float) $specialPrice, $specialFromDate, $specialToDate);
        }

        try {
            $websiteId = (int) $this->storeManager->getStore($storeId)->getWebsiteId();
            $rulePrice = $this->catalogRuleResource->getRulePrice(
                $this->timezone->scopeDate($storeId),
                $websiteId,
                CustomerGroup::NOT_LOGGED_IN_ID,
                (int) $product->getId()
            );

            if (false !== $rulePrice) {
                [$ruleFrom, $ruleTo] = $this->getCatalogRuleDates($product, $storeId, $websiteId);
                $candidates[] = new SalePriceCandidate((float) $rulePrice, $ruleFrom, $ruleTo);
            }
        } catch (\Throwable $e) {
            $this->logger->addError($e->getMessage(), ['exception' => $e]);
        }

        $winner = $this->pickLowestPriceCandidate($candidates);

        if ($winner->getPrice() + 0.00001 >= $basePrice) {
            return null;
        }

        return new ProductSalePrice($winner->getPrice(), $winner->getFrom(), $winner->getTo());
    }

    /**
     * Pick the candidate with the lowest price.
     *
     * @param SalePriceCandidate[] $candidates
     */
    private function pickLowestPriceCandidate(array $candidates): SalePriceCandidate
    {
        $winner = $candidates[0];

        foreach ($candidates as $candidate) {
            if ($candidate->getPrice() + 0.00001 < $winner->getPrice()) {
                $winner = $candidate;
                continue;
            }

            $isTie = abs($candidate->getPrice() - $winner->getPrice()) <= 0.00001;
            if ($isTie && $this->periodLength($candidate) < $this->periodLength($winner)) {
                $winner = $candidate;
            }
        }

        return $winner;
    }

    /**
     * Length of the candidate promotion period in seconds
     *
     * @param SalePriceCandidate $candidate
     */
    private function periodLength(SalePriceCandidate $candidate): int
    {
        if (null === $candidate->getTo()) {
            return PHP_INT_MAX;
        }

        $to = strtotime($candidate->getTo());
        if (false === $to) {
            return PHP_INT_MAX;
        }

        $from = null !== $candidate->getFrom() ? strtotime($candidate->getFrom()) : false;
        if (false === $from) {
            $from = 0;
        }

        return $to - $from;
    }

    /**
     * Get the active catalog price rule period for the product
     *
     * @param MagentoProduct $product
     * @param int $storeId
     * @param int $websiteId
     * @return array
     */
    private function getCatalogRuleDates(MagentoProduct $product, int $storeId, int $websiteId): array
    {
        try {
            $rules = $this->catalogRuleResource->getRulesFromProduct(
                $this->timezone->scopeDate($storeId),
                $websiteId,
                CustomerGroup::NOT_LOGGED_IN_ID,
                (int) $product->getId()
            );
        } catch (\Throwable $e) {
            $this->logger->addError($e->getMessage(), ['exception' => $e]);
            $now = $this->timezone->scopeDate($storeId);
            $to = date('Y-m-d H:i:s', strtotime($now) + 86400);

            return [$now, $to];
        }

        if (empty($rules)) {
            return [null, null];
        }

        $fromTime = null;
        $toTime = null;
        foreach ($rules as $rule) {
            $ruleFrom = isset($rule['from_time']) ? (int) $rule['from_time'] : 0;
            $ruleTo = isset($rule['to_time']) ? (int) $rule['to_time'] : 0;

            if ($ruleFrom > 0 && (null === $fromTime || $ruleFrom > $fromTime)) {
                $fromTime = $ruleFrom;
            }

            if ($ruleTo > 0 && (null === $toTime || $ruleTo < $toTime)) {
                $toTime = $ruleTo;
            }
        }

        return [
            null !== $fromTime ? date('Y-m-d H:i:s', $fromTime) : null,
            null !== $toTime ? date('Y-m-d H:i:s', $toTime) : null,
        ];
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
