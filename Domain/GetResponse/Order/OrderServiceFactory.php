<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Order;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\Magento\ShareCodeRepository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GrShareCode\Order\OrderService as GrOrderService;
use GrShareCode\Order\OrderServiceFactory as GrOrderServiceFactory;

class OrderServiceFactory
{
    private $sharedCodeRepository;
    private $apiClientFactory;
    private $orderServiceFactory;

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
     * @param Scope $scope
     * @return GrOrderService
     * @throws ApiException
     */
    public function create(Scope $scope): GrOrderService
    {
        return $this->orderServiceFactory->create(
            $this->apiClientFactory->createGetResponseApiClient($scope),
            $this->sharedCodeRepository
        );
    }
}
