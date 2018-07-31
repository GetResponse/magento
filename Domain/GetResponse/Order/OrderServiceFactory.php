<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Order;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiTypeFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\Config;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\ProductServiceFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\RepositoryForSharedCode;
use GrShareCode\Api\ApiTypeException;
use GrShareCode\Api\UserAgentHeader;
use GrShareCode\GetresponseApi;
use GrShareCode\Order\OrderService as GrOrderService;

/**
 * Class OrderServiceFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Order
 */
class OrderServiceFactory
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
     * @return GrOrderService
     * @throws ApiTypeException
     */
    public function create()
    {
        $connectionSettings = ConnectionSettingsFactory::createFromArray(
            $this->magentoRepository->getConnectionSettings()
        );

        $pluginVersion = $this->magentoRepository->getGetResponsePluginVersion();

        $apiType = ApiTypeFactory::createFromDomainUrl(
            $connectionSettings->getDomain()
        );

        $getResponseApi = new GetresponseApi(
            $connectionSettings->getApiKey(),
            $apiType,
            Config::X_APP_ID,
            new UserAgentHeader(
                Config::SERVICE_NAME,
                Config::SERVICE_VERSION,
                $pluginVersion
            )
        );

        $productService = new ProductServiceFactory(
            $getResponseApi,
            $this->sharedCodeRepository
        );

        return new GrOrderService(
            $getResponseApi,
            $this->sharedCodeRepository,
            $productService->create()
        );

    }

}