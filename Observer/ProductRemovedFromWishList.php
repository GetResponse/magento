<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Recommendation\RecommendationSession;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Logger\Logger;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Wishlist\Model\Item;

class ProductRemovedFromWishList implements ObserverInterface
{
    private $session;
    private $logger;
    private $objectManager;

    public function __construct(
        RecommendationSession $session,
        Logger $logger,
        Repository $repository,
        ObjectManagerInterface $objectManager
    ) {
        $this->session = $session;
        $this->logger = $logger;
        $this->repository = $repository;
        $this->objectManager = $objectManager;
    }

    public function execute(Observer $observer): self
    {
        try {
            $pluginMode = PluginMode::createFromRepository($this->repository->getPluginMode());
            if (!$pluginMode->isNewVersion()) {
                return $this;
            }

            if (false === $this->session->isUserLoggedIn()) {
                return $this;
            }

            $wishListId = $observer->getData()['request']->getParam('item');
            $item = $this->objectManager->create(Item::class)->load($wishListId);
            $this->session->setProductIdRemovedFromWishList($item->getProductId());
        } catch (Exception $e) {
            $this->logger->addError($e->getMessage(), ['exception' => $e]);
        }

        return $this;
    }
}
