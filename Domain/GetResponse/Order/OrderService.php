<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Order;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Address\AddressFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\ProductFactory;
use GrShareCode\Api\ApiTypeException;
use GrShareCode\GetresponseApiException;
use GrShareCode\Order\AddOrderCommand;
use GrShareCode\Order\Order as GrOrder;
use GrShareCode\Product\ProductsCollection;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;

/**
 * Class OrderService
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Order
 */
class OrderService
{
    /** @var OrderServiceFactory */
    private $orderServiceFactory;

    /** @var ProductFactory */
    private $productFactory;

    /** @var AddressFactory */
    private $addressFactory;

    /**
     * @param OrderServiceFactory $orderServiceFactory
     * @param ProductFactory $productFactory
     * @param AddressFactory $addressFactory
     */
    public function __construct(
        OrderServiceFactory $orderServiceFactory,
        ProductFactory $productFactory,
        AddressFactory $addressFactory
    ) {
        $this->orderServiceFactory = $orderServiceFactory;
        $this->productFactory = $productFactory;
        $this->addressFactory = $addressFactory;
    }

    /**
     * @param Order $order
     * @param string $contactListId
     * @param string $grShopId
     * @throws GetresponseApiException
     * @throws ApiTypeException
     */
    public function exportOrder(Order $order, $contactListId, $grShopId)
    {
        $addCommandOrder = new AddOrderCommand(
            $this->getOrderForCommand($order),
            $order->getCustomerEmail(),
            $contactListId,
            $grShopId
        );

        $addCommandOrder->setToSkipAutomation();

        $orderService = $this->orderServiceFactory->create();
        $orderService->sendOrder($addCommandOrder);
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

    /**
     * @param Order $order
     * @param string $contactListId
     * @param string $grShopId
     * @throws GetresponseApiException
     * @throws ApiTypeException
     */
    public function sendOrder(Order $order, $contactListId, $grShopId)
    {
        $addOrderCommand = new AddOrderCommand(
            $this->getOrderForCommand($order),
            $order->getCustomerEmail(),
            $contactListId,
            $grShopId
        );

        $orderService = $this->orderServiceFactory->create();
        $orderService->sendOrder($addOrderCommand);
    }

}