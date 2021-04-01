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
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GrShareCode\Api\Exception\GetresponseApiException;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\Sales\Model\Order;

class CustomerSubscribedFromOrder implements ObserverInterface
{
    private $repository;
    private $contactService;
    private $contactCustomFieldsCollectionFactory;
    private $subscribeViaRegistrationService;
    private $magentoStore;
    private $customerReadModel;
    private $logger;
    private $apiService;
    private $subscriber;

    public function __construct(
        Repository $repository,
        ContactService $contactService,
        SubscribeViaRegistrationService $subscribeViaRegistrationService,
        ContactCustomFieldsCollectionFactory $contactCustomFieldsCollectionFactory,
        MagentoStore $magentoStore,
        CustomerReadModel $customerReadModel,
        Logger $logger,
        ApiService $apiService,
        Subscriber $subscriber
    ) {
        $this->repository = $repository;
        $this->contactService = $contactService;
        $this->contactCustomFieldsCollectionFactory = $contactCustomFieldsCollectionFactory;
        $this->subscribeViaRegistrationService = $subscribeViaRegistrationService;
        $this->magentoStore = $magentoStore;
        $this->customerReadModel = $customerReadModel;
        $this->logger = $logger;
        $this->apiService = $apiService;
        $this->subscriber = $subscriber;
    }

    public function execute(EventObserver $observer): CustomerSubscribedFromOrder
    {
        /** @var Order $order */
        $order = $observer->getOrder();

        if (empty($order->getCustomerId())) {
            return $this;
        }

        $subscriber = $this->subscriber->setStoreId($order->getStoreId())->loadByCustomerId($order->getCustomerId());

        if (!$subscriber->isSubscribed()) {
            return $this;
        }

        $scope = $this->magentoStore->getCurrentScope();
        $pluginMode = PluginMode::createFromRepository($this->repository->getPluginMode());

        try {
            if ($pluginMode->isNewVersion()) {
                $this->apiService->createCustomer($order->getCustomerId(), $scope);
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
                $this->magentoStore->getCurrentScope(),
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
