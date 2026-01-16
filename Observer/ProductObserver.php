<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Logger\Logger;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class ProductObserver implements ObserverInterface
{
    private $logger;
    private $apiService;
    private $productRepository;

    public function __construct(
        Logger $logger,
        ApiService $apiService,
        ProductRepositoryInterface $productRepository
    ) {
        $this->logger = $logger;
        $this->apiService = $apiService;
        $this->productRepository = $productRepository;
    }

    public function execute(EventObserver $observer): self
    {
        try {
            /** @var Product $product */
            $product = $observer->getProduct();

            if (null === $product) {
                $this->logger->addNotice('Product in observer is empty', [
                    'observerName' => $observer->getName(),
                    'eventName' => $observer->getEventName(),
                ]);
                return $this;
            }

            foreach ($product->getStoreIds() as $storeId) {
                /** @var Product $updatedProduct */
                $updatedProduct = $this->productRepository->getById($product->getId(), false, $storeId);
                $this->apiService->upsertProductCatalog($updatedProduct, Scope::createFromStoreId($storeId));
            }
        } catch (Exception $e) {
            $this->logger->addError($e->getMessage(), ['exception' => $e]);
        }

        return $this;
    }
}
