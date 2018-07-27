<?php
namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\RegistrationSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\Config;
use Magento\Catalog\Model\Category\Interceptor;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Customer\Model\Customer;
use GetResponse\GetResponseIntegration\Model\ProductMap;
use GetResponse\GetResponseIntegration\Model\ProductMapFactory;
use GetResponse\GetResponseIntegration\Model\ResourceModel\ProductMap\Collection;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Model\Order;
use Magento\Directory\Model\CountryFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Ecommerce
 * @package GetResponse\GetResponseIntegration\Observer
 */
class Ecommerce
{
    /** @var Session */
    protected $customerSession;

    /** @var ObjectManagerInterface */
    protected $objectManager;

    /** @var ProductMapFactory */
    protected $productMapFactory;

    /** @var CountryFactory */
    protected $countryFactory;

    /** @var RepositoryFactory */
    private $repositoryFactory;

    /** @var Repository */
    private $repository;

    /** @var CollectionFactory */
    private $categoryCollection;

    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Session $customerSession
     * @param ProductMapFactory $productMapFactory
     * @param CountryFactory $countryFactory
     * @param RepositoryFactory $repositoryFactory
     * @param Repository $repository
     * @param CollectionFactory $categoryCollection
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Session $customerSession,
        ProductMapFactory $productMapFactory,
        CountryFactory $countryFactory,
        RepositoryFactory $repositoryFactory,
        Repository $repository,
        CollectionFactory $categoryCollection,
        StoreManagerInterface $storeManager
    ) {
        $this->objectManager = $objectManager;
        $this->customerSession = $customerSession;
        $this->productMapFactory = $productMapFactory;
        $this->countryFactory = $countryFactory;
        $this->repositoryFactory = $repositoryFactory;
        $this->repository = $repository;
        $this->categoryCollection = $categoryCollection;
        $this->storeManager = $storeManager;
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
        return !isset($contact->contactId) ? false : true;
    }

    /**
     * @return \stdClass
     */
    protected function getContactFromGetResponse()
    {
        $cache = $this->objectManager->get('Magento\Framework\App\CacheInterface');

        $settings = RegistrationSettingsFactory::createFromArray(
            $this->repository->getRegistrationSettings()
        );

        /** @var Customer $customer */
        $customer = $this->customerSession->getCustomer();

        $cacheKey = md5($customer->getEmail() . $settings->getCampaignId());
        $cachedCustomer = $cache->load($cacheKey);

        if (false !== $cachedCustomer) {
            return unserialize($cachedCustomer);
        }

        $params = [
            'query' => [
                'email' => $customer->getEmail(),
                'campaignId' => $settings->getCampaignId()
            ]
        ];

        try {
            $grRepository = $this->repositoryFactory->createRepository();
        } catch (RepositoryException $e) {
            return null;
        }

        $response = (array)$grRepository->getContacts($params);
        $grCustomer = array_pop($response);

        $cache->save(serialize($grCustomer), $cacheKey, [Config::CACHE_KEY], Config::CACHE_TIME);

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
        $grCategories = $grImages = [];

        /** @var \Magento\Catalog\Model\Product\Interceptor $product */
        $product = $magentoCartItem->getProduct();
        $this->objectManager->get("Magento\Catalog\Model\Product\Gallery\ReadHandler")->execute($product);

        try {
            $categories = $this->categoryCollection->create()
                ->addAttributeToSelect('*')
                ->setStore($this->storeManager->getStore())
                ->addAttributeToFilter('is_active', '1');
        } catch (LocalizedException $e) {
            $categories = [];
        }

        /** @var Interceptor $category */
        foreach ($categories as $category) {
            if (!in_array($category->getId(), $product->getCategoryIds())) {
                continue;
            }

            $grCategories[] = [
                'name' => $category->getName(),
                'url' => $category->getUrl(),
                'externalId' => $category->getId(),
                'isDefault' => false
            ];
        }

        $images = $product->getMediaGalleryImages();

        foreach ($images as $image) {
            $grImages[] = [
                'src' => $image['url'],
                'position' => $image['position']
            ];
        }

        $params = [
            'name' => (string) $product->getName(),
            'url' => $product->getProductUrl(),
            'categories' => $grCategories,
            'externalId' => (string) $product->getId(),
            'variants' => [
                [
                    'name' => (string) $product->getName(),
                    'url' => $product->getProductUrl(),
                    'price' => (float) $product->getPrice(),
                    'priceTax' =>  $product->getFinalPrice(),
                    'quantity' => (int) $product->getQty(),
                    'sku' => (string) $product->getSku(),
                    'images' => $grImages
                ],
            ],
        ];

        try {
            $grRepository = $this->repositoryFactory->createRepository();
            $response = $grRepository->addProduct($shopId, $params);
            return $this->handleProductResponse($response);
        } catch (RepositoryException $e) {
            return null;
        }
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
            'contactId' => (string) $this->getContactFromGetResponse()->contactId,
            'totalPriceTax' => (float) $order->getTaxAmount(),
            'totalPrice' => (float) $order->getBaseSubtotal(),
            'currency' => (string) $order->getOrderCurrencyCode(),
            'status' => (string) $order->getStatus(),
            'cartId' => 0,
            'shippingPrice' => (float) $order->getShippingAmount(),
            'externalId' => (string) $order->getId(),
            'shippingAddress' => [
                'countryCode' => (string) $shippingCountry->getData('iso3_code'),
                'name' => (string) $shippingAddress->getData('street'),
                'firstName' => (string) $shippingAddress->getFirstname(),
                'lastName' => (string) $shippingAddress->getLastname(),
                'city' => (string) $shippingAddress->getCity(),
                'zip' => (string) $shippingAddress->getPostcode(),
            ],
            'billingAddress' => [
                'countryCode' => (string) $billingCountry->getData('iso3_code'),
                'name' => (string) $billingAddress->getData('street'),
                'firstName' => (string) $billingAddress->getFirstname(),
                'lastName' => (string) $billingAddress->getLastname(),
                'city' => (string) $billingAddress->getCity(),
                'zip' => (string) $billingAddress->getPostcode(),
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
                'variantId' => (string) $grProductId,
                'price' => (float) $item->getPrice(),
                'priceTax' => (float) $item->getTaxAmount(),
                'quantity' => (int) $item->getQtyOrdered(),
                'taxes' => [
                    [
                        'name' => 'tax',
                        'rate' => (float) $item->getTaxPercent(),
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
