<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\TrackingCodeBufferService;
use GetResponse\GetResponseIntegration\Logger\Logger;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ProductAddedToWishList implements ObserverInterface
{
    private $trackingCodeBufferService;
    private $logger;

    public function __construct(
        TrackingCodeBufferService $trackingCodeBufferService,
        Logger $logger
    ) {
        $this->trackingCodeBufferService = $trackingCodeBufferService;
        $this->logger = $logger;
    }

    public function execute(Observer $observer): self
    {
        try {
            if (false === $this->trackingCodeBufferService->isUserLoggedIn()) {
                return $this;
            }

            /** @phpstan-ignore-next-line */
            $product = $observer->getEvent()->getProduct();
            $this->trackingCodeBufferService->setProductIdAddedToWishList($product->getId());
        } catch (Exception $e) {
            $this->logger->addError($e->getMessage(), ['exception' => $e]);
        }

        return $this;
    }
}
