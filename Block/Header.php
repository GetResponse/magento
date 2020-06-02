<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTrackingSettingsFactory;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Header extends Template
{
    private $repository;
    private $magentoStore;

    public function __construct(
        Context $context,
        Repository $repository,
        MagentoStore $magentoStore
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->magentoStore = $magentoStore;
    }

    public function getTrackingData(): array
    {
        $trackingCodeSnippet = $this->getTrackingCodeSnippet();

        return [
            'trackingCodeSnippet' => $trackingCodeSnippet
        ];
    }

    private function getTrackingCodeSnippet(): string
    {
        $webEventTracking = WebEventTrackingSettingsFactory::createFromArray(
            $this->repository->getWebEventTracking(
                $this->magentoStore->getCurrentScope()->getScopeId()
            )
        );

        if ($webEventTracking->isEnabled()) {
            return $webEventTracking->getCodeSnippet();
        }

        return '';
    }

}
