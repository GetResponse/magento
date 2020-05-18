<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento\Customer\ReadModel;

use GetResponse\GetResponseIntegration\Domain\Magento\Customer\ReadModel\Query\CustomerEmail;
use GetResponse\GetResponseIntegration\Domain\Magento\Customer\ReadModel\Query\CustomerId;
use Magento\Customer\Model\Customer;
use Magento\Framework\ObjectManagerInterface;
use Magento\Newsletter\Model\Subscriber;

class CustomerReadModel
{
    private $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function getCustomerById(CustomerId $query): Customer
    {
        $customer = $this->objectManager->create(Customer::class);
        return $customer->load($query->getId());
    }

    public function getCustomerByEmail(CustomerEmail $query): Customer
    {
        /** @var Customer $customer */
        $customer = $this->objectManager->create(Customer::class);
        $customer->setWebsiteId($query->getScope()->getScopeId());
        return $customer->loadByEmail($query->getEmail());
    }

    public function findCustomers()
    {
        $subscriberModel = $this->objectManager->get(Subscriber::class);
        $subscribers = $subscriberModel->getCollection();

        $customerEntityTable = $subscribers->getTable('customer_entity');
        $customerAddressEntityTable = $subscribers->getTable('customer_address_entity');

        $subscribers->getSelect()
            ->joinLeft(
                ['customer_entity' => $customerEntityTable],
                'customer_entity.entity_id=main_table.customer_id',
                ['*']
            )
            ->joinLeft(
                ['customer_address_entity' => $customerAddressEntityTable],
                'customer_address_entity.entity_id=default_billing',
                ['*']
            )
            ->where('subscriber_status=1');

        return $subscribers;
    }
}
