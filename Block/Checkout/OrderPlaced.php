<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Block\Checkout;

use GetResponse\GetResponseIntegration\Domain\GetResponse\TrackingCode\TrackingCodeSession;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTracking;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class OrderPlaced extends Template
{
    private $repository;
    private $magentoStore;
    private $session;

    public function __construct(
        Context $context,
        Repository $repository,
        MagentoStore $magentoStore,
        TrackingCodeSession $session
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->magentoStore = $magentoStore;
        $this->session = $session;
    }

    public function getBufferedOrder(): ?array
    {
        $webEventTracking = WebEventTracking::createFromRepository(
            $this->repository->getWebEventTracking(
                $this->magentoStore->getCurrentScope()->getScopeId()
            )
        );

        if ($webEventTracking->isActive()) {
            return ['order' => $this->session->getOrderFromBuffer()];
        }

        return null;
    }
}
