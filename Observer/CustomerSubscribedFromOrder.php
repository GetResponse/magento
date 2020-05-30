<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\Application\Command\AddContact;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\Application\ContactService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactCustomFieldsCollectionFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistrationFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistrationService;
use GetResponse\GetResponseIntegration\Domain\Magento\Customer\ReadModel\CustomerReadModel;
use GetResponse\GetResponseIntegration\Domain\Magento\Customer\ReadModel\Query\CustomerId;
use GetResponse\GetResponseIntegration\Domain\Magento\Order\ReadModel\OrderReadModel;
use GetResponse\GetResponseIntegration\Domain\Magento\Order\ReadModel\Query\GetOrder;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\Subscriber\ReadModel\Query\SubscriberEmail;
use GetResponse\GetResponseIntegration\Domain\Magento\Subscriber\ReadModel\SubscriberReadModel;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GrShareCode\Api\Exception\GetresponseApiException;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Newsletter\Model\Subscriber;

class CustomerSubscribedFromOrder implements ObserverInterface
{
    private $repository;
    private $contactService;
    private $contactCustomFieldsCollectionFactory;
    private $subscribeViaRegistrationService;
    private $magentoStore;
    private $subscriberReadModel;
    private $customerReadModel;
    private $orderReadModel;

    public function __construct(
        Repository $repository,
        ContactService $contactService,
        SubscribeViaRegistrationService $subscribeViaRegistrationService,
        ContactCustomFieldsCollectionFactory $contactCustomFieldsCollectionFactory,
        MagentoStore $magentoStore,
        SubscriberReadModel $subscriberReadModel,
        CustomerReadModel $customerReadModel,
        OrderReadModel $orderReadModel
    ) {
        $this->repository = $repository;
        $this->contactService = $contactService;
        $this->contactCustomFieldsCollectionFactory = $contactCustomFieldsCollectionFactory;
        $this->subscribeViaRegistrationService = $subscribeViaRegistrationService;
        $this->magentoStore = $magentoStore;
        $this->subscriberReadModel = $subscriberReadModel;
        $this->customerReadModel = $customerReadModel;
        $this->orderReadModel = $orderReadModel;
    }

    public function execute(EventObserver $observer)
    {
        $scope = $this->magentoStore->getCurrentScope();
        $registrationSettings = SubscribeViaRegistrationFactory::createFromArray(
            $this->repository->getRegistrationSettings($scope->getScopeId())
        );

        if (!$registrationSettings->isEnabled()) {
            return $this;
        }

        $orderIds = $observer->getOrderIds();
        $orderId = (int)(is_array($orderIds) ? array_pop($orderIds) : $orderIds);

        if ($orderId < 1) {
            return $this;
        }

        $order = $this->orderReadModel->getOrder(new GetOrder($orderId));

        $customerEmail = $order->getCustomerEmail();
        $customerId = $order->getCustomerId();

        if (null === $customerEmail) {
            return $this;
        }

        /** @var Subscriber $subscriber */
        $subscriber = $this->subscriberReadModel->loadSubscriberByEmail(
            new SubscriberEmail($customerEmail)
        );

        if (!$subscriber->isSubscribed()) {
            return $this;
        }

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

        try {
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
        } catch (ApiException $e) {
        } catch (GetresponseApiException $e) {
        }

        return $this;
    }
}
