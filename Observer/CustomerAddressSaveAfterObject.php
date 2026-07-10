<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Logger\Logger;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomerAddressSaveAfterObject implements ObserverInterface
{
    /** @var Logger */
    private $logger;
    /** @var ApiService */
    private $apiService;

    /**
     * @param Logger $logger
     * @param ApiService $apiService
     */
    public function __construct(Logger $logger, ApiService $apiService)
    {
        $this->logger = $logger;
        $this->apiService = $apiService;
    }

    /**
     * Handle execute.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer): CustomerAddressSaveAfterObject
    {
        try {
            $customerAddress = $observer->getCustomerAddress();

            if (null === $customerAddress) {
                $this->logger->addNotice('CustomerAddress in observer is empty', [
                    'observerName' => $observer->getName(),
                    'eventName' => $observer->getEventName(),
                ]);
                return $this;
            }

            $scope = Scope::createFromStoreId($customerAddress->getStoreId());
            /** @var AddressInterface $address */
            $address = $customerAddress->getDataModel();

            if ($address->isDefaultBilling() || $address->isDefaultShipping()) {
                $this->apiService->upsertCustomerAddress($address, $scope);
                return $this;
            }

            $isDefaultBilling = $customerAddress->getData('is_default_billing');
            $isDefaultShipping = $customerAddress->getData('is_default_shipping');

            if ($isDefaultBilling || $isDefaultShipping) {
                $address->setIsDefaultBilling($isDefaultBilling);
                $address->setIsDefaultShipping($isDefaultShipping);
                $this->apiService->upsertCustomerAddress($address, $scope);
                return $this;
            }
        } catch (Exception $e) {
            $this->logger->addError($e->getMessage(), ['exception' => $e]);
        }

        return $this;
    }
}
