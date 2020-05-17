<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Ecommerce\ReadModel\EcommerceReadModel;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Command\EditOrderCommandFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\OrderService;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Logger\Logger;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class UpdateOrderHandler implements ObserverInterface
{
    private $orderService;
    private $logger;
    private $editOrderCommandFactory;
    private $magentoStore;
    private $ecommerceReadModel;

    public function __construct(
        OrderService $orderService,
        Logger $getResponseLogger,
        EditOrderCommandFactory $editOrderCommandFactory,
        MagentoStore $magentoStore,
        EcommerceReadModel $ecommerceReadModel
    ) {
        $this->orderService = $orderService;
        $this->logger = $getResponseLogger;
        $this->editOrderCommandFactory = $editOrderCommandFactory;
        $this->magentoStore = $magentoStore;
        $this->ecommerceReadModel = $ecommerceReadModel;
    }

    public function execute(EventObserver $observer)
    {
        $scope = $this->magentoStore->getCurrentScope();

        try {
            $shopId = $this->ecommerceReadModel->getShopId($scope);

            if (empty($shopId)) {
                return;
            }

            $this->orderService->updateOrder(
                $this->editOrderCommandFactory->createForOrderService(
                    $observer->getEvent()->getOrder(),
                    $shopId
                ),
                $scope
            );

        } catch (Exception $e) {
            $this->logger->addError($e->getMessage(), ['exception' => $e]);
        }
    }

}
