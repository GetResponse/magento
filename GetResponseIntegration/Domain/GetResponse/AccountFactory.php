<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

/**
 * Class AccountFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class AccountFactory
{
    /**
     * @param array $response
     *
     * @return Account
     */
    public static function createFromArray(array $response)
    {
        return new Account(
            $response['accountId'],
            $response['firstName'],
            $response['lastName'],
            $response['email'],
            $response['companyName'],
            $response['phone'],
            $response['state'],
            $response['city'],
            $response['street'],
            $response['zipCode'],
            isset($response['countryCode']->countryCodeId) ? $response['countryCode']->countryCode : ''
        );
    }
}
