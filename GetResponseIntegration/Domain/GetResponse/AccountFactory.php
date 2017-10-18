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
    public static function buildFromApiResponse(array $response)
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

    /**
     * @param $response
     *
     * @return Account
     */
    public static function buildFromRepository($response)
    {
        if (!isset($response['accountId']) || empty($response['accountId'])) {
            return new Account(null, null, null, null, null, null, null, null, null, null, null);
        }

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
