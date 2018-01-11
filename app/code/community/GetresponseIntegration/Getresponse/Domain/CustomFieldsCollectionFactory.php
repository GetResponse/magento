<?php
use GetresponseIntegration_Getresponse_Domain_CustomFieldsCollection as CustomFieldsCollectionCollection;
use GetresponseIntegration_Getresponse_Domain_CustomFieldFactory as CustomFieldFactory;

class GetresponseIntegration_Getresponse_Domain_CustomFieldsCollectionFactory
{
    /**
     * @param $data
     * @return GetresponseIntegration_Getresponse_Domain_CustomFieldsCollection
     */
    public static function createFromArray($data)
    {
        $customFields = new CustomFieldsCollectionCollection;

        foreach ($data as $customField) {
            $customFields->add(CustomFieldFactory::createFromArray(array(
                $customField['id'],
                $customField['customField'],
                $customField['customValue'],
                $customField['isDefault'],
                $customField['isActive']
            )));
        }

        return $customFields;
    }
}