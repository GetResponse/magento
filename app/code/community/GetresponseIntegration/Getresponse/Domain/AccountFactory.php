<?php
use GetresponseIntegration_Getresponse_Domain_Account as Account;

class GetresponseIntegration_Getresponse_Domain_AccountFactory
{
    /**
     * @param array $response
     * @return GetresponseIntegration_Getresponse_Domain_Account
     */
    public static function createFromArray(array $response = null)
    {
        return new Account(
            $response['accountId'],
            $response['firstName'],
            $response['lastName'],
            $response['email'],
            $response['phone'],
            $response['state'],
            $response['city'],
            $response['street'],
            $response['zipCode'],
            isset($response['country']->country) ? $response['country']->country : '',
            isset($response['numberOfEmployees']->numberOfEmployees) ? $response['numberOfEmployees']->numberOfEmployees : '',
            isset($response['timeFormat']->timeFormat) ? $response['timeFormat']->timeFormat : '',
            isset($response['timeZoneName']->timeZone_name) ? $response['timeZoneName']->timeZone_name : '',
            isset($response['timeZoneOffset']->timeZoneOffset) ? $response['timeZoneOffset']->timeZoneOffset : ''
        );
    }
}