<?php

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Order;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\Magento\ShareCodeRepository;
use GrShareCode\Order\OrderService as GrOrderService;
use GrShareCode\Order\OrderServiceFactory as GrOrderServiceFactory;

/**
 * Class OrderServiceFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Order
 */
class OrderServiceFactory
{
    /** @var ShareCodeRepository */
    private $sharedCodeRepository;

    /** @var ApiClientFactory */
    private $apiClientFactory;

    /** @var GrOrderServiceFactory */
    private $orderServiceFactory;

    /**
     * @param ShareCodeRepository $sharedCodeRepository
     * @param ApiClientFactory $apiClientFactory
     * @param GrOrderServiceFactory $orderServiceFactory
     */
    public function __construct(
        ShareCodeRepository $sharedCodeRepository,
        ApiClientFactory $apiClientFactory,
        GrOrderServiceFactory $orderServiceFactory
    ) {
        $this->sharedCodeRepository = $sharedCodeRepository;
        $this->apiClientFactory = $apiClientFactory;
        $this->orderServiceFactory = $orderServiceFactory;
    }

    /**
     * @return GrOrderService
     * @throws ApiException
     */
    public function create()
    {
        return $this->orderServiceFactory->create(
            $this->apiClientFactory->createGetResponseApiClient(),
            $this->sharedCodeRepository
        );
    }
}
