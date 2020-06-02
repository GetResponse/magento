<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Account;

use GrShareCode\Account\Account as ShareCodeAccount;

class AccountFactory
{
    public static function createFromArray(array $response): Account
    {
        return new Account(
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

    public static function createFromShareCodeAccount(ShareCodeAccount $account): Account
    {
        return new Account(
            $account->getFirstName(),
            $account->getLastName(),
            $account->getEmail(),
            $account->getCompanyName(),
            $account->getPhone(),
            $account->getStreet(),
            $account->getCity(),
            $account->getStreet(),
            $account->getZipCode(),
            $account->getZipCode()
        );
    }
}
