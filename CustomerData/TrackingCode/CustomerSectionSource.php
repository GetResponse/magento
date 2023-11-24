<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\CustomerData\TrackingCode;

use GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\TrackingCodeSession;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Serialize\SerializerInterface;

class CustomerSectionSource implements SectionSourceInterface
{
    protected $currentCustomer;
    private $session;
    private $serializer;

    public function __construct(
        CurrentCustomer $currentCustomer,
        TrackingCodeSession $session,
        SerializerInterface $serializer
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->session = $session;
        $this->serializer = $serializer;
    }

    public function getSectionData(): array
    {
        $email = $this->currentCustomer->getCustomerId() ? $this->currentCustomer->getCustomer()->getEmail() : null;
        $serializedCart = $this->serializer->serialize($this->session->getCartFromBuffer());

        return [
            'customerEmail' => $email,
            'cart' => $serializedCart
        ];
    }
}
