<?php
namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\AddOrderCommandFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\OrderService;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\Config;
use GetResponse\GetResponseIntegration\Logger\Logger;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class UpdateOrderHandler
 * @package GetResponse\GetResponseIntegration\Observer
 */
class UpdateOrderHandler extends Ecommerce implements ObserverInterface
{
    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /** @var OrderService */
    private $orderService;

    /** @var Repository */
    private $magentoRepository;

    /** @var Logger */
    private $logger;

    /** @var AddOrderCommandFactory */
    private $addOrderCommandFactory;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Session $customerSession
     * @param ScopeConfigInterface $scopeConfig
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
        Repository $repository,
        OrderService $orderService,
        ContactService $contactService,
        Logger $getResponseLogger,
        AddOrderCommandFactory $addOrderCommandFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->orderService = $orderService;
        $this->magentoRepository = $repository;
        $this->logger = $getResponseLogger;
        $this->addOrderCommandFactory = $addOrderCommandFactory;

        parent::__construct(
            $objectManager,
            $customerSession,
            $repository,
            $contactService
        );
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

            $this->orderService->sendOrder(
                $this->addOrderCommandFactory->createForOrderService(
                    $observer->getEvent()->getOrder(),
                    $this->scopeConfig->getValue(Config::CONFIG_DATA_ECOMMERCE_LIST_ID),
                    $shopId
                )
            );

        } catch (Exception $e) {
            $this->logger->addError($e->getMessage(), ['exception' => $e]);
        }
    }

}
