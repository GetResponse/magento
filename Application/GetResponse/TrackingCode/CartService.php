<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Application\GetResponse\TrackingCode;

use GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\Model\CartFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\TrackingCodeBufferService;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTracking;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use Magento\Quote\Model\Quote;

class CartService
{
    /** @var CartFactory */
    private $cartFactory;
    /** @var TrackingCodeBufferService */
    private $service;
    /** @var Repository */
    private $repository;

    /**
     * @param CartFactory $cartFactory
     * @param TrackingCodeBufferService $service
     * @param Repository $repository
     */
    public function __construct(CartFactory $cartFactory, TrackingCodeBufferService $service, Repository $repository)
    {
        $this->cartFactory = $cartFactory;
        $this->service = $service;
        $this->repository = $repository;
    }

    /**
     * Handle add to buffer.
     *
     * @param Quote $quote
     * @param Scope $scope
     */
    public function addToBuffer(Quote $quote, Scope $scope): void
    {
        $webConnect = WebEventTracking::createFromRepository(
            $this->repository->getWebEventTracking($scope->getScopeId())
        );

        if (!$webConnect->isActive()) {
            return;
        }

        $cart = $this->cartFactory->create($quote);
        $this->service->addCartToBuffer($cart);
    }
}
