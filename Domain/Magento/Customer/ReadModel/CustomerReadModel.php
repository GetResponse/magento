<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento\Customer\ReadModel;

use GetResponse\GetResponseIntegration\Domain\Magento\Customer\ReadModel\Query\CustomerId;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Data\Customer;
use Magento\Framework\ObjectManagerInterface;
use Magento\Newsletter\Model\Subscriber;

class CustomerReadModel
{
    private $objectManager;
    private $customerRepository;

    public function __construct(
        ObjectManagerInterface $objectManager,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->objectManager = $objectManager;
        $this->customerRepository = $customerRepository;
    }

    public function getCustomerById(CustomerId $query): Customer
    {
        return $this->customerRepository->getById($query->getId());
    }

    public function findCustomers(Scope $scope)
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
            ->where('subscriber_status=1')
            ->where('main_table.store_id=' . (int) $scope->getScopeId());

        return $subscribers;
    }
}
