<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Contact;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\Config;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiTypeFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GrShareCode\Api\ApiTypeException;
use GrShareCode\Api\UserAgentHeader;
use GrShareCode\Contact\ContactService as GrContactService;
use GrShareCode\GetresponseApi;

/**
 * Class ContactServiceFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Contact
 */
class ContactServiceFactory
{
    /** @var Repository */
    private $magentoRepository;

    /**
     * @param Repository $magentoRepository
     */
    public function __construct(Repository $magentoRepository)
    {
        $this->magentoRepository = $magentoRepository;
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

        return new GrContactService($getResponseApi);

    }
}