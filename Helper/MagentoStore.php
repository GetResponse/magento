<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Helper;

use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

class MagentoStore extends AbstractHelper
{
    private $storeManager;
    private $request;
    private $session;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Http $request,
        SessionManagerInterface $session
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->session = $session;
    }


    public function getMagentoStores(): array
    {
        $allStores = [];
        $stores = $this->storeManager->getStores();

        foreach ($stores as $store) {
            $allStores[$store->getId()] = $store->getName();
        }

        return $allStores;
    }

    public function getStoreIdFromSession()
    {
        return $this->session->getGrScope();
    }

    public function getDefaultStoreId(): int
    {
        return (int) $this->storeManager->getDefaultStoreView()->getId();
    }

    public function getStoreIdFromUrl()
    {
        $storeId = $this->request->get(Config::SCOPE_TAG);
        return null !== $storeId ? (int)$storeId : null;
    }

    public function getCurrentScope(): Scope
    {
        return new Scope($this->storeManager->getStore()->getId());
    }

    public function shouldRedirectToStore(): bool
    {
        $storeId = $this->request->get(Config::SCOPE_TAG);
        $storeInSession = $this->session->getGrScope();

        if (null === $storeId && null === $storeInSession) {
            $storeId = $this->getDefaultStoreId();
        }

        if (null !== $storeId && $storeId !== $storeInSession) {
            $this->session->setGrScope($storeId);
        }

        return $storeInSession !== $storeId;
    }
}
