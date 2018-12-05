<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Contact;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\Config;
use GetResponse\GetResponseIntegration\Domain\Magento\ShareCodeRepository;
use GrShareCode\Contact\ContactService as GrContactService;
use GrShareCode\Contact\ContactServiceFactory as GrContactServiceFactory;

/**
 * Class ContactServiceFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Contact
 */
class ContactServiceFactory
{
    /** @var ShareCodeRepository */
    private $shareCodeRepository;

    /** @var ApiClientFactory */
    private $apiClientFactory;

    /** @var GrContactServiceFactory */
    private $grContactServiceFactory;

    /**
     * @param ShareCodeRepository $shareCodeRepository
     * @param ApiClientFactory $apiClientFactory
     * @param GrContactServiceFactory $grContactServiceFactory
     */
    public function __construct(
        ShareCodeRepository $shareCodeRepository,
        ApiClientFactory $apiClientFactory,
        GrContactServiceFactory $grContactServiceFactory
    ) {
        $this->shareCodeRepository = $shareCodeRepository;
        $this->apiClientFactory = $apiClientFactory;
        $this->grContactServiceFactory = $grContactServiceFactory;
    }

    /**
     * @return GrContactService
     * @throws ApiException
     */
    public function create()
    {
        $getResponseApi = $this->apiClientFactory->createGetResponseApiClient();

        return $this->grContactServiceFactory->create(
            $getResponseApi,
            $this->shareCodeRepository,
            Config::ORIGIN_NAME
        );
    }
}