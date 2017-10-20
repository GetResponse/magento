<?php
namespace GetResponse\GetResponseIntegration\Observer;

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
use GetResponse\GetResponseIntegration\Domain\GetResponse\Repository as GrRepository;

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

    /** @var GrRepository */
    private $grRepository;

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
        $this->grRepository = $repositoryFactory->buildRepository();

        parent::__construct($objectManager, $customerSession, $productMapFactory, $countryFactory, $repositoryFactory, $repository);
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

        $requestToGr = [
            'externalId' => $this->cart->getQuote()->getId(),
            'contactId' => $this->getContactFromGetResponse()->contactId,
            'currency' => $this->cart->getQuote()->getQuoteCurrencyCode(),
            'totalPrice' => $totalPrice,
            'totalTaxPrice' => $totalTaxPrice,
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
                'variantId' => $grProductId,
                'price' => $magentoCartItem->getPrice(),
                'priceTax' => $magentoCartItem->getPriceInclTax(),
                'quantity' => $magentoCartItem->getQty(),
            ];

            $totalPrice += ($magentoCartItem->getPrice() * $magentoCartItem->getQty());
            $totalTaxPrice += ($magentoCartItem->getPriceInclTax() * $magentoCartItem->getQty());
        }

        $requestToGr['totalPrice'] = $totalPrice;
        $requestToGr['totalTaxPrice'] = $totalTaxPrice;

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->cart->getQuote();

        if (empty($requestToGr['selectedVariants'])) {
            if (!empty($quote->getData('getresponse_cart_id'))) {

                $this->grRepository->deleteCart(
                    $shopId,
                    $quote->getData('getresponse_cart_id')
                );

                $quote->setData('getresponse_cart_id', '');
                $quote->save();
            }
            return $this;
        }

        if (empty($quote->getData('getresponse_cart_id'))) {
            $response = $this->grRepository->addCart($shopId, $requestToGr);
            $quote->setData('getresponse_cart_id', $response->cartId);
            $quote->save();
        } else {
            $this->grRepository->updateCart(
                $shopId,
                $quote->getData('getresponse_cart_id'),
                $requestToGr
            );
        }
        return $this;
    }
}
