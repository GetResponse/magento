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

class ProductAddedToWishList implements ObserverInterface
{
    private $session;
    private $logger;
    private $repository;

    public function __construct(RecommendationSession $session, Logger $logger, Repository $repository)
    {
        $this->session = $session;
        $this->logger = $logger;
        $this->repository = $repository;
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

            $product = $observer->getEvent()->getProduct();
            $this->session->setProductIdAddedToWishList($product->getId());
        } catch (Exception $e) {
            $this->logger->addError($e->getMessage(), ['exception' => $e]);
        }

        return $this;
    }
}
