<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;

class WebTrackingCode implements SectionSourceInterface
{
    protected $currentCustomer;

    public function __construct(CurrentCustomer $currentCustomer)
    {
        $this->currentCustomer = $currentCustomer;
    }

    public function getSectionData(): array
    {
        $email = $this->currentCustomer->getCustomerId() ? $this->currentCustomer->getCustomer()->getEmail() : null;

        return [
            'customerEmail' => $email,
        ];
    }
}
