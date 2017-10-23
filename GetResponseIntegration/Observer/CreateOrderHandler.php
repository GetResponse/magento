<?php
namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Model\Session;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\Order;
use GetResponse\GetResponseIntegration\Model\ProductMapFactory;
use GetResponse\GetResponseIntegration\Helper\Config;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Repository as GrRepository;

/**
 * Class CreateOrderHandler
 * @package GetResponse\GetResponseIntegration\Observer
 */
class CreateOrderHandler extends Ecommerce implements ObserverInterface
{
    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /** @var Order */
    private $order;

    /** @var QuoteFactory */
    private $quoteFactory;

    /** @var GrRepository */
    private $grRepository;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Session $customerSession
     * @param QuoteFactory $quoteFactory
     * @param Order $orderFactory
     * @param ProductMapFactory $productMapFactory
     * @param CountryFactory $countryFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param RepositoryFactory $repositoryFactory
     * @param Repository $repository
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Session $customerSession,
        QuoteFactory $quoteFactory,
        Order $orderFactory,
        ProductMapFactory $productMapFactory,
        CountryFactory $countryFactory,
        ScopeConfigInterface $scopeConfig,
        RepositoryFactory $repositoryFactory,
        Repository $repository
    ) {
        parent::__construct(
            $objectManager,
            $customerSession,
            $productMapFactory,
            $countryFactory,
            $repositoryFactory,
            $repository
        );

        $this->order = $orderFactory;
        $this->quoteFactory = $quoteFactory;
        $this->countryFactory = $countryFactory;
        $this->scopeConfig = $scopeConfig;
        $this->grRepository = $repositoryFactory->createRepository();
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        if (false === $this->canHandleECommerceEvent()) {
            return;
        }

        $shopId = $this->scopeConfig->getValue(Config::CONFIG_DATA_SHOP_ID);

        $orderIds = $observer->getEvent()->getOrderIds();
        $lastOrderId = $orderIds[0];

        /** @var Order $order */
        $order = $this->order->load($lastOrderId);

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteFactory->create()->load($order->getQuoteId());

        $requestToGr = $this->createOrderPayload($shopId, $order);
        $requestToGr['cartId'] = $quote->getGetresponseCartId();


        $response = $this->grRepository->createOrder(
            $shopId,
            $requestToGr
        );

        if (isset($response->httpStatus) && $response->httpStatus > 299) {
            return;
        }

        $order->setData('getresponse_order_id', $response->orderId);
        $order->setData('getresponse_order_md5', $this->createOrderPayloadHash($requestToGr));
        $order->save();
    }
}
