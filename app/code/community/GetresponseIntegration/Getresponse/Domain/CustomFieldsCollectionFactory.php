<?php
use GetresponseIntegration_Getresponse_Domain_CustomFieldsCollection as CustomFieldsCollectionCollection;
use GetresponseIntegration_Getresponse_Domain_CustomField as CustomField;

class GetresponseIntegration_Getresponse_Domain_CustomFieldsCollectionFactory
{
    public static function createFromArray($data)
    {
        $customFields = new CustomFieldsCollectionCollection;

        foreach ($data as $customField) {
            $customFields->add(new CustomField(
                $customField['id'],
                $customField['customField'],
                $customField['customValue'],
                $customField['isDefault'],
                $customField['isActive']
            ));
        }

        return $customFields;
    }
}