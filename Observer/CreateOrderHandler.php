<?php
namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Command\AddOrderCommandFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\OrderService;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\Config;
use GetResponse\GetResponseIntegration\Logger\Logger;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order;

/**
 * Class CreateOrderHandler
 * @package GetResponse\GetResponseIntegration\Observer
 */
class CreateOrderHandler extends Ecommerce implements ObserverInterface
{
    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /** @var OrderService */
    private $orderService;

    /** @var Order */
    private $orderFactory;

    /** @var Logger */
    private $logger;

    /** @var AddOrderCommandFactory */
    private $addOrderCommandFactory;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Session $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param Order $orderFactory
     * @param Repository $repository
     * @param OrderService $orderService
     * @param ContactService $contactService
     * @param Logger $getResponseLogger
     * @param AddOrderCommandFactory $addOrderCommandFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Session $customerSession,
        ScopeConfigInterface $scopeConfig,
        Order $orderFactory,
        Repository $repository,
        OrderService $orderService,
        ContactService $contactService,
        Logger $getResponseLogger,
        AddOrderCommandFactory $addOrderCommandFactory
    ) {
        parent::__construct(
            $objectManager,
            $customerSession,
            $repository,
            $contactService
        );

        $this->scopeConfig = $scopeConfig;
        $this->orderService = $orderService;
        $this->orderFactory = $orderFactory;
        $this->logger = $getResponseLogger;
        $this->addOrderCommandFactory = $addOrderCommandFactory;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        try {

            $shopId = $this->scopeConfig->getValue(Config::CONFIG_DATA_SHOP_ID);

            if (empty($shopId)) {
                return;
            }

            if (!$this->canHandleECommerceEvent()) {
                return;
            }

            /** @var Order $order */
            $order = $this->orderFactory->load(
                $observer->getEvent()->getOrderIds()[0]
            );

            $this->orderService->addOrder(
                $this->addOrderCommandFactory->createForMagentoOrder(
                    $order,
                    $this->scopeConfig->getValue(Config::CONFIG_DATA_ECOMMERCE_LIST_ID),
                    $shopId
                )
            );

        } catch (Exception $e) {
            $this->logger->addError($e->getMessage(), ['exception' => $e]);
        }
    }
}
