<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api\Controller;

use GetResponse\GetResponseIntegration\Api\ProductAvailabilityRepositoryInterface;
use GetResponse\GetResponseIntegration\Controller\Api\ProductAvailabilityControllerInterface;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use Magento\Framework\Phrase;
use Magento\Framework\Webapi\Exception as WebapiException;

class ProductAvailabilityController extends ApiController implements ProductAvailabilityControllerInterface
{
    public const DISABLED_ERROR_CODE = 'PRODUCT_AVAILABILITY_DISABLED';
    private const HTTP_CONFLICT = 409;

    /** @var ProductAvailabilityRepositoryInterface */
    private $productAvailabilityRepository;
    /** @var Repository */
    private $configurationRepository;

    /**
     * @param MagentoStore $magentoStore
     * @param ProductAvailabilityRepositoryInterface $productAvailabilityRepository
     * @param Repository $configurationRepository
     */
    public function __construct(
        MagentoStore $magentoStore,
        ProductAvailabilityRepositoryInterface $productAvailabilityRepository,
        Repository $configurationRepository
    ) {
        parent::__construct($magentoStore);
        $this->productAvailabilityRepository = $productAvailabilityRepository;
        $this->configurationRepository = $configurationRepository;
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

        if (!$this->configurationRepository->getProductAvailability($scope)->isEnabled()) {
            throw new WebapiException(
                new Phrase('Product availability handling is disabled.'),
                self::DISABLED_ERROR_CODE,
                self::HTTP_CONFLICT
            );
        }

        return $this->productAvailabilityRepository->getList($scope, $productIds, $pageSize, $currentPage);
    }
}
