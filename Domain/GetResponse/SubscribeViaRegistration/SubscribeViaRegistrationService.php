<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMapping;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingCollection;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;

/**
 * Class SubscribeViaRegistrationService
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration
 */
class SubscribeViaRegistrationService
{
    /** @var Repository */
    private $repository;

    /**
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return SubscribeViaRegistration
     */
    public function getSettings()
    {
        return SubscribeViaRegistrationFactory::createFromArray(
            $this->repository->getRegistrationSettings()
        );
    }

    /**
     * @param CustomFieldsMappingCollection $customFieldsMappingCollection
     */
    public function saveCustomFieldsMapping(CustomFieldsMappingCollection $customFieldsMappingCollection)
    {
        $finalCustomFieldMappingCollection = CustomFieldsMappingCollection::createDefaults();

        /** @var CustomFieldsMapping $customFieldMapping */
        foreach ($customFieldsMappingCollection as $customFieldMapping) {
            $finalCustomFieldMappingCollection->add($customFieldMapping);
        }

        $this->repository->updateCustoms($finalCustomFieldMappingCollection);
    }

    /**
     * @return CustomFieldsMappingCollection
     */
    public function getCustomFieldMappingSettings()
    {
        return CustomFieldsMappingCollection::createFromRepository(
            $this->repository->getCustomFieldsMappingForRegistration()
        );
    }

    /**
     * @param SubscribeViaRegistration $registrationSettings
     */
    public function saveSettings(SubscribeViaRegistration $registrationSettings)
    {
        $this->repository->saveRegistrationSettings($registrationSettings);
    }

}