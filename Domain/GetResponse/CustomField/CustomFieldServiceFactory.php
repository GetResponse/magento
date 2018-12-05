<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomField;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GrShareCode\CustomField\CustomFieldService as GrCustomFieldService;

/**
 * Class CustomFieldServiceFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Contact
 */
class CustomFieldServiceFactory
{
    /** @var ApiClientFactory */
    private $apiClientFactory;

    /**
     * @param ApiClientFactory $apiClientFactory
     */
    public function __construct(ApiClientFactory $apiClientFactory)
    {
        $this->apiClientFactory = $apiClientFactory;
    }

    /**
     * @return GrCustomFieldService
     * @throws ApiException
     */
    public function create()
    {
        return new GrCustomFieldService(
            $this->apiClientFactory->createGetResponseApiClient()
        );
    }

}