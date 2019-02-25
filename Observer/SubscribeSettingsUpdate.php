<?php
namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactCustomFieldsCollectionFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistrationService;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GrShareCode\Api\Exception\GetresponseApiException;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class SubscribeUpdate
 * @package GetResponse\GetResponseIntegration\Observer
 */
class SubscribeSettingsUpdate implements ObserverInterface
{
    const UNSUBSCRIBE_EVENTS = [
        'adminhtml_customer_save_after',
        'customer_account_edited',
    ];

    /** @var ObjectManagerInterface */
    protected $_objectManager;

    /** @var Repository */
    private $repository;

    /** @var ContactService */
    private $contactService;

    /** @var SubscribeViaRegistrationService */
    private $subscribeViaRegistrationService;

    /** @var ContactCustomFieldsCollectionFactory */
    private $contactCustomFieldsCollectionFactory;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Repository $repository
     * @param ContactService $contactService
     * @param SubscribeViaRegistrationService $subscribeViaRegistrationService
     * @param ContactCustomFieldsCollectionFactory $contactCustomFieldsCollectionFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Repository $repository,
        ContactService $contactService,
        SubscribeViaRegistrationService $subscribeViaRegistrationService,
        ContactCustomFieldsCollectionFactory $contactCustomFieldsCollectionFactory
    ) {
        $this->_objectManager = $objectManager;
        $this->repository = $repository;
        $this->contactService = $contactService;
        $this->subscribeViaRegistrationService = $subscribeViaRegistrationService;
        $this->contactCustomFieldsCollectionFactory = $contactCustomFieldsCollectionFactory;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $registrationSettings = $this->subscribeViaRegistrationService->getSettings();

        if (!$registrationSettings->isEnabled()) {
            return $this;
        }

        $customerData = $observer->getEvent()->getCustomer();
        $subscriber = $this->repository->loadSubscriberByEmail($customerData->getEmail());

        if ($subscriber->isSubscribed()) {

            /** @var Customer $customer */
            $customer = $this->repository->loadCustomer($customerData->getId());

            $contactCustomFieldsCollection = $this->contactCustomFieldsCollectionFactory->createForCustomer(
                $customer,
                $this->subscribeViaRegistrationService->getCustomFieldMappingSettings(),
                $registrationSettings->isUpdateCustomFieldsEnalbed()
            );

            try {
                $this->contactService->addContact(
                    $customerData->getEmail(),
                    $customerData->getFirstname(),
                    $customerData->getLastname(),
                    $registrationSettings->getCampaignId(),
                    $registrationSettings->getCycleDay(),
                    $contactCustomFieldsCollection,
                    $registrationSettings->isUpdateCustomFieldsEnalbed()
                );
            } catch (ApiException $e) {
            } catch (GetresponseApiException $e) {
            }
        } elseif (
            false === $subscriber->isSubscribed()
            && in_array($observer->getEvent()->getName(), self::UNSUBSCRIBE_EVENTS)
        ) {
            try {
                $this->contactService->removeContact($customerData->getEmail());
            } catch (ApiException $e) {
            } catch (GetresponseApiException $e) {
            }
        }

        return $this;
    }
}
