<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto\CustomFieldMappingDtoCollection;
use GetResponse\GetResponseIntegration\Helper\Message;

class CustomFieldsMappingValidator
{
    private $errorMessage;

    public function isValid(
        CustomFieldMappingDtoCollection $customFieldMappingDtoCollection
    ): bool {
        $getResponseCustomFields = [];

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

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}
