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

        $orderId = $observer->getOrderIds();
        $orderId = (int)(is_array($orderId) ? array_pop($orderId) : $orderId);

        if ($orderId < 1) {
            return $this;
        }

        $order = $this->repository->loadOrder($orderId);

        $customer_id = $order->getCustomerId();
        $customer = $this->repository->loadCustomer($customer_id);

        $address = $this->repository->loadCustomerAddress($customer->getDefaultBilling());

        $data = array_merge($address->getData(), $customer->getData());

        $customFields = [];

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

        $subscriber = $this->repository->loadSubscriberByEmail($customer->getEmail());

        if ($subscriber->isSubscribed() == true) {
            $moveSubscriber = false;

            if (!empty($rulesCollection->getRules())) {
                $categoryIds = [];
                foreach ($rulesCollection->getRules() as $rule) {
                    $categoryIds[$rule->getCategory()] = [
                        'category_id' => $rule->getCategory(),
                        'action' => $rule->getAction(),
                        'campaign_id' => $rule->getCampaign(),
                        'cycle_day' => $rule->getAutoresponderDay()
                    ];
                }

                $automations_categories = array_keys($categoryIds);

                foreach ($order->getItems() as $item) {
                    $product_id = $item->getData()['product_id'];
                    $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
                    $product_categories = $product->getCategoryIds();

                    $category = array_intersect($product_categories, $automations_categories);
                    if ($category) {
                        foreach ($category as $c) {
                            if ($categoryIds[$c]['action'] == 'move') {
                                $moveSubscriber = true;
                            }

                            $this->addContact(
                                $categoryIds[$c]['campaign_id'],
                                $customer->getFirstname(),
                                $customer->getLastname(),
                                $customer->getEmail(),
                                $categoryIds[$c]['cycle_day'],
                                $customFields
                            );
                        }
                    }
                }
                if ($moveSubscriber) {
                    $results = $grRepository->getContacts([
                        'query' => [
                            'email' => $customer->getEmail(),
                            'campaignId' => $registrationSettings->getCampaignId()
                        ]
                    ]);
                    $contact = array_pop($results);

                    if (isset($contact['contactId'])) {
                        $grRepository->deleteContact($contact['contactId']);
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
        }

        return $this;
    }


    /**
     * Add (or update) contact to gr campaign
     *
     * @param string $campaign
     * @param string $firstname
     * @param string $lastname
     * @param string $email
     * @param int $cycle_day
     * @param array $user_customs
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

        $results = $grRepository->getContacts(['query' => ['email' => $email, 'campaignId' => $campaign]]);
        $contact = array_pop($results);

        if (isset($contact['contactId'])) {
            $results = $grRepository->getContact($contact['contactId']);
            if (!empty($results['customFieldValues'])) {
                $params['customFieldValues'] = $apiHelper->mergeUserCustoms($results['customFieldValues'], $user_customs);
            } else {
                $params['customFieldValues'] = $apiHelper->setCustoms($user_customs);
            }

            $grRepository->updateContact($contact['contactId'], $params);
        } else {
            $params['customFieldValues'] = $apiHelper->setCustoms($user_customs);
            $grRepository->addContact($params);
        }
    }
}
