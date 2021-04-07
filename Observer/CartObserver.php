<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Api\ApiService;
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
use Magento\Quote\Model\Quote;

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
        Logger $logger,
        MagentoStore $magentoStore,
        EcommerceReadModel $ecommerceReadModel,
        ContactReadModel $contactReadModel,
        Repository $repository,
        ApiService $apiService
    ) {
        $this->cartService = $cartService;
        $this->logger = $logger;
        $this->magentoStore = $magentoStore;
        $this->customerSession = $customerSession;
        $this->ecommerceReadModel = $ecommerceReadModel;
        $this->contactReadModel = $contactReadModel;
        $this->repository = $repository;
        $this->apiService = $apiService;
    }

    public function execute(EventObserver $observer): CartObserver
    {
        try {
            if (false === $this->customerSession->isLoggedIn()) {
                return $this;
            }

            $quote = $observer->getCart()->getQuote();
            $scope = $this->magentoStore->getCurrentScope();

            $pluginMode = PluginMode::createFromRepository($this->repository->getPluginMode());

            if ($pluginMode->isNewVersion()) {
                $this->apiService->createCart($quote, $scope);
            } else {
                $this->handleOldVersion($quote, $scope);
            }
        } catch (Exception $e) {
            $this->logger->addError($e->getMessage(), ['exception' => $e]);
        }
        return $this;
    }

    /**
     * @throws ApiException
     * @throws GetresponseApiException
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
    private function handleOldVersion(Quote $quote, Scope $scope): void
    {
        $shopId = $this->ecommerceReadModel->getShopId($scope);

        if (empty($shopId) || $this->getContactFromGetResponse($scope)) {
            return;
        }

        $this->cartService->sendCart(
            $quote->getId(),
            $this->ecommerceReadModel->getListId($scope),
            $shopId,
            $scope
        );
    }
}
