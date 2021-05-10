<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\Application\Command\AddContact;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\Application\ContactService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactCustomFieldsCollectionFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistrationFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistrationService;
use GetResponse\GetResponseIntegration\Domain\Magento\Customer\ReadModel\CustomerReadModel;
use GetResponse\GetResponseIntegration\Domain\Magento\Customer\ReadModel\Query\CustomerId;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GrShareCode\Api\Exception\GetresponseApiException;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class CustomerSubscribedFromOrder implements ObserverInterface
{
    private $repository;
    private $contactService;
    private $contactCustomFieldsCollectionFactory;
    private $subscribeViaRegistrationService;
    private $customerReadModel;
    private $logger;
    private $apiService;

    public function __construct(
        Repository $repository,
        ContactService $contactService,
        SubscribeViaRegistrationService $subscribeViaRegistrationService,
        ContactCustomFieldsCollectionFactory $contactCustomFieldsCollectionFactory,
        CustomerReadModel $customerReadModel,
        Logger $logger,
        ApiService $apiService
    ) {
        $this->repository = $repository;
        $this->contactService = $contactService;
        $this->contactCustomFieldsCollectionFactory = $contactCustomFieldsCollectionFactory;
        $this->subscribeViaRegistrationService = $subscribeViaRegistrationService;
        $this->customerReadModel = $customerReadModel;
        $this->logger = $logger;
        $this->apiService = $apiService;
    }

    public function execute(EventObserver $observer): CustomerSubscribedFromOrder
    {
        try {
            /** @var Order $order */
            $order = $observer->getOrder();

            $customerId = $order->getCustomerId();
            if (empty($customerId)) {
                return $this;
            }

            $scope = new Scope($order->getStoreId());
            $pluginMode = PluginMode::createFromRepository($this->repository->getPluginMode());

            if ($pluginMode->isNewVersion()) {
                $customer = $this->customerReadModel->getCustomerById(new CustomerId($customerId));
                $this->apiService->upsertCustomer($customer, $scope);
            } else {
                $this->handleOldVersion($order, $scope);
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
    private function handleOldVersion(Order $order, Scope $scope): void
    {
        $registrationSettings = SubscribeViaRegistrationFactory::createFromArray(
            $this->repository->getRegistrationSettings($scope->getScopeId())
        );

        if (!$registrationSettings->isEnabled()) {
            return;
        }

        $customerEmail = $order->getCustomerEmail();
        $customerId = $order->getCustomerId();

        if (null !== $customerId) {
            $customer = $this->customerReadModel->getCustomerById(
                new CustomerId($customerId)
            );

            $contactCustomFieldsCollection = $this->contactCustomFieldsCollectionFactory->createForCustomer(
                $customer,
                $this->subscribeViaRegistrationService->getCustomFieldMappingSettings(
                    $scope
                ),
                $registrationSettings->isUpdateCustomFieldsEnalbed()
            );
        } else {
            $contactCustomFieldsCollection = $this->contactCustomFieldsCollectionFactory->createForSubscriber();
        }

        $this->contactService->addContact(
            new AddContact(
                new Scope($order->getStoreId()),
                $customerEmail,
                $order->getCustomerFirstname(),
                $order->getCustomerLastname(),
                $registrationSettings->getCampaignId(),
                $registrationSettings->getCycleDay(),
                $contactCustomFieldsCollection,
                $registrationSettings->isUpdateCustomFieldsEnalbed()
            )
        );
    }
}
