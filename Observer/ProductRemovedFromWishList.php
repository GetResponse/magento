<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\TrackingCodeBufferService;
use GetResponse\GetResponseIntegration\Logger\Logger;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Wishlist\Model\Item;

class ProductRemovedFromWishList implements ObserverInterface
{
    private $trackingCodeBufferService;
    private $logger;
    private $objectManager;

    public function __construct(
        TrackingCodeBufferService $trackingCodeBufferService,
        Logger $logger,
        ObjectManagerInterface $objectManager
    ) {
        $this->trackingCodeBufferService = $trackingCodeBufferService;
        $this->logger = $logger;
        $this->objectManager = $objectManager;
    }

    public function execute(Observer $observer): self
    {
        try {
            if (false === $this->trackingCodeBufferService->isUserLoggedIn()) {
                return $this;
            }

            $wishListId = $observer->getData()['request']->getParam('item');
            $item = $this->objectManager->create(Item::class)->load($wishListId);
            $this->trackingCodeBufferService->setProductIdRemovedFromWishList($item->getProductId());
        } catch (Exception $e) {
            $this->logger->addError($e->getMessage(), ['exception' => $e]);
        }

        return $this;
    }
}
