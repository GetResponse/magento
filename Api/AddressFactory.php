<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;

class AddressFactory
{
    public function createFromCustomer(AddressInterface $address): Address
    {
        $street = $address->getStreet();
        $address1 = count($street) <= 1 ? [$street[0]] : array_slice($street, 0, count($street) - 1);
        $address2 = count($street) > 1 ? array_slice($street, count($street) - 1, 1) : [''];

        return new Address(
            sprintf('%s %s', $address->getFirstname(), $address->getLastname()),
            (string)$address->getCountryId(),
            (string)$address->getFirstname(),
            (string)$address->getLastname(),
            implode(' ', $address1),
            implode(' ', $address2),
            (string)$address->getCity(),
            (string)$address->getPostcode(),
            $address->getRegion() ? $address->getRegion()->getRegion() : '',
            $address->getRegion() ? $address->getRegion()->getRegionCode() : '',
            (string)$address->getTelephone(),
            $address->getCompany()
        );
    }

    public function createFromOrder(OrderAddressInterface $address): Address
    {
        $street = $address->getStreet();
        $address1 = count($street) <= 1 ? [$street[0]] : array_slice($street, 0, count($street) - 1);
        $address2 = count($street) > 1 ? array_slice($street, count($street) - 1, 1) : [''];

        return new Address(
            sprintf('%s %s', $address->getFirstname(), $address->getLastname()),
            (string)$address->getCountryId(),
            (string)$address->getFirstname(),
            (string)$address->getLastname(),
            implode(' ', $address1),
            implode(' ', $address2),
            (string)$address->getCity(),
            (string)$address->getPostcode(),
            (string)$address->getRegion(),
            (string)$address->getRegionCode(),
            (string)$address->getTelephone(),
            $address->getCompany()
        );
    }
}
