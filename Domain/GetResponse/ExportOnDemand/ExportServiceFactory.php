<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\Config;
use GetResponse\GetResponseIntegration\Domain\Magento\ShareCodeRepository;
use GrShareCode\Export\ExportContactService;
use GrShareCode\Export\ExportContactServiceFactory;

/**
 * Class ExportServiceFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand
 */
class ExportServiceFactory
{
    /** @var ShareCodeRepository */
    private $shareCodeRepository;

    /** @var ApiClientFactory */
    private $apiClientFactory;

    /** @var ExportContactServiceFactory */
    private $exportContactServiceFactory;

    /**
     * @param ApiClientFactory $apiClientFactory
     * @param ShareCodeRepository $shareCodeRepository
     * @param ExportContactServiceFactory $exportContactServiceFactory
     */
    public function __construct(
        ApiClientFactory $apiClientFactory,
        ShareCodeRepository $shareCodeRepository,
        ExportContactServiceFactory $exportContactServiceFactory
    ) {
        $this->apiClientFactory = $apiClientFactory;
        $this->shareCodeRepository = $shareCodeRepository;
        $this->exportContactServiceFactory = $exportContactServiceFactory;
    }

    /**
     * @return ExportContactService
     * @throws ApiException
     */
    public function create()
    {
        return $this->exportContactServiceFactory->create(
            $this->apiClientFactory->createGetResponseApiClient(),
            $this->shareCodeRepository,
            Config::ORIGIN_NAME
        );
    }
}