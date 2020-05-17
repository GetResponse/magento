<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMapping;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingCollection;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;

class SubscribeViaRegistrationService
{
    private $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function getSettings(Scope $scope): SubscribeViaRegistration
    {
        return SubscribeViaRegistrationFactory::createFromArray(
            $this->repository->getRegistrationSettings($scope->getScopeId())
        );
    }

    public function saveCustomFieldsMapping(
        CustomFieldsMappingCollection $customFieldsMappingCollection,
        Scope $scope
    ) {
        $finalCustomFieldMappingCollection = CustomFieldsMappingCollection::createDefaults();

        /** @var CustomFieldsMapping $customFieldMapping */
        foreach ($customFieldsMappingCollection as $customFieldMapping) {
            $finalCustomFieldMappingCollection->add($customFieldMapping);
        }

        $this->repository->updateCustoms(
            $finalCustomFieldMappingCollection,
            $scope->getScopeId()
        );
    }

    public function getCustomFieldMappingSettings(Scope $scope): CustomFieldsMappingCollection
    {
        return CustomFieldsMappingCollection::createFromRepository(
            $this->repository->getCustomFieldsMappingForRegistration($scope->getScopeId())
        );
    }

    public function saveSettings(SubscribeViaRegistration $registrationSettings, Scope $scope)
    {
        $this->repository->saveRegistrationSettings($registrationSettings, $scope->getScopeId());
    }
}
