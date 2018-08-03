<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Order;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Address\AddressFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\ProductFactory;
use GrShareCode\Api\ApiTypeException;
use GrShareCode\GetresponseApiException;
use GrShareCode\Order\AddOrderCommand;
use GrShareCode\Order\Order as GrOrder;
use GrShareCode\Order\OrderService as GrOrderService;
use GrShareCode\Product\ProductsCollection;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;

/**
 * Class OrderService
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Order
 */
class OrderService
{
    /** @var GrOrderService */
    private $grOrderService;

    /** @var ProductFactory */
    private $productFactory;

    /** @var AddressFactory */
    private $addressFactory;

    /**
     * @param OrderServiceFactory $orderServiceFactory
     * @param ProductFactory $productFactory
     * @param AddressFactory $addressFactory
     * @throws ApiTypeException
     */
    public function __construct(
        OrderServiceFactory $orderServiceFactory,
        ProductFactory $productFactory,
        AddressFactory $addressFactory
    ) {
        $this->grOrderService = $orderServiceFactory->create();
        $this->productFactory = $productFactory;
        $this->addressFactory = $addressFactory;
    }

    /**
     * @param Order $order
     * @param string $contactListId
     * @param string $grShopId
     * @throws GetresponseApiException
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

        $this->grOrderService->sendOrder($addCommandOrder);
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
     */
    public function sendOrder(Order $order, $contactListId, $grShopId)
    {
        $addOrderCommand = new AddOrderCommand(
            $this->getOrderForCommand($order),
            $order->getCustomerEmail(),
            $contactListId,
            $grShopId
        );

        $this->grOrderService->sendOrder($addOrderCommand);
    }

}