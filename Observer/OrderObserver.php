<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Application\GetResponse\TrackingCode\OrderService as TrackingCodeOrderService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ReadModel\ContactReadModel;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ReadModel\Query\ContactByEmail;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Ecommerce\ReadModel\EcommerceReadModel;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Command\AddOrderCommandFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Exception\InvalidOrderException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\OrderService;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\Contact\Contact;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class OrderObserver implements ObserverInterface
{
    private $orderService;
    private $logger;
    private $addOrderCommandFactory;
    private $customerSession;
    private $ecommerceReadModel;
    private $contactReadModel;
    private $repository;
    private $apiService;
    private $trackingCodeOrderService;

    public function __construct(
        Session $customerSession,
        OrderService $orderService,
        Logger $logger,
        AddOrderCommandFactory $addOrderCommandFactory,
        EcommerceReadModel $ecommerceReadModel,
        ContactReadModel $contactReadModel,
        Repository $repository,
        ApiService $apiService,
        TrackingCodeOrderService $trackingCodeOrderService
    ) {
        $this->orderService = $orderService;
        $this->logger = $logger;
        $this->addOrderCommandFactory = $addOrderCommandFactory;
        $this->customerSession = $customerSession;
        $this->ecommerceReadModel = $ecommerceReadModel;
        $this->contactReadModel = $contactReadModel;
        $this->repository = $repository;
        $this->apiService = $apiService;
        $this->trackingCodeOrderService = $trackingCodeOrderService;
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

            $pluginMode = PluginMode::createFromRepository($this->repository->getPluginMode());

            if ($pluginMode->isNewVersion()) {
                $this->apiService->createOrder($order, $scope);
                $this->trackingCodeOrderService->addToBuffer($order, $scope);
            } else {
                $this->handleOldVersion($order, $scope);
            }
        } catch (Exception $e) {
            $this->logger->addError($e->getMessage(), ['exception' => $e]);
        }

        return $this;
    }

    /**
     * @throws ApiException
     * @throws GetresponseApiException
     * @throws InvalidOrderException
     */
    private function handleOldVersion(Order $order, Scope $scope): void
    {
        $shopId = $this->ecommerceReadModel->getShopId($scope);

        if (empty($shopId) || null === $this->getContactFromGetResponse($scope)) {
            return;
        }

        $this->orderService->addOrder(
            $this->addOrderCommandFactory->createForMagentoOrder(
                $order,
                $this->ecommerceReadModel->getListId($scope),
                $shopId
            ),
            $scope
        );
    }

    /**
     * @throws ApiException
     * @throws GetresponseApiException
     */
    private function getContactFromGetResponse(Scope $scope): ?Contact
    {
        $contactListId = $this->ecommerceReadModel->getListId($scope);

        return $this->contactReadModel->findContactByEmail(
            new ContactByEmail(
                $this->customerSession->getCustomer()->getEmail(),
                $contactListId,
                $scope
            )
        );
    }
}
