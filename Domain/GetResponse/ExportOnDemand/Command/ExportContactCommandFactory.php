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
use GrShareCode\Contact\ContactCustomField\ContactCustomFieldsCollection;
use GrShareCode\Export\Command\ExportContactCommand;
use GrShareCode\Order\OrderCollection;
use Magento\Customer\Model\Data\Customer;
use Magento\Newsletter\Model\Subscriber;

class ExportContactCommandFactory
{
    private $contactCustomFieldsCollectionFactory;
    private $orderFactory;
    private $customerReadModel;
    private $orderReadModel;

    public function __construct(
        ContactCustomFieldsCollectionFactory $contactCustomFieldsCollectionFactory,
        OrderFactory $orderFactory,
        CustomerReadModel $customerReadModel,
        OrderReadModel $orderReadModel
    ) {
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

    private function subscriberIsAlsoCustomer(Subscriber $subscriber): bool
    {
        return 0 !== (int)$subscriber->getCustomerId();
    }

    private function createExportCommandForSubscriber(
        Subscriber $subscriber,
        ExportOnDemand $exportOnDemand
    ): ExportContactCommand {
        $exportSettings = ExportSettingsFactory::createFromExportOnDemand($exportOnDemand);

        return new ExportContactCommand(
            $subscriber['subscriber_email'],
            '',
            $exportSettings,
            new ContactCustomFieldsCollection(),
            new OrderCollection()
        );
    }

    private function createExportCommandForCustomer(
        Customer $customer,
        ExportOnDemand $exportOnDemand
    ): ExportContactCommand {
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

    private function getCustomerOrderCollection(
        Customer $customer,
        ExportOnDemand $exportOnDemand
    ): OrderCollection {
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
