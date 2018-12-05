<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto\CustomFieldMappingDto;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto\CustomFieldMappingDtoCollection;
use GetResponse\GetResponseIntegration\Helper\Message;

/**
 * Class CustomFieldsMappingValidator
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping
 */
class CustomFieldsMappingValidator
{
    /** @var string */
    private $errorMessage;

    /**
     * @param CustomFieldMappingDtoCollection $customFieldMappingDtoCollection
     * @return bool
     */
    public function isValid(CustomFieldMappingDtoCollection $customFieldMappingDtoCollection)
    {
        $getResponseCustomFields = [];

        /** @var CustomFieldMappingDto $customFieldMappingDto */
        foreach ($customFieldMappingDtoCollection as $customFieldMappingDto) {

            if (empty($customFieldMappingDto->getGetResponseCustomFieldId())) {
                $this->errorMessage = Message::CUSTOM_FIELDS_MAPPING_VALIDATION_GETRESPONSE_CUSTOM_FIELD_EMPTY;

                return false;
            }

            if (empty($customFieldMappingDto->getMagentoAttributeCode())) {
                $this->errorMessage = Message::CUSTOM_FIELDS_MAPPING_VALIDATION_MAGENTO_CUSTOM_DETAILS_EMPTY;

                return false;
            }

            if (in_array($customFieldMappingDto->getGetResponseCustomFieldId(), $getResponseCustomFields, true)) {
                $this->errorMessage = Message::CUSTOM_FIELDS_MAPPING_VALIDATION_GETRESPONSE_CUSTOM_FIELD_DUPLICATED;

                return false;
            }

            $getResponseCustomFields[] = $customFieldMappingDto->getGetResponseCustomFieldId();
        }

        return true;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

}