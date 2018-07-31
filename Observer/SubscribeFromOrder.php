<?php
namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RulesCollectionFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\RegistrationSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\ApiHelper;
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
        $registrationSettings = RegistrationSettingsFactory::createFromArray(
            $this->repository->getRegistrationSettings()
        );

        if (!$registrationSettings->isEnabled()) {
            return $this;
        }

        try {
            $grRepository = $this->repositoryFactory->createRepository();
        } catch (RepositoryException $e) {
            return $this;
        }

        $rulesCollection = RulesCollectionFactory::createFromRepository($this->repository->getRules());

        $customs = $this->repository->getCustoms();

        $order_id = $observer->getOrderIds();
        $order_id = (int)(is_array($order_id) ? array_pop($order_id) : $order_id);

        if ($order_id < 1) {
            return $this;
        }

        $order = $this->repository->loadOrder($order_id);

        $customer_id = $order->getCustomerId();
        $customer = $this->repository->loadCustomer($customer_id);

        $address = $this->repository->loadCustomerAddress($customer->getDefaultBilling());

        $data = array_merge($address->getData(), $customer->getData());

        $custom_fields = [];

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
                $custom_fields[$custom->customName] = $data[$custom->customField];
            }
        }

        $subscriber = $this->repository->loadSubscriberByEmail($customer->getEmail());

        if ($subscriber->isSubscribed() == true) {
            $move_subscriber = false;

            if (!empty($rulesCollection->getRules())) {
                $category_ids = [];
                foreach ($rulesCollection->getRules() as $rule) {
                    $category_ids[$rule->getCategory()] = [
                        'category_id' => $rule->getCategory(),
                        'action' => $rule->getAction(),
                        'campaign_id' => $rule->getCampaign(),
                        'cycle_day' => $rule->getAutoresponder()
                    ];
                }

                $automations_categories = array_keys($category_ids);

                foreach ($order->getItems() as $item) {
                    $product_id = $item->getData()['product_id'];
                    $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
                    $product_categories = $product->getCategoryIds();

                    $category = array_intersect($product_categories, $automations_categories);
                    if ($category) {
                        foreach ($category as $c) {
                            if ($category_ids[$c]['action'] == 'move') {
                                $move_subscriber = true;
                            }

                            $this->addContact(
                                $category_ids[$c]['campaign_id'],
                                $customer->getFirstname(),
                                $customer->getLastname(),
                                $customer->getEmail(),
                                $category_ids[$c]['cycle_day'],
                                $custom_fields
                            );
                        }
                    }
                }
                if ($move_subscriber) {
                    $results = (array)$grRepository->getContacts([
                        'query' => [
                            'email' => $customer->getEmail(),
                            'campaignId' => $registrationSettings->getCampaignId()
                        ]
                    ]);
                    $contact = array_pop($results);

                    if (!empty($contact) && isset($contact->contactId)) {
                        $grRepository->deleteContact($contact->contactId);
                    }
                }
            }
            if (!$move_subscriber) {
                $this->addContact(
                    $registrationSettings->getCampaignId(),
                    $customer->getFirstname(),
                    $customer->getLastname(),
                    $customer->getEmail(),
                    $registrationSettings->getCycleDay(),
                    $custom_fields
                );
            }
        }

        return $this;
    }


    /**
     * Add (or update) contact to gr campaign
     *
     * @param       $campaign
     * @param       $firstname
     * @param       $lastname
     * @param       $email
     * @param int $cycle_day
     * @param array $user_customs
     *
     * @return mixed
     */
    public function addContact($campaign, $firstname, $lastname, $email, $cycle_day = 0, $user_customs = [])
    {
        try {
            $grRepository = $this->repositoryFactory->createRepository();
        } catch (RepositoryException $e) {
            return $this;
        }

        $apiHelper = new ApiHelper($grRepository);

        $name = trim($firstname) . ' ' . trim($lastname);
        $user_customs['origin'] = 'magento2';

        $params = [
            'name' => $name,
            'email' => $email,
            'campaign' => ['campaignId' => $campaign],
            'ipAddress' => $_SERVER['REMOTE_ADDR']
        ];

        if (!empty($cycle_day)) {
            $params['dayOfCycle'] = (int)$cycle_day;
        }

        $results = (array)$grRepository->getContacts([
            'query' => [
                'email' => $email,
                'campaignId' => $campaign
            ]
        ]);

        $contact = array_pop($results);

        // if contact already exists in gr account
        if (!empty($contact) && isset($contact->contactId)) {
            $results = $grRepository->getContact($contact->contactId);
            if (!empty($results->customFieldValues)) {
                $params['customFieldValues'] = $apiHelper->mergeUserCustoms($results->customFieldValues, $user_customs);
            } else {
                $params['customFieldValues'] = $apiHelper->setCustoms($user_customs);
            }

            return $grRepository->updateContact($contact->contactId, $params);
        } else {
            $params['customFieldValues'] = $apiHelper->setCustoms($user_customs);

            return $grRepository->addContact($params);
        }
    }
}
