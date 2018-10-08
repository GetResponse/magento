<?php
namespace Domain\GetResponse\Order;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\OrderService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\ProductFactory;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\Order\AddOrderCommand;
use GrShareCode\Order\Order;
use GrShareCode\Order\OrderService as GrOrderService;
use GrShareCode\Order\Order as GrOrder;

/**
 * Class OrderServiceTest
 * @package Domain\GetResponse\Order
 */
class OrderServiceTest extends BaseTestCase
{
    /** @var GrOrderService|\PHPUnit_Framework_MockObject_MockObject */
    private $grOrderService;

    /** @var ProductFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $productFactory;

    /** @var AddressFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $addressFactory;

    /** @var Order|\PHPUnit_Framework_MockObject_MockObject */
    private $order;

    public function setUp()
    {
        $this->grOrderService = $this->getMockWithoutConstructing(GrOrderService::class);
        $this->order = $this->getMockWithoutConstructing(\Magento\Sales\Model\Order::class);
    }

    /**
     * @test
     */
    public function sendOrderTest()
    {
        $email = 'test@test.com';
        $contactListId = 'Xdk3';
        $grShopId = 'e93D';
        $grOrder = new GrOrder();

        $addOrderCommand = new AddOrderCommand($grOrder, $email, $contactListId, $grShopId);
        $this->grOrderService->expects($this->once())->method('sendOrder')->with($addOrderCommand);

        $orderService = new OrderService();
    }
}
