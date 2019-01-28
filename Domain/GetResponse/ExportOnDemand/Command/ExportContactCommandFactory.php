<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand\Command;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactCustomFieldsCollectionFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand\ExportOnDemand;
use GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand\ExportSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Exception\InvalidOrderException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\OrderFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GrShareCode\Export\Command\ExportContactCommand;
use GrShareCode\Order\OrderCollection;
use Magento\Customer\Model\Customer;
use Magento\Newsletter\Model\Subscriber;
use Magento\Sales\Model\Order;

/**
 * Class ExportContactCommandFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand\Command
 */
class ExportContactCommandFactory
{
    /** @var ContactCustomFieldsCollectionFactory */
    private $contactCustomFieldsCollectionFactory;

    /** @var Repository */
    private $repository;

    /** @var OrderFactory */
    private $orderFactory;

    /**
     * @param Repository $repository
     * @param ContactCustomFieldsCollectionFactory $contactCustomFieldsCollectionFactory
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        Repository $repository,
        ContactCustomFieldsCollectionFactory $contactCustomFieldsCollectionFactory,
        OrderFactory $orderFactory
    ) {
        $this->repository = $repository;
        $this->contactCustomFieldsCollectionFactory = $contactCustomFieldsCollectionFactory;
        $this->orderFactory = $orderFactory;
    }

    /**
     * @param Subscriber $subscriber
     * @param ExportOnDemand $exportOnDemand
     * @return ExportContactCommand
     */
    public function createForSubscriber(Subscriber $subscriber, ExportOnDemand $exportOnDemand)
    {
        if (!$this->subscriberIsAlsoCustomer($subscriber)) {
            return $this->createExportCommandForSubscriber($subscriber, $exportOnDemand);
        }

        $customer = $this->repository->loadCustomer($subscriber->getCustomerId());

        return $this->createExportCommandForCustomer($customer, $exportOnDemand);
    }

    /**
     * @param $subscriber
     * @return bool
     */
    private function subscriberIsAlsoCustomer(Subscriber $subscriber)
    {
        return 0 !== (int)$subscriber->getCustomerId();
    }

    /**
     * @param Subscriber $subscriber
     * @param ExportOnDemand $exportOnDemand
     * @return ExportContactCommand
     */
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

    /**
     * @param Customer $customer
     * @param ExportOnDemand $exportOnDemand
     * @return ExportContactCommand
     */
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

    /**
     * @param Customer $customer
     * @param ExportOnDemand $exportOnDemand
     * @return OrderCollection
     */
    private function getCustomerOrderCollection(Customer $customer, ExportOnDemand $exportOnDemand)
    {
        $orderCollection = new OrderCollection();

        if (!$exportOnDemand->isSendEcommerceDataEnabled()) {
            return $orderCollection;
        }

        $orders = $this->repository->getOrderByCustomerId($customer->getId());

        /** @var Order $order */
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