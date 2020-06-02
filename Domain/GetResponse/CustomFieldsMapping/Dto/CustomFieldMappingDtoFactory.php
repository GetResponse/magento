<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMapping;

class CustomFieldMappingDtoFactory
{
    /**
     * @param string $magentoAttributeCode
     * @param string $getResponseCustomFieldId
     * @return CustomFieldMappingDto
     * @throws InvalidPrefixException
     */
    public function createFromRequestData(
        $magentoAttributeCode,
        $getResponseCustomFieldId
    ): CustomFieldMappingDto {
        $this->assertValidPrefixAttributeCode($magentoAttributeCode);

        return new CustomFieldMappingDto(
            $this->getAttributeIdFromAttributeCode($magentoAttributeCode),
            $this->getAttributeTypeFromAttributeCode($magentoAttributeCode),
            $getResponseCustomFieldId
        );
    }

    /**
     * @param string $magentoAttributeCode
     * @throws InvalidPrefixException
     */
    private function assertValidPrefixAttributeCode($magentoAttributeCode)
    {
        if (empty($magentoAttributeCode)) {
            return;
        }

        $type = explode('_', $magentoAttributeCode)[0];

        $allowed_prefixes = [
            CustomFieldsMapping::TYPE_CUSTOMER,
            CustomFieldsMapping::TYPE_ADDRESS
        ];

        if (!in_array($type, $allowed_prefixes, true)) {
            throw InvalidPrefixException::createForInvalidPrefix($magentoAttributeCode);
        }
    }

    private function getAttributeIdFromAttributeCode(string $magentoAttributeCode): string
    {
        return trim(strstr($magentoAttributeCode, '_'), '_');
    }

    private function getAttributeTypeFromAttributeCode(string $magentoAttributeCode): string
    {
        return explode('_', $magentoAttributeCode)[0];
    }
}
