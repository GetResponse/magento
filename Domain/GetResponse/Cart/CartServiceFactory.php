<?php

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Cart;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiTypeFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\GetresponseApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\ProductServiceFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\ShareCodeCache;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsException;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\ShareCodeRepository;
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

    /** @var ShareCodeRepository */
    private $shareCodeRepository;

    /** @var ShareCodeCache */
    private $shareCodeCache;

    /** @var GetresponseApiClientFactory */
    private $apiClientFactory;

    /**
     * @param Repository $magentoRepository
     * @param ShareCodeRepository $shareCodeRepository
     * @param ShareCodeCache $shareCodeCache
     * @param GetresponseApiClientFactory $apiClientFactory
     */
    public function __construct(
        Repository $magentoRepository,
        ShareCodeRepository $shareCodeRepository,
        ShareCodeCache $shareCodeCache,
        GetresponseApiClientFactory $apiClientFactory
    ) {
        $this->magentoRepository = $magentoRepository;
        $this->shareCodeRepository = $shareCodeRepository;
        $this->shareCodeCache = $shareCodeCache;
        $this->apiClientFactory = $apiClientFactory;
    }

    /**
     * @return GrCartService
     * @throws ApiTypeException
     * @throws ConnectionSettingsException
     */
    public function create()
    {
        $settings = ConnectionSettingsFactory::createFromArray($this->magentoRepository->getConnectionSettings());
        $getResponseApiClient = $this->apiClientFactory->createFromParams(
            $settings->getApiKey(),
            ApiTypeFactory::createFromConnectionSettings($settings),
            $settings->getDomain()
        );

        $productService = new ProductServiceFactory($getResponseApiClient, $this->shareCodeRepository);
        return new GrCartService(
            $getResponseApiClient,
            $this->shareCodeRepository,
            $productService->create(),
            $this->shareCodeCache
        );
    }
}
