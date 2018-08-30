<?php

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Contact;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\Config;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiTypeFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\RepositoryForSharedCode;
use GrShareCode\Api\ApiTypeException;
use GrShareCode\Api\UserAgentHeader;
use GrShareCode\Contact\ContactService as GrContactService;
use GrShareCode\GetresponseApi;
use GrShareCode\GetresponseApiClient;

/**
 * Class ContactServiceFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Contact
 */
class ContactServiceFactory
{
    /** @var Repository */
    private $magentoRepository;

    /** @var RepositoryForSharedCode */
    private $shareCodeRepository;

    /**
     * @param Repository $magentoRepository
     */
    public function __construct(Repository $magentoRepository, RepositoryForSharedCode $shareCodeRepository)
    {
        $this->magentoRepository = $magentoRepository;
        $this->shareCodeRepository = $shareCodeRepository;
    }

    /**
     * @return GrContactService
     * @throws ApiTypeException
     */
    public function create()
    {
        $connectionSettings = ConnectionSettingsFactory::createFromArray(
            $this->magentoRepository->getConnectionSettings()
        );

        $pluginVersion = $this->magentoRepository->getGetResponsePluginVersion();

        $apiType = ApiTypeFactory::createFromConnectionSettings($connectionSettings);

        $getResponseApi = new GetresponseApiClient(
            new GetresponseApi(
                $connectionSettings->getApiKey(),
                $apiType,
                Config::X_APP_ID,
                new UserAgentHeader(
                    Config::SERVICE_NAME,
                    Config::SERVICE_VERSION,
                    $pluginVersion
                )
            ), $this->shareCodeRepository
        );

        return new GrContactService($getResponseApi);

    }
}