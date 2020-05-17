<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Block\Admin;

use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTrackingSettings;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTrackingSettingsFactory;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use Magento\Framework\View\Element\Template\Context;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;

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
    }

    public function getWebEventTracking(): WebEventTrackingSettings
    {
        return WebEventTrackingSettingsFactory::createFromArray(
            $this->repository->getWebEventTracking($this->getScopeId())
        );
    }
}
