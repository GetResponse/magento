<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use GetResponse\GetResponseIntegration\Domain\Magento\LiveSynchronization;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Quote\Model\Quote;
use \Magento\Sales\Model\Order as MagentoOrder;

class ApiService
{
    private $repository;
    private $httpClient;
    private $cartFactory;
    private $orderFactory;
    private $productFactory;

    public function __construct(
        Repository $repository,
        HttpClient $httpClient,
        CartFactory $cartFactory,
        OrderFactory $orderFactory,
        ProductFactory $productFactory
    ) {
        $this->repository = $repository;
        $this->httpClient = $httpClient;
        $this->cartFactory = $cartFactory;
        $this->orderFactory = $orderFactory;
        $this->productFactory = $productFactory;
    }

    /**
     * @throws HttpClientException
     */
    public function createCart(Quote $quote, Scope $scope): void
    {
        $liveSynchronization = LiveSynchronization::createFromRepository(
            $this->repository->getLiveSynchronization($scope->getScopeId())
        );

        if (!$liveSynchronization->isActive()) {
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

        if (!$liveSynchronization->isActive()) {
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

        if (!$liveSynchronization->isActive()) {
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
    public function createProduct(MagentoProduct $product, Scope $scope): void
    {
        $liveSynchronization = LiveSynchronization::createFromRepository(
            $this->repository->getLiveSynchronization($scope->getScopeId())
        );

        if (!$liveSynchronization->isActive()) {
            return;
        }

        $this->httpClient->post(
            $liveSynchronization->getCallbackUrl(),
            $this->productFactory->create($product, $scope)
        );
    }
}
