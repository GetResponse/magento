<?php
namespace GetResponse\GetResponseIntegration\Observer;

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

/**
 * Class UpdateOrderHandler
 * @package GetResponse\GetResponseIntegration\Observer
 */
class UpdateOrderHandler extends Ecommerce implements ObserverInterface
{
    /** @var ScopeConfigInterface */
    private $scopeConfig;

    private $orderFactory;
    private $quoteFactory;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Session $customerSession
     * @param QuoteFactory $quoteFactory
     * @param Order $orderFactory
     * @param ProductMapFactory $productMapFactory
     * @param CountryFactory $countryFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Session $customerSession,
        QuoteFactory $quoteFactory,
        Order $orderFactory,
        ProductMapFactory $productMapFactory,
        CountryFactory $countryFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->orderFactory = $orderFactory;
        $this->quoteFactory = $quoteFactory;
        $this->scopeConfig = $scopeConfig;

        parent::__construct($objectManager, $customerSession, $productMapFactory, $countryFactory);
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $shopId = $this->scopeConfig->getValue(Config::SHOP_ID);

        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        $requestToGr = $this->createOrderPayload($shopId, $order);
        $requestToGr['cartId'] = $this->getGetresponseCartId($order);

        if ($order->getGetresponseOrderMd5() == $this->createOrderPayloadHash($requestToGr) || '' == $order->getGetresponseOrderId()) {
            return;
        }

        $this->apiClient->updateOrder(
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
        $shopId = $this->scopeConfig->getValue(Config::SHOP_ID);
        $getresponseOrderId = $order->getData('getresponse_order_id');

        if (empty($getresponseOrderId)) {
            return null;
        }

        $order = $this->apiClient->getOrder($shopId, $getresponseOrderId);

        return isset($order->cartId) ? $order->cartId : null;
    }
}
