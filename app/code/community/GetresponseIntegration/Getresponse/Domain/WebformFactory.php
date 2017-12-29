<?php
use GetresponseIntegration_Getresponse_Domain_Webform as Webform;

class GetresponseIntegration_Getresponse_Domain_WebformFactory
{
    public static function createFromArray($data)
    {
        return new Webform(
                $data['webformId'],
                $data['activeSubscription'],
                $data['layoutPosition'],
                $data['blockPosition'],
                $data['webformTitle'],
                $data['url']
        );
    }
}