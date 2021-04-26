<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Address;

use GetResponse\GetResponseIntegration\Domain\Magento\Country\ReadModel\CountryReadModel;
use GetResponse\GetResponseIntegration\Domain\Magento\Country\ReadModel\Query\CountryId;
use GrShareCode\Address\Address;
use GrShareCode\Address\AddressFactory as GrAddressFactory;
use Magento\Sales\Model\Order;

class AddressFactory
{
    private $countryReadModel;

    public function __construct(CountryReadModel $countryReadModel)
    {
        $this->countryReadModel = $countryReadModel;
    }

    public function createBillingAddressFromMagentoOrder(Order $order): Address
    {
        $orderBillingAddress = $order->getBillingAddress();

        return GrAddressFactory::createFromParams(
            $this->countryReadModel->getIsoCountryCode(
                new CountryId($orderBillingAddress->getCountryId())
            ),
            $orderBillingAddress->getFirstname(),
            $orderBillingAddress->getLastname(),
            $orderBillingAddress->getStreetLine(1),
            $orderBillingAddress->getStreetLine(2),
            $orderBillingAddress->getCity(),
            $orderBillingAddress->getPostcode(),
            '',
            '',
            $orderBillingAddress->getTelephone(),
            $orderBillingAddress->getCompany()
        );
    }

    /**
     * @param Order $order
     * @return Address
     */
    public function createShippingAddressFromMagentoOrder(Order $order)
    {
        $shareCodeShippingAddress = null;

        if ($order->hasShippingAddressId()) {

            /** @var Order\Address $address */
            $shippingAddress = $order->getShippingAddress();

            if ($shippingAddress) {
                $shareCodeShippingAddress = GrAddressFactory::createFromParams(
                    $this->countryReadModel->getIsoCountryCode(
                        new CountryId((int) $shippingAddress->getCountryId())
                    ),
                    $shippingAddress->getFirstname(),
                    $shippingAddress->getLastname(),
                    $shippingAddress->getStreetLine(1),
                    $shippingAddress->getStreetLine(2),
                    $shippingAddress->getCity(),
                    $shippingAddress->getPostcode(),
                    '',
                    '',
                    $shippingAddress->getTelephone(),
                    $shippingAddress->getCompany()
                );
            }
        }

        return $shareCodeShippingAddress;
    }
}
