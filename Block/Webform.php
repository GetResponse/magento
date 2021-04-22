<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\WebForm as WebFormSettings;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Webform extends Template
{
    private $magentoStore;
    private $repository;

    public function __construct(
        Context $context,
        MagentoStore $magentoStore,
        Repository $repository
    ) {
        parent::__construct($context);
        $this->magentoStore = $magentoStore;
        $this->repository = $repository;
    }

    public function getWebFormUrlToDisplay(string $placement): ?string
    {
        $scope = $this->magentoStore->getCurrentScope();

        $webForm = WebFormSettings::createFromRepository(
            $this->repository->getWebformSettings($scope->getScopeId())
        );

        if (!$webForm->isEnabled() || $webForm->getSidebar() !== $placement) {
            return null;
        }

        return $webForm->getUrl();
    }
}
