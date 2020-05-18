<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Block\Admin;

use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Helper\Config;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\ContactList\Autoresponder;
use GrShareCode\ContactList\ContactListService;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class AdminTemplate extends Template
{
    protected $magentoStore;
    protected $apiClient;

    public function __construct(
        Context $context,
        MagentoStore $magentoStore
    ) {
        parent::__construct($context);
        $this->magentoStore = $magentoStore;
    }

    /**
     * @return array
     * @throws GetresponseApiException
     */
    public function getAutoRespondersForFrontend(): array
    {
        $result = [];

        $service = new ContactListService($this->apiClient);
        $responders = $service->getAutoresponders();

        /** @var Autoresponder $responder */
        foreach ($responders as $responder) {
            $result[$responder->getCampaignId()][$responder->getId()] = [
                'name' => $responder->getName(),
                'subject' => $responder->getSubject(),
                'dayOfCycle' => $responder->getCycleDay()
            ];
        }

        return $result;
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
}
