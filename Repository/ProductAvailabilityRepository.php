<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Repository;

use GetResponse\GetResponseIntegration\Api\ProductAvailability;
use GetResponse\GetResponseIntegration\Api\ProductAvailabilityEntry;
use GetResponse\GetResponseIntegration\Api\ProductAvailabilityRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Relation as ProductRelation;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Returns whether products are salable in a given store.
 */
class ProductAvailabilityRepository implements ProductAvailabilityRepositoryInterface
{
    private const CONFIGURABLE_PRODUCT_TYPE = 'configurable';
    private const COMPLEX_PRODUCT_TYPES = [self::CONFIGURABLE_PRODUCT_TYPE, 'grouped', 'bundle'];

    /** @var ProductCollectionFactory */
    private $productCollectionFactory;
    /** @var StoreManagerInterface */
    private $storeManager;
    /** @var StockRegistryInterface */
    private $stockRegistry;
    /** @var ProductRelation */
    private $productRelation;

    /**
     * @param ProductCollectionFactory $productCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param StockRegistryInterface $stockRegistry
     * @param ProductRelation $productRelation
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        StoreManagerInterface $storeManager,
        StockRegistryInterface $stockRegistry,
        ProductRelation $productRelation
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->storeManager = $storeManager;
        $this->stockRegistry = $stockRegistry;
        $this->productRelation = $productRelation;
    }

    /**
     * Get product availability.
     *
     * @param int $storeId
     * @param int[] $productIds
     * @param int $pageSize
     * @param int $currentPage
     * @return \GetResponse\GetResponseIntegration\Api\ProductAvailability[]
     * @throws InputException
     */
    public function getList(
        int $storeId,
        array $productIds = [],
        int $pageSize = self::MAX_PAGE_SIZE,
        int $currentPage = 1
    ): array {
        try {
            $store = $this->storeManager->getStore($storeId);
            $websiteId = (int)$store->getWebsiteId();
        } catch (NoSuchEntityException $e) {
            throw new InputException(__('The store with the specified ID %1 does not exist.', $storeId));
        }

        $productIds = array_unique($productIds);

        $pageSize = ($pageSize > 0) ? min($pageSize, self::MAX_PAGE_SIZE) : self::MAX_PAGE_SIZE;
        $currentPage = ($currentPage > 0) ? $currentPage : 1;

        $collection = $this->productCollectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addStoreFilter($storeId);
        $collection->addAttributeToFilter('type_id', ['nin' => self::COMPLEX_PRODUCT_TYPES]);
        $collection->addAttributeToSelect(['status', 'visibility']);
        $collection->setOrder('entity_id', 'ASC');
        $collection->setCurPage($currentPage);
        $collection->setPageSize($pageSize);

        if (!empty($productIds)) {
            $collection->addFieldToFilter('entity_id', ['in' => $productIds]);
        }

        /** @var \Magento\Catalog\Model\Product[] $products */
        $products = iterator_to_array($collection, false);

        $parentIdsByChildId = $this->findParentIdsForProducts($products);
        $isParentSalable = [];
        foreach ($this->findConfigurableParents($parentIdsByChildId, $storeId) as $parentId => $parent) {
            $isParentSalable[$parentId] = $parent->isVisibleInSiteVisibility()
                && $this->isProductEnabledAndInStock($parent, $websiteId);
        }

        $items = [];
        foreach ($products as $product) {
            $productId = (int)$product->getId();
            $isSalable = $this->isProductEnabledAndInStock($product, $websiteId);

            $salability = [];
            foreach ($parentIdsByChildId[$productId] ?? [] as $parentId) {
                $parentId = (int)$parentId;
                if (isset($isParentSalable[$parentId])) {
                    $salability[] = new ProductAvailabilityEntry(
                        $parentId,
                        $isSalable && $isParentSalable[$parentId]
                    );
                }
            }

            if ($product->isVisibleInSiteVisibility()) {
                array_unshift($salability, new ProductAvailabilityEntry($productId, $isSalable));
            }

            $items[] = new ProductAvailability($productId, $salability);
        }

        return $items;
    }

    /**
     * Find IDs of all parents of the given products.
     *
     * @param \Magento\Catalog\Model\Product[] $products
     * @return int[][] Parent IDs indexed by product ID.
     */
    private function findParentIdsForProducts(array $products): array
    {
        if (empty($products)) {
            return [];
        }

        $productIds = [];
        foreach ($products as $product) {
            $productIds[] = (int)$product->getId();
        }

        return $this->productRelation->getRelationsByChildren($productIds);
    }

    /**
     * Find configurable parents assigned to the store, indexed by ID.
     *
     * @param int[][] $parentIdsByChildId
     * @param int $storeId
     * @return \Magento\Catalog\Model\Product[]
     */
    private function findConfigurableParents(array $parentIdsByChildId, int $storeId): array
    {
        if (empty($parentIdsByChildId)) {
            return [];
        }

        $collection = $this->productCollectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addStoreFilter($storeId);
        $collection->addAttributeToFilter('type_id', self::CONFIGURABLE_PRODUCT_TYPE);
        $collection->addAttributeToSelect(['status', 'visibility']);
        $parentIds = array_unique(array_merge(...$parentIdsByChildId));
        $collection->addFieldToFilter('entity_id', ['in' => $parentIds]);

        $parents = [];
        foreach ($collection as $parent) {
            $parents[(int)$parent->getId()] = $parent;
        }

        return $parents;
    }

    /**
     * Check whether the product is enabled and in stock.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param int $websiteId
     * @return bool
     */
    private function isProductEnabledAndInStock($product, int $websiteId): bool
    {
        return $this->isProductEnabled($product) && $this->isProductInStock((int)$product->getId(), $websiteId);
    }

    /**
     * Check whether the product is enabled in the scope.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    private function isProductEnabled($product): bool
    {
        return (int)$product->getStatus() === ProductStatus::STATUS_ENABLED;
    }

    /**
     * Check whether the product is in stock for the given website.
     *
     * @param int $productId
     * @param int $websiteId
     * @return bool
     */
    private function isProductInStock(int $productId, int $websiteId): bool
    {
        $stockStatus = (int)$this->stockRegistry->getProductStockStatus($productId, $websiteId);

        return $stockStatus === StockStatusInterface::STATUS_IN_STOCK;
    }
}
