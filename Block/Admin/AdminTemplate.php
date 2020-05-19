<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Block\Admin;

use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Helper\Config;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class AdminTemplate extends Template
{
    protected $magentoStore;
    protected $apiClient;
    protected $routePrefix;

    public function __construct(
        Context $context,
        MagentoStore $magentoStore
    ) {
        parent::__construct($context);
        $this->magentoStore = $magentoStore;
    }

    public function getMagentoStores(): array
    {
        return $this->magentoStore->getMagentoStores();
    }

    public function getScope(): Scope
    {
        return new Scope($this->magentoStore->getStoreIdFromUrl());
    }

    public function getUrlWithScope($route = '', $params = []): string
    {
        $scopeId = $this->getScope()->getScopeId();

        if ($scopeId !== null) {
            $route .= '/' . Config::SCOPE_TAG . '/' . $scopeId;
        }

        return $this->getUrl($route, $params);
    }

    public function getPageUrlForScope(int $scope): string
    {
        return $this->getUrl($this->routePrefix . '/' . Config::SCOPE_TAG . '/' .  $scope);
    }
}
