<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Block\Admin;

use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTracking as WebEventTrackingSettings;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Helper\Route;
use Magento\Framework\View\Element\Template\Context;

class WebEventTracking extends AdminTemplate
{
    private $repository;

    public function __construct(
        Context $context,
        MagentoStore $magentoStore,
        Repository $repository
    ) {
        parent::__construct($context, $magentoStore);
        $this->repository = $repository;
        $this->routePrefix = Route::WEB_TRAFFIC_INDEX_ROUTE;
    }

    public function getWebEventTracking(): WebEventTrackingSettings
    {
        return WebEventTrackingSettings::createFromArray(
            $this->repository->getWebEventTracking($this->getScope()->getScopeId())
        );
    }
}
