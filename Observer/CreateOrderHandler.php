<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ReadModel\ContactReadModel;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ReadModel\Query\ContactByEmail;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Ecommerce\ReadModel\EcommerceReadModel;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Command\AddOrderCommandFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\OrderService;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\Contact\Contact;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class CreateOrderHandler implements ObserverInterface
{
    private $orderService;
    private $orderFactory;
    private $logger;
    private $addOrderCommandFactory;
    private $magentoStore;
    private $customerSession;
    private $ecommerceReadModel;
    private $contactReadModel;

    public function __construct(
        Session $customerSession,
        Order $orderFactory,
        OrderService $orderService,
        Logger $getResponseLogger,
        AddOrderCommandFactory $addOrderCommandFactory,
        EcommerceReadModel $ecommerceReadModel,
        ContactReadModel $contactReadModel,
        MagentoStore $magentoStore
    ) {
        $this->orderService = $orderService;
        $this->orderFactory = $orderFactory;
        $this->logger = $getResponseLogger;
        $this->addOrderCommandFactory = $addOrderCommandFactory;
        $this->customerSession = $customerSession;
        $this->ecommerceReadModel = $ecommerceReadModel;
        $this->contactReadModel = $contactReadModel;
        $this->magentoStore = $magentoStore;
    }

    public function execute(EventObserver $observer)
    {
        $scope = $this->magentoStore->getCurrentScope();

        try {
            $shopId = $this->ecommerceReadModel->getShopId($scope);

            if (empty($shopId)) {
                return $this;
            }

            if (false === $this->customerSession->isLoggedIn()) {
                return $this;
            }

            if (null === $this->getContactFromGetResponse($scope)) {
                return $this;
            }

            $order = $this->orderFactory->load(
                $observer->getEvent()->getOrderIds()[0]
            );

            $this->orderService->addOrder(
                $this->addOrderCommandFactory->createForMagentoOrder(
                    $order,
                    $this->ecommerceReadModel->getListId($scope),
                    $shopId
                ),
                $scope
            );

        } catch (Exception $e) {
            $this->logger->addError($e->getMessage(), ['exception' => $e]);
        }

        return $this;
    }

    /**
     * @param Scope $scope
     * @return null|Contact
     * @throws ApiException
     * @throws GetresponseApiException
     */
    private function getContactFromGetResponse(Scope $scope)
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
