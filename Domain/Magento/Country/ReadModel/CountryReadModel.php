<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento\Country\ReadModel;

use GetResponse\GetResponseIntegration\Domain\Magento\Country\ReadModel\Query\CountryId;
use Magento\Directory\Model\Country;
use Magento\Framework\ObjectManagerInterface;

class CountryReadModel
{
    private $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function getIsoCountryCode(CountryId $query): string
    {
        $country = $this->objectManager->create(Country::class)->load($query->getId());
        return $country->getData('iso3_code');
    }
}
