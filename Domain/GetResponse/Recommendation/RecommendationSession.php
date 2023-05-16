<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Recommendation;

use Magento\Customer\Model\Session;

class RecommendationSession
{
    /** @var Session */
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function isUserLoggedIn(): bool
    {
        return $this->session->isLoggedIn();
    }

    public function pullProductIdAddedToWishList(): ?string
    {
        $productIdAddedToWishList = $this->session->getGrProductAddedToWishList();
        if ($productIdAddedToWishList) {
            $this->session->unsGrProductAddedToWishList();
        }

        return $productIdAddedToWishList;
    }

    public function setProductIdAddedToWishList(string $productId): void
    {
        $this->session->setGrProductAddedToWishList($productId);
    }

    public function pullProductIdRemovedFromWishList(): ?string
    {
        $productIdAddedToWishList = $this->session->getGrProductRemovedFromWishList();
        if ($productIdAddedToWishList) {
            $this->session->unsGrProductRemovedFromWishList();
        }

        return $productIdAddedToWishList;
    }

    public function setProductIdRemovedFromWishList(string $productId): void
    {
        $this->session->setGrProductRemovedFromWishList($productId);
    }
}