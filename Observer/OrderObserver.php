<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Application\GetResponse\TrackingCode\OrderService;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Logger\Logger;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class OrderObserver implements ObserverInterface
{
    private $logger;
    private $apiService;
    private $orderService;

    public function __construct(
        Logger $logger,
        ApiService $apiService,
        OrderService $orderService
    ) {
        $this->logger = $logger;
        $this->apiService = $apiService;
        $this->orderService = $orderService;
    }

    public function execute(EventObserver $observer): OrderObserver
    {
        try {
            $order = $observer->getOrder();

            if (empty($order)) {
                $this->logger->addNotice('Order in observer is empty', [
                    'observerName' => $observer->getName(),
                    'eventName' => $observer->getEventName(),
                ]);
                return $this;
            }

            $scope = new Scope($order->getStoreId());

            $this->apiService->createOrder($order, $scope);
            $this->orderService->addToBuffer($order, $scope);

        } catch (Exception $e) {
            $this->logger->addError($e->getMessage(), ['exception' => $e]);
        }

        return $this;
    }
}
