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

    /**
     * @param Repository $magentoRepository
     * @param ShareCodeRepository $sharedCodeRepository
     */
    public function __construct(Repository $magentoRepository, ShareCodeRepository $sharedCodeRepository)
    {
        $this->magentoRepository = $magentoRepository;
        $this->sharedCodeRepository = $sharedCodeRepository;
    }

    /**
     * @return GrOrderService
     * @throws ApiTypeException
     * @throws ConnectionSettingsException
     */
    public function create()
    {
        $settings = ConnectionSettingsFactory::createFromArray($this->magentoRepository->getConnectionSettings());
        $getResponseApi = GetresponseApiClientFactory::createFromParams(
            $settings->getApiKey(),
            ApiTypeFactory::createFromConnectionSettings($settings),
            $settings->getDomain(),
            $this->sharedCodeRepository,
            $this->magentoRepository->getGetResponsePluginVersion()
        );

        $productService = new ProductServiceFactory($getResponseApi, $this->sharedCodeRepository);
        return new GrOrderService($getResponseApi, $this->sharedCodeRepository, $productService->create());
    }

}