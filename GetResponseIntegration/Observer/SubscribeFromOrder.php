<?php
namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Helper\ApiHelper;
use GetResponse\GetResponseIntegration\Helper\GetResponseAPI3;
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

    /** @var GetResponseAPI3 */
    public $grApi;

    /** @var ApiHelper */
    private $apiHelper;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager) {
        $this->_objectManager = $objectManager;
    }

    /**
     * Save order into registry to use it in the overloaded controller.
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $block = $this->_objectManager->create('GetResponse\GetResponseIntegration\Block\Settings');
        $settings = $block->getSettings();
        $automations = $block->getAutomations();

        $this->grApi = $block->getClient();

        if (empty($this->grApi) || $settings['active_subscription'] != true) {
            return $this;
        }

        $this->apiHelper = new ApiHelper($this->grApi);
        $active_customs = $block->getActiveCustoms();

        $order_id = $observer->getOrderIds();
        $order_id = (int) (is_array($order_id) ? array_pop($order_id) : $order_id);

        if ($order_id < 1) {
            return $this;
        }

        $order_object = $this->_objectManager->get('Magento\Sales\Model\Order');
        $order = $order_object->load($order_id);

        $customer_id = $order->getCustomerId();

        $customer_object = $this->_objectManager->get('Magento\Customer\Model\Customer');
        $customer = $customer_object->load($customer_id);

        $address_object = $this->_objectManager->get('Magento\Customer\Model\Address');
        $address = $address_object->load($customer->getDefaultBilling());
        if (empty($address->getData()['entity_id'])) {
            $address_object = $this->_objectManager->create('Magento\Customer\Model\Address');
            $address = $address_object->load($customer->getDefaultShipping());
        }

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

        $subscriber = $this->_objectManager->create('Magento\Newsletter\Model\Subscriber');
        $subscriber->loadByEmail($customer->getEmail());

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
                            $response = $this->addContact($category_ids[$c]['campaign_id'], $customer->getFirstname(), $customer->getLastname(), $customer->getEmail(), $category_ids[$c]['cycle_day'], $custom_fields);
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
                        $this->grApi->deleteContact($contact->contactId);
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

        $results = (array) $this->grApi->getContacts([
            'query' => [
                'email'      => $email,
                'campaignId' => $campaign
            ]
        ]);

        $contact = array_pop($results);

        // if contact already exists in gr account
        if (!empty($contact) && isset($contact->contactId)) {
            $results = $this->grApi->getContact($contact->contactId);
            if (!empty($results->customFieldValues)) {
                $params['customFieldValues'] = $this->apiHelper->mergeUserCustoms($results->customFieldValues, $user_customs);
            } else {
                $params['customFieldValues'] = $this->apiHelper->setCustoms($user_customs);
            }
            return $this->grApi->updateContact($contact->contactId, $params);
        } else {
            $params['customFieldValues'] = $this->apiHelper->setCustoms($user_customs);
            return $this->grApi->addContact($params);
        }
    }
}
