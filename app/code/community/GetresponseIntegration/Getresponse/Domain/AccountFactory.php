<?php
use GetresponseIntegration_Getresponse_Domain_Account as Account;

class GetresponseIntegration_Getresponse_Domain_AccountFactory
{
    public static function createFromArray(array $response)
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
            isset($response['timeZone_name']->timeZone_name) ? $response['timeZone_name']->timeZone_name : '',
            isset($response['timeZone_offset']->timeZone_offset) ? $response['timeZone_offset']->timeZone_offset : ''
        );
    }
}