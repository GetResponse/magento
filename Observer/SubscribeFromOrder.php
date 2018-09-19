<?php

namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\Config;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RulesCollectionFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\RegistrationSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GrShareCode\Api\ApiTypeException;
use GrShareCode\Contact\AddContactCommand;
use GrShareCode\Contact\ContactCustomField;
use GrShareCode\Contact\ContactCustomFieldsCollection;
use GrShareCode\Contact\ContactService;
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

    /** @var RepositoryFactory */
    private $repositoryFactory;

    /** @var Repository */
    private $repository;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param RepositoryFactory $repositoryFactory
     * @param Repository $repository
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        RepositoryFactory $repositoryFactory,
        Repository $repository
    ) {
        $this->_objectManager = $objectManager;
        $this->repositoryFactory = $repositoryFactory;
        $this->repository = $repository;
    }

    /**
     * Save order into registry to use it in the overloaded controller.
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $moveSubscriber = false;
        $registrationSettings = RegistrationSettingsFactory::createFromArray(
            $this->repository->getRegistrationSettings()
        );

        if (!$registrationSettings->isEnabled()) {
            return $this;
        }

        try {
            $grApiClient = $this->repositoryFactory->createGetResponseApiClient();
            $rules = RulesCollectionFactory::createFromRepository(
                $this->repository->getRules()
            );

            $orderIds = $observer->getOrderIds();
            $orderId = (int)(is_array($orderIds) ? array_pop($orderIds) : $orderIds);

            $customFields  = $this->prepareCustomFields($orderId);

            if ($orderId < 1) {
                return $this;
            }

            $order = $this->repository->loadOrder($orderId);
            $customer = $this->repository->loadCustomer($order->getCustomerId());
            $subscriber = $this->repository->loadSubscriberByEmail($customer->getEmail());

            if (!$subscriber->isSubscribed()) {
                return $this;
            }
            if (!empty($rules->getRules())) {
                $categoryIds = [];
                foreach ($rules->getRules() as $rule) {
                    $categoryIds[$rule->getCategory()] = [
                        'category_id' => $rule->getCategory(),
                        'action' => $rule->getAction(),
                        'campaign_id' => $rule->getCampaign(),
                        'cycle_day' => $rule->getAutoresponderDay()
                    ];
                }

                $automationCategories = array_keys($categoryIds);

                foreach ($order->getItems() as $item) {

                    $productId = $item->getData()['product_id'];
                    $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId);
                    $category = array_intersect($product->getCategoryIds(), $automationCategories);

                    if (!empty($category)) {
                        foreach ($category as $c) {

                            $this->addContact(
                                $categoryIds[$c]['campaign_id'],
                                $customer->getFirstname(),
                                $customer->getLastname(),
                                $customer->getEmail(),
                                $categoobryIds[$c]['cycle_day'],
                                $customFields
                            );

                            if ($categoryIds[$c]['action'] == 'move') {
                                $moveSubscriber = true;
                                $contact = $grApiClient->getContactByEmail(
                                    $customer->getEmail(),
                                    $registrationSettings->getCampaignId()
                                );

                                if (isset($contact['contactId'])) {
                                    $grApiClient->deleteContact($contact['contactId']);
                                }
                            }
                        }
                    }
                }
            }
            if (!$moveSubscriber) {
                $this->addContact(
                    $registrationSettings->getCampaignId(),
                    $customer->getFirstname(),
                    $customer->getLastname(),
                    $customer->getEmail(),
                    $registrationSettings->getCycleDay(),
                    $customFields
                );
            }

            return $this;
        } catch (RepositoryException $e) {
            return $this;
        } catch (ApiTypeException $e) {
            return $this;
        } catch (GetresponseApiException $e) {
            return $this;
        }
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
            $grApiClient = $this->repositoryFactory->createGetResponseApiClient();
            $customFields = new ContactCustomFieldsCollection();

            foreach ($user_customs as $name => $value) {
                $custom = $grApiClient->getCustomFieldByName($name);

                if (!empty($custom)) {
                    $customFields->add(new ContactCustomField($custom['customFieldId'], $value));
                }
            }

            $service = new ContactService($grApiClient);
            $service->upsertContact(new AddContactCommand(
                $email,
                trim($firstName) . ' ' . trim($lastName),
                $campaign,
                $cycleDay,
                $customFields,
                Config::ORIGIN_NAME
            ));

        } catch (RepositoryException $e) {
        } catch (ApiTypeException $e) {
        } catch (GetresponseApiException $e) {
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
        $customer = $this->repository->loadCustomer( $order->getCustomerId());
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
