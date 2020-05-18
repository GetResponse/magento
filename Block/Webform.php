<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Account\ReadModel\AccountReadModel;
use GetResponse\GetResponseIntegration\Domain\Magento\WebformSettingsFactory;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;

class Webform extends Template
{
    private $accountReadModel;
    private $magentoStore;
    private $repository;

    public function __construct(
        Context $context,
        MagentoStore $magentoStore,
        AccountReadModel $accountReadModel,
        Repository $repository
    ) {
        parent::__construct($context);
        $this->magentoStore = $magentoStore;
        $this->accountReadModel = $accountReadModel;
        $this->repository = $repository;
    }

    public function getWebFormUrlToDisplay(string $placement)
    {
        $scope = $this->magentoStore->getCurrentScope();

        if (!$this->accountReadModel->isConnected($scope)) {
            return null;
        }

        $webForm = WebformSettingsFactory::createFromArray(
            $this->repository->getWebformSettings($scope->getScopeId())
        );

        if (!$webForm->isEnabled() || $webForm->getSidebar() !== $placement) {
            return null;
        }

        return $webForm->getUrl();
    }
}
