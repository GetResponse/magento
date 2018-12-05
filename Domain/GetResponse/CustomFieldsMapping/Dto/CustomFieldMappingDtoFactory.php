<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMapping;

/**
 * Class CustomFieldMappingDtoFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto
 */
class CustomFieldMappingDtoFactory
{
    /**
     * @param string $magentoAttributeCode
     * @param string $getResponseCustomFieldId
     * @return CustomFieldMappingDto
     * @throws InvalidPrefixException
     */
    public function createFromRequestData($magentoAttributeCode, $getResponseCustomFieldId)
    {
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

    /**
     * @param string $magentoAttributeCode
     * @return string
     */
    private function getAttributeIdFromAttributeCode($magentoAttributeCode)
    {
        return trim(strstr($magentoAttributeCode, '_'), '_');
    }

    /**
     * @param string $magentoAttributeCode
     * @return string
     */
    private function getAttributeTypeFromAttributeCode($magentoAttributeCode)
    {
        return explode('_', $magentoAttributeCode)[0];
    }
}