<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Application\GetResponse\TrackingCode\CartService as TrackingCodeCartService;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Logger\Logger;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;

class CartObserver implements ObserverInterface
{
    private $logger;
    private $apiService;
    private $trackingCodeCartService;

    public function __construct(
        Logger $logger,
        ApiService $apiService,
        TrackingCodeCartService $trackingCodeCartService
    ) {
        $this->logger = $logger;
        $this->apiService = $apiService;
        $this->trackingCodeCartService = $trackingCodeCartService;
    }

    public function execute(EventObserver $observer): self
    {
        try {
            /** @phpstan-ignore-next-line */
            $cart = $observer->getCart();

            if (null === $cart) {
                $this->logger->addNotice('Cart or Quote in observer is empty', [
                    'observerName' => $observer->getName(),
                    'eventName' => $observer->getEventName(),
                ]);

                return $this;
            }

            /** @var Quote $quote */
            $quote = $cart->getQuote();

            if (null === $quote) {
                $this->logger->addNotice('Cart or Quote in observer is empty', [
                    'observerName' => $observer->getName(),
                    'eventName' => $observer->getEventName(),
                ]);

                return $this;
            }

            $scope = new Scope($quote->getStoreId());

            $this->trackingCodeCartService->addToBuffer($quote, $scope);
            $this->apiService->createCart($quote, $scope);
        } catch (Exception $e) {
            $this->logger->addError($e->getMessage(), ['exception' => $e]);
        }
        return $this;
    }
}
