<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Api\HttpClientException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Cart\CartService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ReadModel\ContactReadModel;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ReadModel\Query\ContactByEmail;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Ecommerce\ReadModel\EcommerceReadModel;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\Contact\Contact;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class CartObserver implements ObserverInterface
{
    private $cartService;
    private $logger;
    private $magentoStore;
    private $customerSession;
    private $ecommerceReadModel;
    private $contactReadModel;
    private $repository;
    private $apiService;

    public function __construct(
        Session $customerSession,
        CartService $cartService,
        Logger $getResponseLogger,
        MagentoStore $magentoStore,
        EcommerceReadModel $ecommerceReadModel,
        ContactReadModel $contactReadModel,
        Repository $repository,
        ApiService $apiService
    ) {
        $this->cartService = $cartService;
        $this->logger = $getResponseLogger;
        $this->magentoStore = $magentoStore;
        $this->customerSession = $customerSession;
        $this->ecommerceReadModel = $ecommerceReadModel;
        $this->contactReadModel = $contactReadModel;
        $this->repository = $repository;
        $this->apiService = $apiService;
    }

    public function execute(EventObserver $observer): CartObserver
    {
        $scope = $this->magentoStore->getCurrentScope();

        try {
            $pluginMode = PluginMode::createFromRepository($this->repository->getPluginMode($scope->getScopeId()));

            if ($pluginMode->isNewVersion()) {
                $this->handleNewPluginVersion($observer, $scope);
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
     * @param Scope $scope
     * @return null|Contact
     */
    private function getContactFromGetResponse(Scope $scope): Contact
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

    /**
     * @throws ApiException
     * @throws GetresponseApiException
     */
    private function handleOldVersion(EventObserver $observer, Scope $scope): void
    {
        $shopId = $this->ecommerceReadModel->getShopId($scope);

        if (empty($shopId)) {
            return;
        }

        if (false === $this->customerSession->isLoggedIn() || $this->getContactFromGetResponse($scope)) {
            return;
        }

        $this->cartService->sendCart(
            $observer->getCart()->getQuote()->getId(),
            $this->ecommerceReadModel->getListId($scope),
            $shopId,
            $scope
        );
    }

    /**
     * @throws HttpClientException
     */
    private function handleNewPluginVersion(EventObserver $observer, Scope $scope): void
    {
        // if customer is not logged in - skip
        if (false === $this->customerSession->isLoggedIn()) {
            return;
        }

        $this->apiService->createCart($observer->getCart()->getQuote(), $scope);
    }
}
