<?php
namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Helper\GetResponseAPI3;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Cache\Proxy;
use Magento\Framework\ObjectManagerInterface;
use Magento\Customer\Model\Customer;
use GetResponse\GetResponseIntegration\Model\ProductMap;
use GetResponse\GetResponseIntegration\Model\ProductMapFactory;
use GetResponse\GetResponseIntegration\Model\ResourceModel\ProductMap\Collection;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Model\Order;
use Magento\Directory\Model\CountryFactory;

/**
 * Class Ecommerce
 * @package GetResponse\GetResponseIntegration\Observer
 */
class Ecommerce
{
    const CACHE_KEY = 'getresponse_cache';
    /** @var Session */
    protected $customerSession;

    /** @var ObjectManagerInterface */
    protected $objectManager;

    /** @var GetResponseAPI3 */
    protected $apiClient;

    /** @var ProductMapFactory */
    protected $productMapFactory;

    /** @var CountryFactory */
    protected $countryFactory;

    /** @var Proxy */
    protected $cache;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Session $customerSession
     * @param ProductMapFactory $productMapFactory
     * @param CountryFactory $countryFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Session $customerSession,
        ProductMapFactory $productMapFactory,
        CountryFactory $countryFactory
    ) {
        $this->objectManager = $objectManager;
        $this->customerSession = $customerSession;
        $this->productMapFactory = $productMapFactory;
        $this->countryFactory = $countryFactory;

        $block = $objectManager->create('GetResponse\GetResponseIntegration\Block\Settings');
        $this->cache = $objectManager->get('Magento\Framework\App\CacheInterface');
        $this->apiClient = $block->getClient();
    }

    /**
     * @return bool
     */
    protected function canHandleECommerceEvent()
    {
        if (false === $this->customerSession->isLoggedIn()) {
            return false;
        }

        $contact = $this->getContactFromGetResponse();

        if (!isset($contact->contactId)) {
            return false;
        }
        return true;
    }

    /**
     * @return \stdClass
     */
    protected function getContactFromGetResponse()
    {
        $block = $this->objectManager->create('GetResponse\GetResponseIntegration\Block\Settings');
        $settings = $block->getSettings();

        /** @var Customer $customer */
        $customer = $this->customerSession->getCustomer();

        $cacheKey = md5($customer->getEmail().$settings['campaign_id']);
        $cachedCustomer = $this->cache->load($cacheKey);

        if (false !== $cachedCustomer) {
            return unserialize($cachedCustomer);
        }

        $params = array('query' =>
            array(
                'email' => $customer->getEmail(),
                'campaignId' => $settings['campaign_id']
            )
        );

        $response = (array) $this->apiClient->getContacts($params);
        $grCustomer = array_pop($response);

        $this->cache->save(serialize($grCustomer), $cacheKey, [self::CACHE_KEY], 5*60);

        return $grCustomer;
    }

    /**
     * @param int $shopId
     * @param Item $magentoCartItem
     * @return string
     */
    protected function getProductId($shopId, $magentoCartItem)
    {
        $factory = $this->productMapFactory->create();
        /** @var Collection $collection */
        $productMapCollection = $factory->getCollection();

        /** @var ProductMap $productMap */
        $productMap = $productMapCollection
            ->addFieldToFilter('entity_id', $magentoCartItem->getProduct()->getId())
            ->addFieldToFilter('gr_shop_id', $shopId)
            ->getFirstItem();

        if (!is_null($productMap->getData('gr_product_id'))) {
            return $productMap->getData('gr_product_id');
        }

        $productId = $this->createProductInGetResponse($shopId, $magentoCartItem);

        if (0 === strlen($productId)) {
            return '';
        }

        $productMap = $this->productMapFactory->create();
        $productMap->setData([
            'gr_shop_id' => $shopId,
            'entity_id' => $magentoCartItem->getProduct()->getId(),
            'gr_product_id' => $productId
        ]);

        $productMap->save();
        return $productId;
    }

    /**
     * @param string $shopId
     * @param Item $magentoCartItem
     *
     * @return string
     */
    private function createProductInGetResponse($shopId, $magentoCartItem)
    {
        $params = [
            'name' => $magentoCartItem->getProduct()->getName(),
            'categories' => [],
            'externalId' => $magentoCartItem->getProduct()->getId(),
            'variants' => [
                [
                    'name' => $magentoCartItem->getProduct()->getName(),
                    'price'=> $magentoCartItem->getProduct()->getPrice(),
                    'priceTax' => 0,
                    'quantity' => $magentoCartItem->getProduct()->getQty(),
                    'sku' => $magentoCartItem->getProduct()->getSku(),
                ],
            ],
        ];

        $response = $this->apiClient->addProduct($shopId, $params);
        return $this->handleProductResponse($response);
    }

    /**
     * @param \stdClass $response
     * @return string
     */
    private function handleProductResponse($response)
    {
        if (!isset($response->productId)) {
            return '';
        } else {
            return $response->variants[0]->variantId;
        }
    }

    /**
     * @param string $shopId
     * @param Order $order
     * @return array
     */
    protected function createOrderPayload($shopId, Order $order)
    {
        /** @var \Magento\Sales\Model\Order\Address $address */
        $shippingAddress = $order->getShippingAddress();

        /** @var \Magento\Sales\Model\Order\Address $address */
        $billingAddress = $order->getBillingAddress();
        $shippingCountry = $this->countryFactory->create()->loadByCode($shippingAddress->getCountryId());
        $billingCountry = $this->countryFactory->create()->loadByCode($billingAddress->getCountryId());

        $requestToGr = [
            'contactId' => $this->getContactFromGetResponse()->contactId,
            'totalPriceTax' => $order->getTaxAmount(),
            'totalPrice' => $order->getBaseSubtotal(),
            'currency' => $order->getOrderCurrencyCode(),
            'status' => $order->getStatus(),
            'cartId' => 0,
            'shippingPrice'  => $order->getShippingAmount(),
            'externalId' => $order->getId(),
            'shippingAddress' => [
                'countryCode' => $shippingCountry->getData('iso3_code'),
                'name' => $shippingAddress->getData('street'),
                'firstName' => $shippingAddress->getFirstname(),
                'lastName' => $shippingAddress->getLastname(),
                'city' => $shippingAddress->getCity(),
                'zip' => $shippingAddress->getPostcode(),
            ],
            'billingAddress' => [
                'countryCode' => $billingCountry->getData('iso3_code'),
                'name' => $billingAddress->getData('street'),
                'firstName' => $billingAddress->getFirstname(),
                'lastName' => $billingAddress->getLastname(),
                'city' => $billingAddress->getCity(),
                'zip' => $billingAddress->getPostcode(),
            ],
        ];

        /** @var Item $item */
        foreach ($order->getAllItems() as $item) {

            if ('simple' !== $item->getProductType()) {
                false;
            }

            $grProductId = $this->getProductId($shopId, $item);

            if (false === $grProductId) {
                continue;
            }

            $requestToGr['selectedVariants'][] = [
                'variantId' => $grProductId,
                'price' => $item->getPrice(),
                'priceTax' => $item->getTaxAmount(),
                'quantity' => $item->getQtyOrdered(),
                'taxes' => [
                    [
                        'name' => 'tax',
                        'rate' => $item->getTaxPercent(),
                    ]
                ]
            ];
        }

        return $requestToGr;
    }

    /**
     * @param array $orderPayload
     * @return string
     */
    protected function createOrderPayloadHash(array $orderPayload)
    {
        return md5(json_encode($orderPayload));
    }
}
