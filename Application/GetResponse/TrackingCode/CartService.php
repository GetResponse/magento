<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Application\GetResponse\TrackingCode;

use GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\Model\CartFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\TrackingCodeSession;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTracking;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use Magento\Quote\Model\Quote;

class CartService
{
    private $cartFactory;
    private $session;
    private $repository;

    public function __construct(CartFactory $cartFactory, TrackingCodeSession $session, Repository $repository)
    {
        $this->cartFactory = $cartFactory;
        $this->session = $session;
        $this->repository = $repository;
    }

    public function addToBuffer(Quote $quote, Scope $scope): void
    {
        $webConnect = WebEventTracking::createFromRepository(
            $this->repository->getWebEventTracking($scope->getScopeId())
        );

        if(!$webConnect->isActive()) {
            return;
        }

        $cart = $this->cartFactory->create($quote);
        $this->session->addCartToBuffer($cart);
    }

}
