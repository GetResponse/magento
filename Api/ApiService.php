<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use GetResponse\GetResponseIntegration\Domain\Magento\LiveSynchronization;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order as MagentoOrder;

class ApiService
{
    private $repository;
    private $httpClient;
    private $cartFactory;
    private $orderFactory;
    private $productFactory;
    private $customerFactory;
    private $subscriberFactory;

    public function __construct(
        Repository $repository,
        HttpClient $httpClient,
        CartFactory $cartFactory,
        OrderFactory $orderFactory,
        ProductFactory $productFactory,
        CustomerFactory $customerFactory,
        SubscriberFactory $subscriberFactory
    ) {
        $this->repository = $repository;
        $this->httpClient = $httpClient;
        $this->cartFactory = $cartFactory;
        $this->orderFactory = $orderFactory;
        $this->productFactory = $productFactory;
        $this->customerFactory = $customerFactory;
        $this->subscriberFactory = $subscriberFactory;
    }

    /**
     * @throws HttpClientException
     */
    public function upsertCustomerAddress(AddressInterface $address, Scope $scope): void
    {
        $liveSynchronization = LiveSynchronization::createFromRepository(
            $this->repository->getLiveSynchronization($scope->getScopeId())
        );

        if (!$liveSynchronization->shouldImportCustomer()) {
            return;
        }

        $this->httpClient->post(
            $liveSynchronization->getCallbackUrl(),
            $this->customerFactory->createFromCustomerAddress($address)
        );
    }

    /**
     * @throws HttpClientException
     */
    public function upsertCustomer(CustomerInterface $customer, Scope $scope): void
    {
        $liveSynchronization = LiveSynchronization::createFromRepository(
            $this->repository->getLiveSynchronization($scope->getScopeId())
        );

        if (!$liveSynchronization->shouldImportCustomer()) {
            return;
        }

        $this->httpClient->post(
            $liveSynchronization->getCallbackUrl(),
            $this->customerFactory->create($customer)
        );
    }

    /**
     * @throws HttpClientException
     */
    public function upsertCustomerSubscription(Subscriber $subscriber, Scope $scope): void
    {
        $liveSynchronization = LiveSynchronization::createFromRepository(
            $this->repository->getLiveSynchronization($scope->getScopeId())
        );

        if (!$liveSynchronization->shouldImportCustomer()) {
            return;
        }

        $this->httpClient->post(
            $liveSynchronization->getCallbackUrl(),
            $this->customerFactory->createFromNewsletterSubscription($subscriber)
        );
    }

    /**
     * @throws HttpClientException
     */
    public function createCart(Quote $quote, Scope $scope): void
    {
        $liveSynchronization = LiveSynchronization::createFromRepository(
            $this->repository->getLiveSynchronization($scope->getScopeId())
        );

        if (!$liveSynchronization->shouldImportCart()) {
            return;
        }

        $this->httpClient->post(
            $liveSynchronization->getCallbackUrl(),
            $this->cartFactory->create($quote)
        );
    }

    /**
     * @throws HttpClientException
     */
    public function createOrder(MagentoOrder $order, Scope $scope): void
    {
        $liveSynchronization = LiveSynchronization::createFromRepository(
            $this->repository->getLiveSynchronization($scope->getScopeId())
        );

        if (!$liveSynchronization->shouldImportOrder()) {
            return;
        }

        $this->httpClient->post(
            $liveSynchronization->getCallbackUrl(),
            $this->orderFactory->create($order)
        );
    }

    /**
     * @throws HttpClientException
     */
    public function updateOrder(MagentoOrder $order, Scope $scope): void
    {
        $liveSynchronization = LiveSynchronization::createFromRepository(
            $this->repository->getLiveSynchronization($scope->getScopeId())
        );

        if (!$liveSynchronization->shouldImportOrder()) {
            return;
        }

        $this->httpClient->post(
            $liveSynchronization->getCallbackUrl(),
            $this->orderFactory->create($order)
        );
    }

    /**
     * @throws HttpClientException
     */
    public function upsertProductCatalog(MagentoProduct $product, Scope $scope): void
    {
        $liveSynchronization = LiveSynchronization::createFromRepository(
            $this->repository->getLiveSynchronization($scope->getScopeId())
        );

        if (!$liveSynchronization->shouldImportProduct()) {
            return;
        }

        $productsToUpsert = $this->productFactory->create($product, $scope);
        foreach ($productsToUpsert as $productToUpsert) {
            $callbackUrl = $liveSynchronization->getCallbackUrl();
            $this->httpClient->post($callbackUrl, $productToUpsert);
        }
    }

    public function upsertSubscriber(Subscriber $subscriber, Scope $scope): void
    {
        $liveSynchronization = LiveSynchronization::createFromRepository(
            $this->repository->getLiveSynchronization($scope->getScopeId())
        );

        if (!$liveSynchronization->shouldImportCustomer()) {
            return;
        }

        $this->httpClient->post(
            $liveSynchronization->getCallbackUrl(),
            $this->subscriberFactory->create($subscriber)
        );
    }
}
