<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Block\Checkout;

use GetResponse\GetResponseIntegration\Application\GetResponse\TrackingCode\OrderService;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class OrderPlaced extends Template
{
    private $magentoStore;
    private $orderService;

    public function __construct(
        Context $context,
        OrderService $orderService,
        MagentoStore $magentoStore
    ) {
        parent::__construct($context);
        $this->magentoStore = $magentoStore;
        $this->orderService = $orderService;
    }

    public function getBufferedOrder(): array
    {
        $scope = $this->magentoStore->getCurrentScope();
        return ['order' => $this->orderService->getOrderFromBuffer($scope)];
    }
}
