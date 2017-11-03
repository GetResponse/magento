<?php
namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote\Item;
use GetResponse\GetResponseIntegration\Helper\Config;
use GetResponse\GetResponseIntegration\Model\ProductMapFactory;
use Magento\Directory\Model\CountryFactory;

/**
 * Class CreateCartHandler
 * @package GetResponse\GetResponseIntegration\Observer
 */
class CreateCartHandler extends Ecommerce implements ObserverInterface
{
    /** @var Cart */
    private $cart;

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /** @var RepositoryFactory */
    private $repositoryFactory;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Cart $cart
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $customerSession
     * @param ProductMapFactory $productMapFactory
     * @param CountryFactory $countryFactory
     * @param RepositoryFactory $repositoryFactory
     * @param Repository $repository
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Cart $cart,
        ScopeConfigInterface $scopeConfig,
        Session $customerSession,
        ProductMapFactory $productMapFactory,
        CountryFactory $countryFactory,
        RepositoryFactory $repositoryFactory,
        Repository $repository
    ) {
        $this->cart = $cart;
        $this->scopeConfig = $scopeConfig;
        $this->repositoryFactory = $repositoryFactory;

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
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        if (false === $this->canHandleECommerceEvent()) {
            return $this;
        }

        $totalPrice = $totalTaxPrice = 0;
        $shopId = $this->scopeConfig->getValue(Config::CONFIG_DATA_SHOP_ID);

        if (empty($shopId)) {
            return $this;
        }

        $requestToGr = [
            'externalId' => (string) $this->cart->getQuote()->getId(),
            'contactId' => (string) $this->getContactFromGetResponse()->contactId,
            'currency' => (string) $this->cart->getQuote()->getQuoteCurrencyCode(),
            'totalPrice' => (float) $totalPrice,
            'totalTaxPrice' => (float) $totalTaxPrice,
            'selectedVariants' => []
        ];

        /** @var Item $magentoCartItem */
        foreach ($this->cart->getQuote()->getAllItems() as $magentoCartItem) {
            if ('simple' !== $magentoCartItem->getProductType()) {
                continue;
            }

            /** @var string $grProduct */
            $grProductId = $this->getProductId($shopId, $magentoCartItem);

            if (0 === strlen($grProductId)) {
                continue;
            }

            $requestToGr['selectedVariants'][] = [
                'variantId' => (string) $grProductId,
                'price' => (float) $magentoCartItem->getPrice(),
                'priceTax' => (float) $magentoCartItem->getPriceInclTax(),
                'quantity' => (integer) $magentoCartItem->getQty(),
            ];

            $totalPrice += ($magentoCartItem->getPrice() * $magentoCartItem->getQty());
            $totalTaxPrice += ($magentoCartItem->getPriceInclTax() * $magentoCartItem->getQty());
        }

        $requestToGr['totalPrice'] = (float) $totalPrice;
        $requestToGr['totalTaxPrice'] = (float) $totalTaxPrice;

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->cart->getQuote();

        try {
            $grRepository = $this->repositoryFactory->createRepository();
        } catch (RepositoryException $e) {
            return $this;
        }

        if (empty($requestToGr['selectedVariants'])) {
            if (!empty($quote->getData('getresponse_cart_id'))) {
                $grRepository->deleteCart(
                    $shopId,
                    $quote->getData('getresponse_cart_id')
                );

                $quote->setData('getresponse_cart_id', '');
                $quote->save();
            }

            return $this;
        }

        if (empty($quote->getData('getresponse_cart_id'))) {
            $response = $grRepository->addCart($shopId, $requestToGr);
            $quote->setData('getresponse_cart_id', $response->cartId);
            $quote->save();
        } else {
            $grRepository->updateCart(
                $shopId,
                $quote->getData('getresponse_cart_id'),
                $requestToGr
            );
        }

        return $this;
    }
}
