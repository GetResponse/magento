<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Logger\Logger;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ProductDeletedObserver implements ObserverInterface
{
    private $logger;
    private $apiService;

    public function __construct(
        Logger $logger,
        ApiService $apiService
    ) {
        $this->logger = $logger;
        $this->apiService = $apiService;
    }

    public function execute(Observer $observer): self
    {
        try {
            /** @var Product $product && @phpstan-ignore-next-line */
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
