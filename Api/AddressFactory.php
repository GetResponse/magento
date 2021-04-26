<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

class AddressFactory
{
    public function create($address): ?Address
    {
        if (empty($address)) {
            return null;
        }

        $address1 = $address->getStreet()[0] ?? '';
        $address2 = $address->getStreet()[1] ?? '';

        return new Address(
            sprintf('%s %s', $address->getFirstname(), $address->getLastname()),
            $address->getCountryId(),
            $address->getFirstname(),
            $address->getLastname(),
            $address1,
            $address2,
            $address->getCity(),
            $address->getPostcode(),
            $address->getRegion(),
            '',
            $address->getTelephone(),
            $address->getCompany()
        );
    }
}