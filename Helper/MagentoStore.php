<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Helper;

use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;

class MagentoStore extends AbstractHelper
{
    private $storeManager;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
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

    public function storeExists(int $storeId): bool
    {
        $stores = $this->getMagentoStores();

        foreach ($stores as $id => $name) {
            if ($storeId === (int) $id) {
                return true;
            }
        }

        return false;
    }

    public function getCurrentScope(): Scope
    {
        return new Scope($this->storeManager->getStore()->getId());
    }
}
