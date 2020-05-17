<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Cart;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\Magento\ShareCodeCache;
use GetResponse\GetResponseIntegration\Domain\Magento\ShareCodeRepository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GrShareCode\Cart\CartService as GrCartService;
use GrShareCode\Cart\CartServiceFactory as GrCartServiceFactory;

class CartServiceFactory
{
    private $shareCodeRepository;
    private $shareCodeCache;
    private $apiClientFactory;
    private $cartServiceFactory;

    public function __construct(
        ShareCodeRepository $shareCodeRepository,
        ShareCodeCache $shareCodeCache,
        ApiClientFactory $apiClientFactory,
        GrCartServiceFactory $cartServiceFactory
    ) {
        $this->shareCodeRepository = $shareCodeRepository;
        $this->shareCodeCache = $shareCodeCache;
        $this->apiClientFactory = $apiClientFactory;
        $this->cartServiceFactory = $cartServiceFactory;
    }

    /**
     * @param Scope $scope
     * @return GrCartService
     * @throws ApiException
     */
    public function create(Scope $scope): GrCartService
    {
        $getResponseApiClient = $this->apiClientFactory->createGetResponseApiClient($scope);

        return $this->cartServiceFactory->create(
            $getResponseApiClient,
            $this->shareCodeRepository,
            $this->shareCodeCache
        );
    }
}
