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
 * Class UpdateOrderHandler
 * @package GetResponse\GetResponseIntegration\Observer
 */
class UpdateOrderHandler extends Ecommerce implements ObserverInterface
{
    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /** @var Order */
    private $order;

    /** @var QuoteFactory */
    private $quoteFactory;

    /** @var GrRepository */
    private $grRepository;

    /** @var Repository */
    private $repository;

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
        $this->order = $orderFactory;
        $this->quoteFactory = $quoteFactory;
        $this->scopeConfig = $scopeConfig;
        $this->grRepository = $repositoryFactory->createRepository();
        $this->repository = $repository;

        parent::__construct(
            $objectManager,
            $customerSession,
            $productMapFactory,
            $countryFactory,
            $repositoryFactory,
            $repository
        );
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $shopId = $this->scopeConfig->getValue(Config::CONFIG_DATA_SHOP_ID);

        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        $requestToGr = $this->createOrderPayload($shopId, $order);
        $requestToGr['cartId'] = $this->getGetresponseCartId($order);

        if ($order->getGetresponseOrderMd5() == $this->createOrderPayloadHash($requestToGr) || '' == $order->getGetresponseOrderId()) {
            return;
        }

        $this->grRepository->updateOrder(
            $shopId,
            $order->getGetresponseOrderId(),
            $requestToGr
        );
        $order->setGetresponseOrderMd5($this->createOrderPayloadHash($requestToGr));
        $order->save();
    }

    /**
     * @param Order $order
     *
     * @return null|string
     */
    public function getGetresponseCartId(Order $order)
    {
        $shopId = $this->repository->getShopId();
        $getresponseOrderId = $order->getData('getresponse_order_id');

        if (empty($getresponseOrderId)) {
            return null;
        }

        $order = $this->grRepository->getOrder($shopId, $getresponseOrderId);

        return isset($order->cartId) ? $order->cartId : null;
    }
}
