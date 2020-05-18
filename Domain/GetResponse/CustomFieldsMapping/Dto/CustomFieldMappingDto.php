<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto;

class CustomFieldMappingDto
{
    private $magentoAttributeCode;
    private $getResponseCustomFieldId;
    private $magentoAttributeType;

    public function __construct(
        string $magentoAttributeCode,
        string $magentoAttributeType,
        string $getResponseCustomFieldId
    ) {
        $this->magentoAttributeCode = $magentoAttributeCode;
        $this->magentoAttributeType = $magentoAttributeType;
        $this->getResponseCustomFieldId = $getResponseCustomFieldId;
    }

    public function getMagentoAttributeType(): string
    {
        return $this->magentoAttributeType;
    }

    public function getMagentoAttributeCode(): string
    {
        return $this->magentoAttributeCode;
    }

    public function getGetResponseCustomFieldId(): string
    {
        return $this->getResponseCustomFieldId;
    }

}