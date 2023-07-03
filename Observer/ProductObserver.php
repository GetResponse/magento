<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Logger\Logger;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class ProductObserver implements ObserverInterface
{
    private $logger;
    private $repository;
    private $apiService;
    private $productRepository;

    public function __construct(
        Logger $logger,
        Repository $repository,
        ApiService $apiService,
        ProductRepositoryInterface $productRepository
    ) {
        $this->logger = $logger;
        $this->repository = $repository;
        $this->apiService = $apiService;
        $this->productRepository = $productRepository;
    }

    public function execute(EventObserver $observer): ProductObserver
    {
        try {
            $pluginMode = PluginMode::createFromRepository($this->repository->getPluginMode());
            if (!$pluginMode->isNewVersion()) {
                return $this;
            }

            if (null === $observer->getProduct()) {
                $this->logger->addNotice('Product in observer is empty', [
                    'observerName' => $observer->getName(),
                    'eventName' => $observer->getEventName(),
                ]);
                return $this;
            }

            /** @var Product $product */
            $product = $observer->getProduct();

            foreach ($product->getStoreIds() as $storeId) {
                $updatedProduct = $this->productRepository->getById($product->getId(), false, $storeId);
                $this->apiService->upsertProductCatalog($updatedProduct, new Scope($storeId));
            }

        } catch (Exception $e) {
            $this->logger->addError($e->getMessage(), ['exception' => $e]);
        }

        return $this;
    }
}
