<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use Magento\Framework\Exception\InputException;

interface ProductAvailabilityRepositoryInterface
{
    public const MAX_PAGE_SIZE = 250;

    /**
     * Get product availability (salability) for a given store.
     *
     * The salability list reports whether the product can be bought via each product page:
     * - one entry for each configurable parent assigned to the store, with via_product_id equal to the parent's ID;
     * - an entry for its own page, with via_product_id equal to its own product_id, only when visible individually.
     *
     * The product must be enabled in the store scope and in stock to be salable. For a parent entry,
     * the parent must also be visible individually, enabled and in stock. Salability on the product's own page
     * does not depend on its parents. Entries that do not meet these conditions have is_salable set to false.
     *
     * A product not visible individually with no configurable parent in the store has an empty salability list.
     * Configurable, grouped and bundle products are not returned; their children and standalone
     * products are.
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
    ): array;
}
