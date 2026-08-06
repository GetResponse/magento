<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Repository;

use GetResponse\GetResponseIntegration\Api\ProductPrice;
use GetResponse\GetResponseIntegration\Api\ProductPriceRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Customer\Model\Group as CustomerGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class ProductPriceRepository implements ProductPriceRepositoryInterface
{
    /** @var ProductCollectionFactory */
    private $productCollectionFactory;
    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * @param ProductCollectionFactory $productCollectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Get product prices.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \GetResponse\GetResponseIntegration\Api\ProductPrice[]
     */
    public function getList(SearchCriteriaInterface $searchCriteria): array
    {
        $storeId = null;
        $productIds = [];

        foreach ($searchCriteria->getFilterGroups() as $group) {
            foreach ($group->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $storeId = (int)$filter->getValue();
                }
                if ($filter->getField() === 'entity_id') {
                    $conditionType = $filter->getConditionType() ?: 'eq';
                    if ($conditionType === 'in') {
                        $productIds = is_array($filter->getValue())
                            ? array_map('intval', $filter->getValue())
                            : array_map('intval', array_filter(explode(',', (string)$filter->getValue()), 'strlen'));
                    } else {
                        $productIds = [(int)$filter->getValue()];
                    }
                }
            }
        }

        if ($storeId === null) {
            throw new InputException(__('store_id filter is required.'));
        }

        try {
            $store = $this->storeManager->getStore($storeId);
            $websiteId = (int)$store->getWebsiteId();
        } catch (NoSuchEntityException $e) {
            throw new InputException(__('The store with the specified ID %1 does not exist.', $storeId));
        }

        $pageSize = $searchCriteria->getPageSize() ?: 20;
        $currentPage = $searchCriteria->getCurrentPage() ?: 1;

        $pageSize = ($pageSize > 0) ? $pageSize : 20;
        $currentPage = ($currentPage > 0) ? $currentPage : 1;

        $collection = $this->productCollectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addStoreFilter($storeId);

        $collection->addPriceData(CustomerGroup::NOT_LOGGED_IN_ID, $websiteId);
        $collection->addAttributeToSelect('entity_id');

        if (!empty($productIds)) {
            $collection->addFieldToFilter('entity_id', ['in' => $productIds]);
        }

        $collection->setCurPage($currentPage);
        $collection->setPageSize($pageSize);

        $items = [];
        foreach ($collection as $product) {
            $productId = (int)$product->getId();

            $regularPrice = $product->getData('price') !== null ? (float)$product->getData('price') : 0.0;
            $finalPrice = $product->getData('final_price') !== null ? (float)$product->getData('final_price') : 0.0;
            $isDiscounted = $finalPrice < $regularPrice;

            $items[] = new ProductPrice(
                $productId,
                $regularPrice,
                $finalPrice,
                $isDiscounted
            );
        }

        return $items;
    }
}
