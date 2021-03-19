<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Api\HttpClientException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ReadModel\ContactReadModel;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ReadModel\Query\ContactByEmail;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Ecommerce\ReadModel\EcommerceReadModel;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Command\EditOrderCommandFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Exception\InvalidOrderException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\OrderService;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\Contact\Contact;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use \Magento\Sales\Model\Order;

class AdminOrderObserver implements ObserverInterface
{
    private $orderService;
    private $logger;
    private $editOrderCommandFactory;
    private $magentoStore;
    private $ecommerceReadModel;
    private $contactReadModel;
    private $repository;
    private $apiService;

    public function __construct(
        OrderService $orderService,
        Logger $getResponseLogger,
        EditOrderCommandFactory $editOrderCommandFactory,
        MagentoStore $magentoStore,
        EcommerceReadModel $ecommerceReadModel,
        ContactReadModel $contactReadModel,
        Repository $repository,
        ApiService $apiService
    ) {
        $this->orderService = $orderService;
        $this->logger = $getResponseLogger;
        $this->editOrderCommandFactory = $editOrderCommandFactory;
        $this->magentoStore = $magentoStore;
        $this->contactReadModel = $contactReadModel;
        $this->ecommerceReadModel = $ecommerceReadModel;
        $this->repository = $repository;
        $this->apiService = $apiService;
    }

    public function execute(EventObserver $observer): AdminOrderObserver
    {
        $scope = $this->magentoStore->getCurrentScope();

        try {
            $pluginMode = PluginMode::createFromRepository($this->repository->getPluginMode($scope->getScopeId()));

            if ($pluginMode->isNewVersion()) {
                $this->handleNewVersion($observer, $scope);
            } else {
                $this->handleOldVersion($observer, $scope);
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
    private function handleOldVersion(EventObserver $observer, Scope $scope): void
    {
        $shopId = $this->ecommerceReadModel->getShopId($scope);

        if (empty($shopId)) {
            return;
        }

        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();

        if (null === $this->getContactFromGetResponse($order, $scope)) {
            return;
        }

        $this->orderService->updateOrder(
            $this->editOrderCommandFactory->createForOrderService(
                $order,
                $shopId
            ),
            $scope
        );
    }

    /**
     * @throws ApiException
     * @throws GetresponseApiException
     */
    private function getContactFromGetResponse(Order $order, Scope $scope): ?Contact
    {
        $contactListId = $this->ecommerceReadModel->getListId($scope);

        return $this->contactReadModel->findContactByEmail(
            new ContactByEmail(
                $order->getCustomerEmail(),
                $contactListId,
                $scope
            )
        );
    }

    /**
     * @throws HttpClientException
     */
    private function handleNewVersion(EventObserver $observer, Scope $scope): void
    {
        $this->apiService->updateOrder($observer->getOrder(), $scope);
    }
}
