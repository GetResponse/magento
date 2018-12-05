<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Account;

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
            isset($response['accountId']) ? $response['accountId'] : '',
            isset($response['firstName']) ? $response['firstName'] : '',
            isset($response['lastName']) ? $response['lastName'] : '',
            isset($response['email']) ? $response['email'] : '',
            isset($response['companyName']) ? $response['companyName'] : '',
            isset($response['phone']) ? $response['phone'] : '',
            isset($response['state']) ? $response['state'] : '',
            isset($response['city']) ? $response['city'] : '',
            isset($response['street']) ? $response['street'] : '',
            isset($response['zipCode']) ? $response['zipCode'] : '',
            isset($response['countryCode']->countryCodeId) ? $response['countryCode']->countryCode : ''
        );
    }
}
