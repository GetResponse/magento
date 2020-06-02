<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomField;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GrShareCode\CustomField\CustomFieldService as GrCustomFieldService;

class CustomFieldServiceFactory
{
    private $apiClientFactory;

    public function __construct(ApiClientFactory $apiClientFactory)
    {
        $this->apiClientFactory = $apiClientFactory;
    }

    /**
     * @param Scope $scope
     * @return GrCustomFieldService
     * @throws ApiException
     */
    public function create(Scope $scope): GrCustomFieldService
    {
        return new GrCustomFieldService(
            $this->apiClientFactory->createGetResponseApiClient($scope)
        );
    }

}