<?php
namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
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
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

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

    /** @var Repository */
    private $repository;

    private $repositoryFactory;

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
     * @param CollectionFactory $categoryCollection
     * @param StoreManagerInterface $storeManager
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
        Repository $repository,
        CollectionFactory $categoryCollection,
        StoreManagerInterface $storeManager
    ) {
        $this->order = $orderFactory;
        $this->quoteFactory = $quoteFactory;
        $this->scopeConfig = $scopeConfig;
        $this->repositoryFactory = $repositoryFactory;
        $this->repository = $repository;

        parent::__construct(
            $objectManager,
            $customerSession,
            $productMapFactory,
            $countryFactory,
            $repositoryFactory,
            $repository,
            $categoryCollection,
            $storeManager
        );
    }

    /**
     * @param EventObserver $observer
     * @return null
     */
    public function execute(EventObserver $observer)
    {
        $shopId = $this->scopeConfig->getValue(Config::CONFIG_DATA_SHOP_ID);

        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        $requestToGr = $this->createOrderPayload($shopId, $order);
        $requestToGr['cartId'] = $this->getGetresponseCartId($order);

        if ($order->getGetresponseOrderMd5() == $this->createOrderPayloadHash($requestToGr) || '' == $order->getGetresponseOrderId()) {
            return null;
        }

        try {
            $grRepository = $this->repositoryFactory->createRepository();
            $grRepository->updateOrder(
                $shopId,
                $order->getGetresponseOrderId(),
                $requestToGr
            );
            $order->setGetresponseOrderMd5($this->createOrderPayloadHash($requestToGr));
            $order->save();
        } catch (RepositoryException $e) {
            return null;
        }
    }

    /**
     * @param Order $order
     *
     * @return string
     */
    public function getGetresponseCartId(Order $order)
    {
        $shopId = $this->repository->getShopId();
        $getresponseOrderId = $order->getData('getresponse_order_id');

        if (empty($getresponseOrderId)) {
            return '';
        }

        try {
            $grRepository = $this->repositoryFactory->createRepository();
            $order = $grRepository->getOrder($shopId, $getresponseOrderId);
            return isset($order->cartId) ? $order->cartId : '';
        } catch (RepositoryException $e) {
            return '';
        }
    }
}
