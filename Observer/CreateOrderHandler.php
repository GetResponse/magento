<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Account\ReadModel\AccountReadModel;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ReadModel\ContactReadModel;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ReadModel\Query\ContactByEmail;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Ecommerce\ReadModel\EcommerceReadModel;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Command\AddOrderCommandFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Exception\InvalidOrderException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\OrderService;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\Contact\Contact;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;

class CreateOrderHandler implements ObserverInterface
{
    private $orderService;
    private $logger;
    private $addOrderCommandFactory;
    private $magentoStore;
    private $customerSession;
    private $ecommerceReadModel;
    private $contactReadModel;
    private $repository;
    private $accountReadModel;
    private $apiService;

    public function __construct(
        Session $customerSession,
        OrderService $orderService,
        Logger $getResponseLogger,
        AddOrderCommandFactory $addOrderCommandFactory,
        EcommerceReadModel $ecommerceReadModel,
        ContactReadModel $contactReadModel,
        MagentoStore $magentoStore,
        Repository $repository,
        AccountReadModel $accountReadModel,
        ApiService $apiService
    ) {
        $this->orderService = $orderService;
        $this->logger = $getResponseLogger;
        $this->addOrderCommandFactory = $addOrderCommandFactory;
        $this->customerSession = $customerSession;
        $this->ecommerceReadModel = $ecommerceReadModel;
        $this->contactReadModel = $contactReadModel;
        $this->magentoStore = $magentoStore;
        $this->repository = $repository;
        $this->accountReadModel = $accountReadModel;
        $this->apiService = $apiService;
    }

    public function execute(EventObserver $observer): CreateOrderHandler
    {
        $scope = $this->magentoStore->getCurrentScope();

        try {
            $pluginMode = PluginMode::createFromRepository($this->repository->getPluginMode($scope->getScopeId()));

            if ($pluginMode->isNewVersion()) {
                $this->handleNewVersion($observer, $scope);
            }

            $this->handleOldVersion($observer, $scope);
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

        if (false === $this->customerSession->isLoggedIn()) {
            return;
        }

        if (null === $this->getContactFromGetResponse($scope)) {
            return;
        }

        $this->orderService->addOrder(
            $this->addOrderCommandFactory->createForMagentoOrder(
                $observer->getOrder(),
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

    private function handleNewVersion(EventObserver $observer, Scope $scope): void
    {
        // if customer is not logged in - skip
        if (false === $this->customerSession->isLoggedIn()) {
            return;
        }

        // if plugin is not connected - skip
        if (false === $this->accountReadModel->isConnected($scope)) {
            return;
        }

        try {
            $this->apiService->sendOrder($observer->getOrder(), $scope);
        } catch (Exception $e) {
            $this->logger->addError($e->getMessage(), ['exception' => $e]);
        }
    }
}
