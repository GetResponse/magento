<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\ViewModel;

use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTracking;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\StoreManagerInterface;

class PageContext implements ArgumentInterface
{
    /** @var StoreManagerInterface */
    private $storeManager;
    /** @var Repository */
    private $repository;
    /** @var CategoryRepositoryInterface */
    private $categoryRepository;
    /** @var Registry */
    private $registry;
    /** @var RequestInterface */
    private $request;
    /** @var CheckoutSession */
    private $checkoutSession;

    /**
     * @param StoreManagerInterface $storeManager
     * @param Repository $repository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Registry $registry
     * @param RequestInterface $request
     * @param CheckoutSession $checkoutSession
     *
     * @return void
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Repository $repository,
        CategoryRepositoryInterface $categoryRepository,
        Registry $registry,
        RequestInterface $request,
        CheckoutSession $checkoutSession
    ) {
        $this->storeManager = $storeManager;
        $this->repository = $repository;
        $this->categoryRepository = $categoryRepository;
        $this->registry = $registry;
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Get page type
     *
     * @return string
     */
    public function getPageType(): string
    {
        $fullActionName = $this->request->getFullActionName();

        switch ($fullActionName) {
            case 'cms_index_index':
                return 'home';
            case 'catalog_product_view':
                return 'product';
            case 'catalog_category_view':
                return 'category';
            case 'checkout_cart_index':
                return 'cart';
            default:
                return 'other';
        }
    }

    /**
     * Get page context
     *
     * @return array|null
     */
    public function getContext(): ?array
    {
        try {
            $webEventTracking = WebEventTracking::createFromRepository(
                $this->repository->getWebEventTracking($this->storeManager->getStore()->getId())
            );
            $shopId = $webEventTracking->isFeatureTrackingEnabled() ? $webEventTracking->getGetresponseShopId() : null;

            if (null === $shopId) {
                return null;
            }

            $pageType = $this->getPageType();

            switch ($pageType) {
                case 'home':
                    return $this->createResponse($shopId, $pageType);
                case 'product':
                    return $this->createResponse($shopId, $pageType, $this->createProductContext());
                case 'cart':
                    return $this->createResponse($shopId, $pageType, $this->createCartContext());
                case 'category':
                    return $this->createResponse($shopId, $pageType, $this->createCategoryContext());
                default:
                    return $this->createResponse($shopId, 'other');
            }
        } catch (\Throwable $e) {
            return $this->createResponse($shopId, 'other');
        }
    }

    /**
     * Create final response
     *
     * @param string $shopId
     * @param string $pageType
     * @param array $context
     * @return array
     */
    private function createResponse(string $shopId, string $pageType, array $context = []): array
    {
        return [
            'page' => ['type' => $pageType],
            'context' => array_merge(
                ['type' => $pageType],
                ['shop' => ['id' => $shopId]],
                $context
            ),
        ];
    }

    /**
     * Create partial response
     *
     * @return array|array[]
     */
    private function createProductContext()
    {
        $product = $this->registry->registry('current_product');

        if (!$product instanceof Product) {
            return [];
        }

        $categories = [];
        foreach ($product->getCategoryIds() as $categoryId) {
            $category = $this->categoryRepository->get($categoryId, $product->getStoreId());
            $categories[] = ['id' => $category->getId(), 'name' => $category->getName()];
        }

        return [
            'product' => [
                'id' => (string) $product->getId(),
                'name' => $product->getName(),
                'sku' => $product->getSku(),
                'price' => (float)number_format($product->getPriceInfo()->getPrice('final_price')->getValue(), 2),
                'currency' => $this->storeManager->getStore()->getCurrentCurrencyCode(),
                'categories' => $categories,
            ]
        ];
    }

    /**
     * Create partial response
     *
     * @return array[]
     */
    private function createCartContext()
    {
        return [
            'cart' => [
                'total' => number_format((float)$this->checkoutSession->getQuote()->getGrandTotal(), 2),
                'currency' => $this->storeManager->getStore()->getCurrentCurrencyCode(),
                'items' => array_map(
                    function (Item $item) {
                        $option = $item->getOptionByCode('simple_product');
                        $variantId = $option ? $option->getProduct()->getId() : null;

                        return [
                            'productId' => (string) $item->getProduct()->getId(),
                            'variantId' => $variantId,
                            'quantity' => (int) $item->getQty(),
                            'name' => (string) $item->getName(),
                            'sku' => $item->getSku(),
                            'price' => (float) number_format($item->getCalculationPrice(), 2),
                            'lineTotal' => (float) number_format((float)$item->getRowTotal(), 2),
                        ];
                    },
                    $this->checkoutSession->getQuote()->getAllVisibleItems()
                ),
            ],
        ];
    }

    /**
     * Create partial response
     *
     * @return array|array[]
     */
    private function createCategoryContext()
    {
        $category = $this->registry->registry('current_category');
        if (!$category instanceof Category) {
            return [];
        }

        return [
            'category' => [
                'id' => (string) $category->getId(),
                'name' => (string) $category->getName(),
            ],
        ];
    }
}
