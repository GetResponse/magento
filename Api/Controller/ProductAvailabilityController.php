<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api\Controller;

use GetResponse\GetResponseIntegration\Api\ProductAvailabilityRepositoryInterface;
use GetResponse\GetResponseIntegration\Controller\Api\ProductAvailabilityControllerInterface;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;

class ProductAvailabilityController extends ApiController implements ProductAvailabilityControllerInterface
{
    /** @var ProductAvailabilityRepositoryInterface */
    private $productAvailabilityRepository;

    /**
     * @param MagentoStore $magentoStore
     * @param ProductAvailabilityRepositoryInterface $productAvailabilityRepository
     */
    public function __construct(
        MagentoStore $magentoStore,
        ProductAvailabilityRepositoryInterface $productAvailabilityRepository
    ) {
        parent::__construct($magentoStore);
        $this->productAvailabilityRepository = $productAvailabilityRepository;
    }

    /**
     * @inheritdoc
     */
    public function getList(
        int $scope,
        array $productIds = [],
        int $pageSize = ProductAvailabilityRepositoryInterface::MAX_PAGE_SIZE,
        int $currentPage = 1
    ): array {
        $this->verifyScope($scope);

        return $this->productAvailabilityRepository->getList(
            $scope,
            $productIds,
            $pageSize,
            $currentPage
        );
    }
}
