<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand\Dto\ExportOnDemandDto;

class ExportOnDemand
{
    private $contactListId;
    private $dayOfCycle;
    private $shopId;
    private $customFieldsMappingCollection;

    public function __construct(
        string $contactListId,
        $dayOfCycle,
        $shopId,
        CustomFieldsMappingCollection $customFieldsMappingCollection
    ) {
        $this->contactListId = $contactListId;
        $this->dayOfCycle = $dayOfCycle;
        $this->shopId = $shopId;
        $this->customFieldsMappingCollection = $customFieldsMappingCollection;
    }

    public static function createFromDto(ExportOnDemandDto $exportOnDemandDto): ExportOnDemand
    {
        return new self(
            $exportOnDemandDto->getContactListId(),
            $exportOnDemandDto->getDayOfCycle(),
            $exportOnDemandDto->getShopId(),
            CustomFieldsMappingCollection::createFromDto($exportOnDemandDto->getCustomFieldMappingDtoCollection())
        );
    }

    public function getContactListId(): string
    {
        return $this->contactListId;
    }

    public function getDayOfCycle()
    {
        return $this->dayOfCycle;
    }

    public function isUpdateContactCustomFieldEnabled(): bool
    {
        return 0 !== count($this->getCustomFieldsMappingCollection()->toArray());
    }

    public function getCustomFieldsMappingCollection(): CustomFieldsMappingCollection
    {
        return $this->customFieldsMappingCollection;
    }

    public function isSendEcommerceDataEnabled(): bool
    {
        return null !== $this->getShopId();
    }

    public function getShopId()
    {
        return $this->shopId;
    }
}
