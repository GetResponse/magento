<?php
namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Domain\GetResponse\GetResponseRepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
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

    /** @var ApiHelper */
    private $apiHelper;

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
        $settings = $this->repository->getSettings();

        if ($settings['active_subscription'] != true) {
            return $this;
        }

        try {
            $grRepository = $this->repositoryFactory->buildRepository();
        } catch (GetResponseRepositoryException $e) {
            return $this;
        }

        $automations = $this->repository->getAutomations();

        $this->apiHelper = new ApiHelper($grRepository);
        $active_customs = $this->repository->getActiveCustoms();

        $order_id = $observer->getOrderIds();
        $order_id = (int) (is_array($order_id) ? array_pop($order_id) : $order_id);

        if ($order_id < 1) {
            return $this;
        }

        $order = $this->repository->loadOrder($order_id);

        $customer_id = $order->getCustomerId();
        $customer = $this->repository->loadCustomer($customer_id);

        $address = $this->repository->loadCustomerAddress($customer->getDefaultBilling());

//        if (empty($address->getData()['entity_id'])) {
//            $address_object = $this->_objectManager->create('Magento\Customer\Model\Address');
//            $address = $address_object->load($customer->getDefaultShipping());
//        }

        $data = array_merge($address->getData(), $customer->getData());

        $custom_fields = [];

        foreach ($active_customs as $custom) {

            if (in_array($custom['custom_field'], array('firstname', 'lastname'))) {
                continue;
            }

            if ($custom['custom_field'] == 'birthday') {
                $custom['custom_field'] = 'dob';
            }
            if ($custom['custom_field'] == 'country') {
                $custom['custom_field'] = 'country_id';
            }
            if (!empty($data[$custom['custom_field']])) {
                $custom_fields[$custom['custom_name']] = $data[$custom['custom_field']];
            }
        }

        $subscriber = $this->repository->loadSubscriberByEmail($customer->getEmail());

        if ($subscriber->isSubscribed() == true) {

            $move_subscriber = false;

            if (!empty($automations)) {
                $category_ids = [];
                foreach ($automations as $a) {
                    if ($a['active'] == 1) {
                        $category_ids[$a['category_id']] = [
                            'category_id' => $a['category_id'],
                            'action' => $a['action'],
                            'campaign_id' => $a['campaign_id'],
                            'cycle_day' => $a['cycle_day']
                        ];
                    }
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

                            $this->addContact($category_ids[$c]['campaign_id'], $customer->getFirstname(), $customer->getLastname(), $customer->getEmail(), $category_ids[$c]['cycle_day'], $custom_fields);
                        }
                    }
                }
                if ($move_subscriber) {
                    $results = (array) $this->grApi->getContacts([
                        'query' => [
                            'email'      => $customer->getEmail(),
                            'campaignId' => $settings['campaign_id']
                        ]
                    ]);
                    $contact = array_pop($results);

                    if (!empty($contact) && isset($contact->contactId)) {
                        $grRepository->deleteContact($contact->contactId);
                    }
                }
            }
            if (!$move_subscriber) {
                $response = $this->addContact($settings['campaign_id'], $customer->getFirstname(), $customer->getLastname(), $customer->getEmail(), $settings['cycle_day'], $custom_fields);
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
     * @param int   $cycle_day
     * @param array $user_customs
     *
     * @return mixed
     */
    public function addContact($campaign, $firstname, $lastname, $email, $cycle_day = 0, $user_customs = [])
    {
        try {
            $grRepository = $this->repositoryFactory->buildRepository();
        } catch (GetResponseRepositoryException $e) {
            return $this;
        }

        $name = trim($firstname) . ' ' . trim($lastname);
        $user_customs['origin'] = 'magento2';

        $params = [
            'name'       => $name,
            'email'      => $email,
            'campaign'   => ['campaignId' => $campaign],
            'ipAddress'  => $_SERVER['REMOTE_ADDR']
        ];

        if (!empty($cycle_day)) {
            $params['dayOfCycle'] = (int) $cycle_day;
        }

        $results = (array) $grRepository->getContacts([
            'query' => [
                'email'      => $email,
                'campaignId' => $campaign
            ]
        ]);

        $contact = array_pop($results);

        // if contact already exists in gr account
        if (!empty($contact) && isset($contact->contactId)) {
            $results = $grRepository->getContact($contact->contactId);
            if (!empty($results->customFieldValues)) {
                $params['customFieldValues'] = $this->apiHelper->mergeUserCustoms($results->customFieldValues, $user_customs);
            } else {
                $params['customFieldValues'] = $this->apiHelper->setCustoms($user_customs);
            }
            return $grRepository->updateContact($contact->contactId, $params);
        } else {
            $params['customFieldValues'] = $this->apiHelper->setCustoms($user_customs);
            return $grRepository->addContact($params);
        }
    }
}
