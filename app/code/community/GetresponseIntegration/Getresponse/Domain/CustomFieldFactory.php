<?php
use GetresponseIntegration_Getresponse_Domain_CustomField as CustomField;
/**
 * Created by PhpStorm.
 * User: mjaniszewski
 * Date: 29/12/2017
 * Time: 10:53
 */

class GetresponseIntegration_Getresponse_Domain_CustomFieldFactory
{
    public static function createFromArray($array)
    {
        return new CustomField(
            $array['id'],
            $array['customField'],
            $array['customValue'],
            $array['isDefault'],
            $array['isActive']
        );
    }
}