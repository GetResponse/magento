<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer\Admin;

use Exception;
use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Logger\Logger;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class OrderObserver implements ObserverInterface
{
    private $logger;
    private $apiService;

    public function __construct(
        Logger $getResponseLogger,
        ApiService $apiService
    ) {
        $this->logger = $getResponseLogger;
        $this->apiService = $apiService;
    }

    public function execute(EventObserver $observer): OrderObserver
    {
        /** @var Order $order && @phpstan-ignore-next-line */
        $order = $observer->getEvent()->getOrder();

        if ($order === null) {
            $this->logger->addNotice('Order in observer is empty', [
                'observerName' => $observer->getName(),
                'eventName' => $observer->getEventName(),
            ]);

            return $this;
        }

        try {
            $this->apiService->updateOrder($order, new Scope($order->getStoreId()));
        } catch (Exception $e) {
            $this->logger->addError($e->getMessage(), ['exception' => $e]);
        }

        return $this;
    }
}
