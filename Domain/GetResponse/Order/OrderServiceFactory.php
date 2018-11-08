<?php

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Order;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiTypeFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\GetresponseApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\ProductServiceFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsException;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\ShareCodeRepository;
use GrShareCode\Api\ApiTypeException;
use GrShareCode\Order\OrderService as GrOrderService;

/**
 * Class OrderServiceFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Order
 */
class OrderServiceFactory
{
    /** @var Repository */
    private $magentoRepository;

    /** @var ShareCodeRepository */
    private $sharedCodeRepository;

    /** @var GetresponseApiClientFactory */
    private $apiClientFactory;

    /**
     * @param Repository $magentoRepository
     * @param ShareCodeRepository $sharedCodeRepository
     * @param GetresponseApiClientFactory $apiClientFactory
     */
    public function __construct(
        Repository $magentoRepository,
        ShareCodeRepository $sharedCodeRepository,
        GetresponseApiClientFactory $apiClientFactory
    ) {
        $this->magentoRepository = $magentoRepository;
        $this->sharedCodeRepository = $sharedCodeRepository;
        $this->apiClientFactory = $apiClientFactory;
    }

    /**
     * @return GrOrderService
     * @throws ApiTypeException
     * @throws ConnectionSettingsException
     */
    public function create()
    {
        $settings = ConnectionSettingsFactory::createFromArray($this->magentoRepository->getConnectionSettings());
        $getResponseApi = $this->apiClientFactory->createFromParams(
            $settings->getApiKey(),
            ApiTypeFactory::createFromConnectionSettings($settings),
            $settings->getDomain()
        );

        $productService = new ProductServiceFactory($getResponseApi, $this->sharedCodeRepository);
        return new GrOrderService($getResponseApi, $this->sharedCodeRepository, $productService->create());
    }
}
