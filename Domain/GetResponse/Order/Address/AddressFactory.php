<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Address;

use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GrShareCode\Address\Address;
use GrShareCode\Address\AddressFactory as GrAddressFactory;
use Magento\Sales\Model\Order;

/**
 * Class AddressFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Address
 */
class AddressFactory
{
    /** @var Repository */
    private $magentoRepository;

    /**
     * @param Repository $magentoRepository
     */
    public function __construct(Repository $magentoRepository)
    {
        $this->magentoRepository = $magentoRepository;
    }

    /**
     * @param Order $order
     * @return Address
     */
    public function createBillingAddressFromMagentoOrder(Order $order)
    {
        /** @var Order\Address $address */
        $orderBillingAddress = $order->getBillingAddress();
        $countryCode = $this->magentoRepository->getCountryCodeByCountryId($orderBillingAddress->getCountryId());

        $billingAddress = GrAddressFactory::createFromParams(
            $countryCode->getData('iso3_code'),
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

        return $billingAddress;
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

                $countryCode = $this->magentoRepository->getCountryCodeByCountryId($shippingAddress->getCountryId());

                $shareCodeShippingAddress = GrAddressFactory::createFromParams(
                    $countryCode->getData('iso3_code'),
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