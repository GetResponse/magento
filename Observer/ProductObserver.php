<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Logger\Logger;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class ProductObserver implements ObserverInterface
{
    private $logger;
    private $repository;
    private $apiService;

    public function __construct(Logger $getResponseLogger, Repository $repository, ApiService $apiService)
    {
        $this->logger = $getResponseLogger;
        $this->repository = $repository;
        $this->apiService = $apiService;
    }

    public function execute(EventObserver $observer): ProductObserver
    {
        /** @var Product $product */
        $product = $observer->getProduct();
        $websiteIds = $product->getWebsiteIds();

        foreach ($websiteIds as $websiteId) {
            try {
                $pluginMode = PluginMode::createFromRepository($this->repository->getPluginMode());

                if ($pluginMode->isNewVersion()) {
                    $this->apiService->createProduct($product, new Scope($websiteId));
                }
            } catch (Exception $e) {
                $this->logger->addError($e->getMessage(), ['exception' => $e]);
            }
        }
        return $this;
    }
}
