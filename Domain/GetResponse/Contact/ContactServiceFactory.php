<?php

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Contact;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\Config;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiTypeFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\RepositoryForSharedCode;
use GrShareCode\Api\ApiKeyAuthorization;
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
     * @param RepositoryForSharedCode $shareCodeRepository
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

        $getResponseApi = new GetresponseApiClient(
            new GetresponseApi(
                new ApiKeyAuthorization(
                    $connectionSettings->getApiKey(),
                    ApiTypeFactory::createFromConnectionSettings($connectionSettings),
                    $connectionSettings->getDomain()
                ),
                Config::X_APP_ID,
                new UserAgentHeader(
                    Config::SERVICE_NAME,
                    Config::SERVICE_VERSION,
                    $this->magentoRepository->getGetResponsePluginVersion()
                )
            ), $this->shareCodeRepository
        );

        return new GrContactService($getResponseApi);
    }
}