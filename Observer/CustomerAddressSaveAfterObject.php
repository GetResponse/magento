<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\Application\Command\AddContact;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\Application\ContactService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactCustomFieldsCollectionFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistrationService;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GrShareCode\Api\Exception\GetresponseApiException;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\Data\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomerAddressSaveAfterObject implements ObserverInterface
{
    private $contactService;
    private $subscribeViaRegistrationService;
    private $contactCustomFieldsCollectionFactory;
    private $magentoStore;
    private $logger;
    private $repository;
    private $apiService;

    public function __construct(
        ContactService $contactService,
        SubscribeViaRegistrationService $subscribeViaRegistrationService,
        ContactCustomFieldsCollectionFactory $contactCustomFieldsCollectionFactory,
        MagentoStore $magentoStore,
        Logger $logger,
        Repository $repository,
        ApiService $apiService
    ) {
        $this->contactService = $contactService;
        $this->subscribeViaRegistrationService = $subscribeViaRegistrationService;
        $this->contactCustomFieldsCollectionFactory = $contactCustomFieldsCollectionFactory;
        $this->magentoStore = $magentoStore;
        $this->logger = $logger;
        $this->repository = $repository;
        $this->apiService = $apiService;
    }

    public function execute(Observer $observer): CustomerAddressSaveAfterObject
    {
        try {
            $pluginMode = PluginMode::createFromRepository($this->repository->getPluginMode());
            if (!$pluginMode->isNewVersion()) {
                return $this;
            }

            $scope = $this->magentoStore->getCurrentScope();
            $customerAddress = $observer->getCustomerAddress();
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

    /**
     * @throws ApiException
     * @throws GetresponseApiException
     */
    private function handleOldVersion(Customer $customer, Scope $scope): void
    {
        $registrationSettings = $this->subscribeViaRegistrationService->getSettings($scope);

        if (!$registrationSettings->isEnabled()) {
            return;
        }

        $contactCustomFieldsCollection = $this->contactCustomFieldsCollectionFactory->createForCustomer(
            $customer,
            $this->subscribeViaRegistrationService->getCustomFieldMappingSettings($scope),
            $registrationSettings->isUpdateCustomFieldsEnalbed()
        );

        $this->contactService->addContact(
            AddContact::createFromCustomer(
                $customer,
                $registrationSettings,
                $contactCustomFieldsCollection,
                $scope
            )
        );
    }
}
