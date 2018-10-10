<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Order;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Address\AddressFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\ProductFactory;
use GrShareCode\Order\AddOrderCommand;
use Magento\Sales\Model\Order;
use GrShareCode\Order\Order as GrOrder;
use GrShareCode\Product\ProductsCollection;
use Magento\Sales\Model\Order\Item;

/**
 * Class AddOrderServiceFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Order
 */
class AddOrderCommandFactory
{
    /** @var ProductFactory */
    private $productFactory;

    /** @var AddressFactory */
    private $addressFactory;

    /**
     * @param ProductFactory $productFactory
     * @param AddressFactory $addressFactory
     */
    public function __construct(
        ProductFactory $productFactory,
        AddressFactory $addressFactory
    ) {
        $this->productFactory = $productFactory;
        $this->addressFactory = $addressFactory;
    }

    public function createForOrderService(Order $order, $contactListId, $shopId)
    {
        return new AddOrderCommand(
            $this->getOrderForCommand($order),
            $order->getCustomerEmail(),
            $contactListId,
            $shopId
        );
    }

    /**
     * @param Order $order
     * @return GrOrder
     */
    private function getOrderForCommand(Order $order)
    {
        $productCollection = new ProductsCollection();

        /** @var Item $orderItem */
        foreach ($order->getAllVisibleItems() as $orderItem) {
            $productCollection->add(
                $this->productFactory->fromMagentoOrderItem($orderItem)
            );
        }

        $orderForCommand = OrderFactory::fromMagentoOrder(
            $order,
            $productCollection,
            $this->addressFactory->createShippingAddressFromMagentoOrder($order),
            $this->addressFactory->createBillingAddressFromMagentoOrder($order)
        );

        return $orderForCommand;
    }
}
