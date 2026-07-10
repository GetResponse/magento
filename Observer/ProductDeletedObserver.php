<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Logger\Logger;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ProductDeletedObserver implements ObserverInterface
{
    /** @var Logger */
    private $logger;
    /** @var ApiService */
    private $apiService;

    /**
     * @param Logger $logger
     * @param ApiService $apiService
     */
    public function __construct(
        Logger $logger,
        ApiService $apiService
    ) {
        $this->logger = $logger;
        $this->apiService = $apiService;
    }

    /**
     * Handle execute.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer): self
    {
        try {
            $product = $observer->getProduct();

            $storeIds = $product->getWebsiteStoreIds();
            foreach ($storeIds as $storeId) {
                $this->apiService->deleteProduct($product, Scope::createFromStoreId($storeId));
            }
        } catch (Exception $e) {
            $this->logger->addError($e->getMessage(), ['exception' => $e]);
        }

        return $this;
    }
}
