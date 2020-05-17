<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand\Command;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactCustomFieldsCollectionFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand\ExportOnDemand;
use GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand\ExportSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Exception\InvalidOrderException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\OrderFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Customer\ReadModel\CustomerReadModel;
use GetResponse\GetResponseIntegration\Domain\Magento\Customer\ReadModel\Query\CustomerId;
use GetResponse\GetResponseIntegration\Domain\Magento\Order\ReadModel\OrderReadModel;
use GetResponse\GetResponseIntegration\Domain\Magento\Order\ReadModel\Query\CustomerOrders;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GrShareCode\Export\Command\ExportContactCommand;
use GrShareCode\Order\OrderCollection;
use Magento\Customer\Model\Customer;
use Magento\Newsletter\Model\Subscriber;

class ExportContactCommandFactory
{
    private $contactCustomFieldsCollectionFactory;
    private $repository;
    private $orderFactory;
    private $customerReadModel;
    private $orderReadModel;

    public function __construct(
        Repository $repository,
        ContactCustomFieldsCollectionFactory $contactCustomFieldsCollectionFactory,
        OrderFactory $orderFactory,
        CustomerReadModel $customerReadModel,
        OrderReadModel $orderReadModel
    ) {
        $this->repository = $repository;
        $this->contactCustomFieldsCollectionFactory = $contactCustomFieldsCollectionFactory;
        $this->orderFactory = $orderFactory;
        $this->customerReadModel = $customerReadModel;
        $this->orderReadModel = $orderReadModel;
    }

    public function createForSubscriber(
        Subscriber $subscriber,
        ExportOnDemand $exportOnDemand
    ): ExportContactCommand {

        if (!$this->subscriberIsAlsoCustomer($subscriber)) {
            return $this->createExportCommandForSubscriber($subscriber, $exportOnDemand);
        }

        $customer = $this->customerReadModel->getCustomerById(
            new CustomerId($subscriber->getCustomerId())
        );

        return $this->createExportCommandForCustomer($customer, $exportOnDemand);
    }

    private function subscriberIsAlsoCustomer(Subscriber $subscriber)
    {
        return 0 !== (int)$subscriber->getCustomerId();
    }

    private function createExportCommandForSubscriber(Subscriber $subscriber, ExportOnDemand $exportOnDemand)
    {
        $exportSettings = ExportSettingsFactory::createFromExportOnDemand($exportOnDemand);

        return new ExportContactCommand(
            $subscriber['subscriber_email'],
            '',
            $exportSettings,
            $this->contactCustomFieldsCollectionFactory->createForSubscriber(),
            new OrderCollection()
        );
    }

    private function createExportCommandForCustomer($customer, ExportOnDemand $exportOnDemand)
    {
        $exportSettings = ExportSettingsFactory::createFromExportOnDemand($exportOnDemand);

        $contactCustomFieldCollection = $this->contactCustomFieldsCollectionFactory->createForCustomer(
            $customer,
            $exportOnDemand->getCustomFieldsMappingCollection(),
            $exportOnDemand->isUpdateContactCustomFieldEnabled()
        );

        return new ExportContactCommand(
            $customer->getEmail(),
            trim($customer->getFirstname() . ' ' . $customer->getLastname()),
            $exportSettings,
            $contactCustomFieldCollection,
            $this->getCustomerOrderCollection($customer, $exportOnDemand)
        );
    }

    private function getCustomerOrderCollection(Customer $customer, ExportOnDemand $exportOnDemand): OrderCollection
    {
        $orderCollection = new OrderCollection();

        if (!$exportOnDemand->isSendEcommerceDataEnabled()) {
            return $orderCollection;
        }

        $orders = $this->orderReadModel->getCustomerOrders(
            new CustomerOrders((int)$customer->getId())
        );

        foreach ($orders as $order) {
            try {
                $orderCollection->add(
                    $this->orderFactory->fromMagentoOrder($order)
                );
            } catch (InvalidOrderException $e) {
            }
        }

        return $orderCollection;
    }
}
