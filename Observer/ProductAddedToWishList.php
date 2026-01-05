<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Recommendation\RecommendationSession;
use GetResponse\GetResponseIntegration\Logger\Logger;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ProductAddedToWishList implements ObserverInterface
{
    private $session;
    private $logger;

    public function __construct(
        RecommendationSession $session,
        Logger $logger
    )
    {
        $this->session = $session;
        $this->logger = $logger;
    }

    public function execute(Observer $observer): self
    {
        try {
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
