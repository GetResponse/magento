<?php

namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\GetresponseApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsException;
use GetResponse\GetResponseIntegration\Domain\Magento\RegistrationSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GrShareCode\Api\ApiTypeException;
use GrShareCode\Contact\ContactCustomField;
use GrShareCode\Contact\ContactCustomFieldsCollection;
use GrShareCode\GetresponseApiException;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class SubscribeFromOrder
 * @package GetResponse\GetResponseIntegration\Observer
 */
class SubscribeFromOrder implements ObserverInterface
{
    /** @var ObjectManagerInterface */
    protected $_objectManager;

    /** @var GetresponseApiClientFactory */
    private $apiClientFactory;

    /** @var Repository */
    private $repository;

    /** @var ContactService */
    private $contactService;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param GetresponseApiClientFactory $apiClientFactory
     * @param Repository $repository
     * @param ContactService $contactService
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        GetresponseApiClientFactory $apiClientFactory,
        Repository $repository,
        ContactService $contactService
    ) {
        $this->_objectManager = $objectManager;
        $this->apiClientFactory = $apiClientFactory;
        $this->repository = $repository;
        $this->contactService = $contactService;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $registrationSettings = RegistrationSettingsFactory::createFromArray(
            $this->repository->getRegistrationSettings()
        );

        if (!$registrationSettings->isEnabled()) {
            return $this;
        }

        $orderIds = $observer->getOrderIds();
        $orderId = (int)(is_array($orderIds) ? array_pop($orderIds) : $orderIds);

        $customFields = $this->prepareCustomFields($orderId);

        if ($orderId < 1) {
            return $this;
        }

        $order = $this->repository->loadOrder($orderId);
        $customer = $this->repository->loadCustomer($order->getCustomerId());
        $subscriber = $this->repository->loadSubscriberByEmail($customer->getEmail());

        if (!$subscriber->isSubscribed()) {
            return $this;
        }

        $this->addContact(
            $registrationSettings->getCampaignId(),
            $customer->getFirstname(),
            $customer->getLastname(),
            $customer->getEmail(),
            $registrationSettings->getCycleDay(),
            $customFields
        );

        return $this;
    }


    /**
     * @param string $campaign
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param null|int $cycleDay
     * @param array $user_customs
     */
    public function addContact($campaign, $firstName, $lastName, $email, $cycleDay = null, $user_customs = [])
    {
        try {
            $grApiClient = $this->apiClientFactory->createGetResponseApiClient();
            $customFields = new ContactCustomFieldsCollection();

            foreach ($user_customs as $name => $value) {
                $custom = $grApiClient->getCustomFieldByName($name);

                if (!empty($custom)) {
                    $customFields->add(new ContactCustomField($custom['customFieldId'], $value));
                }
            }

            $this->contactService->createContact(
                $email,
                $firstName,
                $lastName,
                $campaign,
                $cycleDay,
                $customFields
            );

        } catch (RepositoryException $e) {
        } catch (ApiTypeException $e) {
        } catch (GetresponseApiException $e) {
        } catch (ConnectionSettingsException $e) {
        }
    }

    /**
     * @param int $orderId
     * @return array
     */
    private function prepareCustomFields($orderId)
    {
        $customFields = [];
        $customs = $this->repository->getCustoms();

        $order = $this->repository->loadOrder($orderId);
        $customer = $this->repository->loadCustomer($order->getCustomerId());
        $address = $this->repository->loadCustomerAddress($customer->getDefaultBilling());
        $data = array_merge($address->getData(), $customer->getData());

        foreach ($customs as $custom) {
            if ($custom->isDefault) {
                continue;
            }

            if ($custom->customField == 'birthday') {
                $custom->customField = 'dob';
            }
            if ($custom->customField == 'country') {
                $custom->customField = 'country_id';
            }

            if (!empty($data[$custom->customField])) {
                $customFields[$custom->customName] = $data[$custom->customField];
            }
        }

        return $customFields;
    }
}
