<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Logger\Logger;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomerSaveAfterDataObject implements ObserverInterface
{
    private $logger;
    private $apiService;

    public function __construct(
        ApiService $apiService,
        Logger $logger
    ) {
        $this->apiService = $apiService;
        $this->logger = $logger;
    }

    public function execute(Observer $observer): self
    {
        try {
            /** @var CustomerInterface $customer */
            $customer = $observer->getCustomerDataObject();

            if (null === $customer) {
                $this->logger->addNotice('CustomerDataObject in observer is empty', [
                    'observerName' => $observer->getName(),
                    'eventName' => $observer->getEventName(),
                ]);

                return $this;
            }

            $scope = Scope::createFromStoreId($customer->getStoreId());

            $this->apiService->upsertCustomer($customer, $scope);
        } catch (Exception $e) {
            $this->logger->addError($e->getMessage(), ['exception' => $e]);
        }

        return $this;
    }
}
