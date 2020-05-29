<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\Application\Command\AddContact;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\Application\ContactService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactCustomFieldsCollectionFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistrationService;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GrShareCode\Api\Exception\GetresponseApiException;
use Magento\Customer\Model\Data\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Newsletter\Model\Subscriber;

class CustomerSubscribedDuringRegistration implements ObserverInterface
{
    private $contactService;
    private $subscribeViaRegistrationService;
    private $contactCustomFieldsCollectionFactory;
    private $magentoStore;
    private $subscriber;

    public function __construct(
        ContactService $contactService,
        SubscribeViaRegistrationService $subscribeViaRegistrationService,
        ContactCustomFieldsCollectionFactory $contactCustomFieldsCollectionFactory,
        MagentoStore $magentoStore,
        Subscriber $subscriber
    ) {
        $this->contactService = $contactService;
        $this->subscribeViaRegistrationService = $subscribeViaRegistrationService;
        $this->contactCustomFieldsCollectionFactory = $contactCustomFieldsCollectionFactory;
        $this->magentoStore = $magentoStore;
        $this->subscriber = $subscriber;
    }

    public function execute(Observer $observer)
    {
        $scope = $this->magentoStore->getCurrentScope();
        /** @var Customer $customer */
        $customer = $observer->getCustomer();

        $checkSubscriber = $this->subscriber->loadByCustomerId($customer->getId());

        if (!$checkSubscriber->isSubscribed()) {
            return $this;
        }

        try {
            $registrationSettings = $this->subscribeViaRegistrationService->getSettings($scope);

            if (!$registrationSettings->isEnabled()) {
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
