<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\Application\Command\AddContact;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\Application\ContactService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactCustomFieldsCollectionFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistrationService;
use GetResponse\GetResponseIntegration\Domain\Magento\Customer\ReadModel\CustomerReadModel;
use GetResponse\GetResponseIntegration\Domain\Magento\Customer\ReadModel\Query\CustomerEmail;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GrShareCode\Api\Exception\GetresponseApiException;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Newsletter\Model\Subscriber;

class CustomerSubscribedDuringRegistration implements ObserverInterface
{
    private $contactService;
    private $subscribeViaRegistrationService;
    private $contactCustomFieldsCollectionFactory;
    private $magentoStore;
    private $customerReadModel;

    public function __construct(
        ContactService $contactService,
        SubscribeViaRegistrationService $subscribeViaRegistrationService,
        ContactCustomFieldsCollectionFactory $contactCustomFieldsCollectionFactory,
        MagentoStore $magentoStore,
        CustomerReadModel $customerReadModel
    ) {
        $this->contactService = $contactService;
        $this->subscribeViaRegistrationService = $subscribeViaRegistrationService;
        $this->contactCustomFieldsCollectionFactory = $contactCustomFieldsCollectionFactory;
        $this->magentoStore = $magentoStore;
        $this->customerReadModel = $customerReadModel;
    }

    public function execute(Observer $observer)
    {
        $scope = $this->magentoStore->getCurrentScope();
        /** @var Subscriber $subscriber */
        $subscriber = $observer->getEvent()->getSubscriber();

        try {
            $registrationSettings = $this->subscribeViaRegistrationService->getSettings($scope);

            if (!$registrationSettings->isEnabled()) {
                return $this;
            }

            if (!$subscriber->isSubscribed()) {
                return $this;
            }

            $customer = $this->customerReadModel->getCustomerByEmail(
                new CustomerEmail($subscriber->getSubscriberEmail(), $scope)
            );

            if ($customer->isEmpty()) {
                return $this;
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
        } catch (ApiException $e) {
        } catch (GetresponseApiException $e) {
        }

        return $this;
    }
}
