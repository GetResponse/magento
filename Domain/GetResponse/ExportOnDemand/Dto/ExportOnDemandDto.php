<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand\Dto;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto\CustomFieldMappingDtoCollection;

class ExportOnDemandDto
{
    private $contactListId;
    private $autoresponderEnabled;
    private $dayOfCycle;
    private $ecommerceEnabled;
    private $shopId;
    private $updateContactCustomFieldEnabled;
    private $customFieldMappingDtoCollection;

    public function __construct(
        string $contactListId,
        bool $autoresponderEnabled,
        $dayOfCycle,
        bool $ecommerceEnabled,
        string $shopId,
        bool $updateContactCustomFieldEnabled,
        CustomFieldMappingDtoCollection $customFieldMappingDtoCollection
    ) {
        $this->contactListId = $contactListId;
        $this->autoresponderEnabled = $autoresponderEnabled;
        $this->dayOfCycle = $dayOfCycle;
        $this->ecommerceEnabled = $ecommerceEnabled;
        $this->shopId = $shopId;
        $this->updateContactCustomFieldEnabled = $updateContactCustomFieldEnabled;
        $this->customFieldMappingDtoCollection = $customFieldMappingDtoCollection;
    }

    public function getContactListId(): string
    {
        return $this->contactListId;
    }

    public function isAutoresponderEnabled(): bool
    {
        return $this->autoresponderEnabled;
    }

    public function getDayOfCycle()
    {
        return $this->dayOfCycle;
    }

    public function isEcommerceEnabled(): bool
    {
        return $this->ecommerceEnabled;
    }

    public function getShopId(): string
    {
        return $this->shopId;
    }

    public function isUpdateContactCustomFieldEnabled(): bool
    {
        return $this->updateContactCustomFieldEnabled;
    }

    public function getCustomFieldMappingDtoCollection(): CustomFieldMappingDtoCollection
    {
        return $this->customFieldMappingDtoCollection;
    }
}
