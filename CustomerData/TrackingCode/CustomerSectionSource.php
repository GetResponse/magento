<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\CustomerData\TrackingCode;

use GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\TrackingCodeBufferService;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\StoreManagerInterface;

class CustomerSectionSource implements SectionSourceInterface
{
    protected $currentCustomer;
    protected $storeManager;
    protected $design;
    private $service;
    private $serializer;

    public function __construct(
        CurrentCustomer $currentCustomer,
        TrackingCodeBufferService $service,
        SerializerInterface $serializer,
        DesignInterface $design,
        StoreManagerInterface $storeManager,
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->service = $service;
        $this->serializer = $serializer;
        $this->design = $design;
        $this->storeManager = $storeManager;
    }

    public function getSectionData(): array
    {
        $theme = $this->design->getDesignTheme();
        $code = $theme->getCode();
        $email = $this->currentCustomer->getCustomerId() ? $this->currentCustomer->getCustomer()->getEmail() : null;
        $serializedCart = $this->serializer->serialize($this->service->getCartFromBuffer());
        $currency = $this->storeManager->getStore()->getCurrentCurrency()->getCode();

        return [
            'customerEmail' => $email,
            'cart' => $serializedCart,
            'theme' => $code,
            'currency' => $currency
        ];
    }
}
