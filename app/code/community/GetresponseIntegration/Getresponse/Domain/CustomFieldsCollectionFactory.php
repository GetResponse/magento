<?php
use GetresponseIntegration_Getresponse_Domain_CustomFieldsCollection as CustomFieldsCollectionCollection;
use GetresponseIntegration_Getresponse_Domain_CustomField as CustomField;

class GetresponseIntegration_Getresponse_Domain_CustomFieldsCollectionFactory
{
    const ACTIVE = 1;
    const INACTIVE = 0;

    const RESERVED_CUSTOM_FIELDS = array('firstname', 'lastname', 'email');

    const DEFAULT_FIELDS = array(
        array('name' => 'firstname', 'value' => self::ACTIVE),
        array('name' => 'lastname', 'value' => self::ACTIVE),
        array('name' => 'email', 'value' => self::ACTIVE),
        array('name' => 'street', 'value' => self::INACTIVE),
        array('name' => 'postcode', 'value' => self::INACTIVE),
        array('name' => 'city', 'value' => self::INACTIVE),
        array('name' => 'telephone', 'value' => self::INACTIVE),
        array('name' => 'country', 'value' => self::INACTIVE),
        array('name' => 'birthday', 'value' => self::INACTIVE),
        array('name' => 'company', 'value' => self::INACTIVE),
    );

    public static function createFromArray($data)
    {
        $customFields = new CustomFieldsCollectionCollection;

        foreach ($data as $customField) {
            $customFields->add(new CustomField(
                $customField['id'],
                $customField['customField'],
                $customField['customValue'],
                $customField['default'],
                $customField['active']
            ));
        }

        return $customFields;
    }
}