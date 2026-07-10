<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\CustomerData\TrackingCode;

use GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\TrackingCodeBufferService;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Serialize\SerializerInterface;

class CustomerSectionSource implements SectionSourceInterface
{
    /** @var CurrentCustomer */
    protected $currentCustomer;
    /** @var TrackingCodeBufferService */
    private $service;
    /** @var SerializerInterface */
    private $serializer;

    /**
     * @param CurrentCustomer $currentCustomer
     * @param TrackingCodeBufferService $service
     * @param SerializerInterface $serializer
     */
    public function __construct(
        CurrentCustomer $currentCustomer,
        TrackingCodeBufferService $service,
        SerializerInterface $serializer
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->service = $service;
        $this->serializer = $serializer;
    }

    /**
     * Get section data.
     */
    public function getSectionData(): array
    {
        $email = $this->currentCustomer->getCustomerId() ? $this->currentCustomer->getCustomer()->getEmail() : null;
        $serializedCart = $this->serializer->serialize($this->service->getCartFromBuffer());

        return [
            'customerEmail' => $email,
            'cart' => $serializedCart
        ];
    }
}
