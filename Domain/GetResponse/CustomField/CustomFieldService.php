<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomField;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\CustomField\CustomFieldCollection;

class CustomFieldService
{
    private $customFieldServiceFactory;

    public function __construct(CustomFieldServiceFactory $customFieldServiceFactory)
    {
        $this->customFieldServiceFactory = $customFieldServiceFactory;
    }

    /**
     * @param Scope $scope
     * @return CustomFieldCollection
     * @throws ApiException
     * @throws GetresponseApiException
     */
    public function getCustomFields(Scope $scope): CustomFieldCollection
    {
        $grCustomFieldService = $this->customFieldServiceFactory->create($scope);

        return $grCustomFieldService->getCustomFieldsForMapping();
    }
}
