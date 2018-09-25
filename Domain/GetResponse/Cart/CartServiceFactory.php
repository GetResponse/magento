<?php

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Cart;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiTypeFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\GetresponseApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\ProductServiceFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\RepositoryForSharedCode;
use GrShareCode\Api\ApiTypeException;
use GrShareCode\Cart\CartService as GrCartService;

/**
 * Class CartServiceFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Cart
 */
class CartServiceFactory
{
    /** @var Repository */
    private $magentoRepository;

    /** @var RepositoryForSharedCode */
    private $sharedCodeRepository;

    /**
     * @param Repository $magentoRepository
     * @param RepositoryForSharedCode $sharedCodeRepository
     */
    public function __construct(Repository $magentoRepository, RepositoryForSharedCode $sharedCodeRepository)
    {
        $this->magentoRepository = $magentoRepository;
        $this->sharedCodeRepository = $sharedCodeRepository;
    }

    /**
     * @return GrCartService
     * @throws ApiTypeException
     */
    public function create()
    {
        $settings = ConnectionSettingsFactory::createFromArray($this->magentoRepository->getConnectionSettings());
        $getResponseApiClient = GetresponseApiClientFactory::createFromParams(
            $settings->getApiKey(),
            ApiTypeFactory::createFromConnectionSettings($settings),
            $settings->getDomain(),
            $this->sharedCodeRepository,
            $this->magentoRepository->getGetResponsePluginVersion()
        );

        $productService = new ProductServiceFactory($getResponseApiClient, $this->sharedCodeRepository);
        return new GrCartService($getResponseApiClient, $this->sharedCodeRepository, $productService->create());
    }
}
