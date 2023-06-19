<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\CustomerData\Recommendation;

use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\Recommendation;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Framework\App\Request\Http;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\AbstractBlock as Subject;

abstract class RecommendationView
{
    protected $repository;
    protected $storeManager;
    protected $request;

    public function __construct(StoreManagerInterface $storeManager, Repository $repository, Http $request)
    {
        $this->storeManager = $storeManager;
        $this->repository = $repository;
        $this->request = $request;
    }

    abstract protected function getBlockName(): string;
    abstract protected function getFullActionName(): string;

    protected function isAllowed(Subject $subject): bool
    {
        $scopeId = $this->storeManager->getStore()->getId();

        if (!is_numeric($scopeId) || (int) $scopeId === 0) {
            return false;
        }

        $pluginMode = PluginMode::createFromRepository($this->repository->getPluginMode());
        $recommendation = Recommendation::createFromRepository($this->repository->getRecommendationSnippet($scopeId));

        return
            $pluginMode->isNewVersion()
            && $subject->getNameInLayout() === $this->getBlockName()
            && $recommendation->isActive()
            && $this->request->getFullActionName() === $this->getFullActionName();
    }
}
