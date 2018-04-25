<?php
use GetresponseIntegration_Getresponse_Domain_CustomField as CustomField;

/**
 * Class GetresponseIntegration_Getresponse_Domain_CustomFieldFactory
 */
class GetresponseIntegration_Getresponse_Domain_CustomFieldFactory
{
    /**
     * @param $data
     *
     * @return GetresponseIntegration_Getresponse_Domain_CustomField
     */
    public static function createFromArray($data)
    {
        return new CustomField(
            $data['id'],
            $data['customField'],
            $data['customValue'],
            $data['isDefault'],
            $data['isActive']
        );
    }
}