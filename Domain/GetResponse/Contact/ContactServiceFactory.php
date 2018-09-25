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
use GrShareCode\GetresponseApiClientFactory;

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
        $settings = ConnectionSettingsFactory::createFromArray($this->magentoRepository->getConnectionSettings());
        $getResponseApi = GetresponseApiClientFactory::createFromParams(
            $settings->getApiKey(),
            ApiTypeFactory::createFromConnectionSettings($settings),
            $settings->getDomain(),
            $this->shareCodeRepository,
            $this->magentoRepository->getGetResponsePluginVersion()
        );

        return new GrContactService($getResponseApi);
    }
}