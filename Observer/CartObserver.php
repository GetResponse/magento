<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Cart\CartService;
use GetResponse\GetResponseIntegration\Application\GetResponse\TrackingCode\CartService as TrackingCodeCartService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ReadModel\ContactReadModel;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ReadModel\Query\ContactByEmail;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Ecommerce\ReadModel\EcommerceReadModel;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
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
    private $ecommerceReadModel;
    private $contactReadModel;
    private $repository;
    private $apiService;
    private $trackingCodeCartService;
    private $session;

    public function __construct(
        CartService $cartService,
        Logger $logger,
        EcommerceReadModel $ecommerceReadModel,
        ContactReadModel $contactReadModel,
        Repository $repository,
        ApiService $apiService,
        TrackingCodeCartService $trackingCodeCartService,
        Session $session
    ) {
        $this->cartService = $cartService;
        $this->logger = $logger;
        $this->ecommerceReadModel = $ecommerceReadModel;
        $this->contactReadModel = $contactReadModel;
        $this->repository = $repository;
        $this->apiService = $apiService;
        $this->trackingCodeCartService = $trackingCodeCartService;
        $this->session = $session;
    }

    public function execute(EventObserver $observer): CartObserver
    {
        try {
            if (null === $observer->getCart() || null === $observer->getCart()->getQuote()) {
                $this->logger->addNotice('Cart or Quote in observer is empty', [
                    'observerName' => $observer->getName(),
                    'eventName' => $observer->getEventName(),
                ]);
                return $this;
            }
            /** @var Quote $quote */
            $quote = $observer->getCart()->getQuote();
            $scope = new Scope($quote->getStoreId());

            $pluginMode = PluginMode::createFromRepository($this->repository->getPluginMode());
            
            if ($pluginMode->isNewVersion()) {
                $this->trackingCodeCartService->addToBuffer($quote, $scope);

                if ($this->session->isLoggedIn()) {
                    $this->apiService->createCart($quote, $scope);
                }
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
    private function getContactFromGetResponse(Scope $scope): ?Contact
    {
        $contactListId = $this->ecommerceReadModel->getListId($scope);

        return $this->contactReadModel->findContactByEmail(
            new ContactByEmail(
                $this->session->getCustomer()->getEmail(),
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

        if (empty($shopId) || null === $this->getContactFromGetResponse($scope)) {
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
