<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Logger\Logger;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use Exception;

class ProductDeletionObserver implements ObserverInterface
{
    private $logger;
    private $apiService;
    private $repository;

    public function __construct(
        Logger $logger,
        ApiService $apiService,
        Repository $repository
    )
    {
        $this->logger = $logger;
        $this->apiService = $apiService;
        $this->repository = $repository;
    }

    public function execute(Observer $observer)
    {
        try {
            $pluginMode = PluginMode::createFromRepository($this->repository->getPluginMode());
            if (!$pluginMode->isNewVersion()) {
                return $this;
            }

            /** @var Product $product */
            $product = $observer->getProduct();

            $storeIds = $product->getWebsiteStoreIds();
            foreach ($storeIds as $storeId) {
                $this->apiService->deleteProduct($product, new Scope((int)$storeId));
            }
        } catch (Exception $e) {
            $this->logger->addError($e->getMessage(), ['exception' => $e]);
        }

        return $this;
    }
}
