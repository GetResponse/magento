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
use GetResponse\GetResponseIntegration\Logger\Logger;
use GrShareCode\Api\Exception\GetresponseApiException;
use Magento\Customer\Model\Data\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomerSaveAfterDataObject implements ObserverInterface
{
    private $contactService;
    private $subscribeViaRegistrationService;
    private $contactCustomFieldsCollectionFactory;
    private $logger;
    private $repository;
    private $apiService;

    public function __construct(
        ContactService $contactService,
        SubscribeViaRegistrationService $subscribeViaRegistrationService,
        ContactCustomFieldsCollectionFactory $contactCustomFieldsCollectionFactory,
        Repository $repository,
        ApiService $apiService,
        Logger $logger
    ) {
        $this->contactService = $contactService;
        $this->subscribeViaRegistrationService = $subscribeViaRegistrationService;
        $this->contactCustomFieldsCollectionFactory = $contactCustomFieldsCollectionFactory;
        $this->repository = $repository;
        $this->apiService = $apiService;
        $this->logger = $logger;
    }

    public function execute(Observer $observer): CustomerSaveAfterDataObject
    {
        try {
            $pluginMode = PluginMode::createFromRepository($this->repository->getPluginMode());

            if (null === $observer->getCustomerDataObject()) {
                $this->logger->addNotice('CustomerDataObject in observer is empty', [
                    'observerName' => $observer->getName(),
                    'eventName' => $observer->getEventName(),
                ]);
                return $this;
            }

            $customer = $observer->getCustomerDataObject();
            $scope = new Scope($customer->getStoreId());

            if (!$pluginMode->isNewVersion()) {
                $this->handleOldVersion($customer, $scope);

                return $this;
            }

            $this->apiService->upsertCustomer($customer, $scope);
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
