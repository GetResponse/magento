<?php
namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\OrderService;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\Config;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GetResponse\GetResponseIntegration\Model\ProductMapFactory;
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

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Session $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param Repository $repository
     * @param OrderService $orderService
     * @param ContactService $contactService
     * @param Logger $getResponseLogger
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Session $customerSession,
        ScopeConfigInterface $scopeConfig,
        Repository $repository,
        OrderService $orderService,
        ContactService $contactService,
        Logger $getResponseLogger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->orderService = $orderService;
        $this->magentoRepository = $repository;
        $this->logger = $getResponseLogger;

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

            $contactListId = $this->magentoRepository->getRegistrationSettings()['campaignId'];

            $this->orderService->sendOrder($observer->getEvent()->getOrder(), $contactListId, $shopId);

        } catch (Exception $e) {
            $this->logger->addError($e->getMessage(), ['exception' => $e]);
        }
    }

}
