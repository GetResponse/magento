<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\CustomerData\Recommendation;

use Exception;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\CspNonceProviderFactory;
use GetResponse\GetResponseIntegration\Helper\JavaScriptTag;
use GetResponse\GetResponseIntegration\Helper\JsScriptTagGenerator;
use Magento\Catalog\Block\Product\View as Subject;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Request\Http;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Csp\Helper\CspNonceProvider;

class ProductView extends RecommendationView
{
    public const FULL_ACTION_NAME = 'catalog_product_view';
    public const DISPLAY_BLOCK = 'product.info';
    public const PAGE_TYPE = 'product';

    private $categoryRepository;

    public function __construct(
        StoreManagerInterface $storeManager,
        Repository $repository,
        Http $request,
        CategoryRepositoryInterface $categoryRepository,
        CspNonceProviderFactory $cspNonceProviderFactory
    )
    {
        parent::__construct($storeManager, $repository, $request, $cspNonceProviderFactory);

        $this->categoryRepository = $categoryRepository;
    }


    public function afterToHtml(Subject $subject, string $html): string
    {
        if (false === $this->isAllowed($subject)) {
            return $html;
        }

        $rawProduct = $this->getProductPayload($subject->getProduct());

        if (empty($rawProduct)) {
            return $html;
        }

        $payload = [
            'pageType' => self::PAGE_TYPE,
            'pageData' => $rawProduct
        ];

        $html .= JavaScriptTag::generateForConst('recommendationPayload', json_encode($payload), $this->cspNonceProvider->generateNonce());

        return $html;
    }

    private function getProductPayload(Product $product, ?Product $parentProduct = null): array
    {
        $specialPrice = $product->getSpecialPrice();
        $productPrice = $product->getPrice();

        $price = $specialPrice !== null && $productPrice !== $specialPrice ? $specialPrice : $productPrice;
        $originalPrice = $specialPrice !== null && $productPrice !== $specialPrice ? $productPrice : "";

        if (null !== $parentProduct) {
            $isSalable = $product->isSalable() && $parentProduct->isSalable();
            $productName = $parentProduct->getName();
            $productUrl = $parentProduct->getUrlModel()->getUrlInStore($parentProduct, ['_escape' => true]);
            $description = $this->getDescriptionWithoutHtmlTags($parentProduct->getDescription());
            $imageUrl = $this->getImageUrl($parentProduct);
            $categories = $this->getCategories($parentProduct);
        } else {
            $isSalable = $product->isSalable();
            $productName = $product->getName();
            $productUrl = $product->getUrlModel()->getUrlInStore($product, ['_escape' => true]);
            $description = $this->getDescriptionWithoutHtmlTags($product->getDescription());
            $imageUrl = $this->getImageUrl($product);
            $categories = $this->getCategories($product);
        }

        try {
            return [
                'productUrl' => $productUrl,
                'pageUrl' => $productUrl,
                'productExternalId' => (string) $product->getId(),
                'productName' => $productName,
                'price' => number_format((float) $price, 2, '.', ''),
                'imageUrl' => $imageUrl,
                'description' => $description,
                'category' => $categories,
                'available' => $isSalable,
                'sku' => $product->getSku(),
                'attribute1' => number_format((float) $originalPrice, 2, '.', ''),
                'attribute2' => "",
                'attribute3' => "",
                'attribute4' => ""
            ];
        } catch (Exception $e) {
            return [];
        }
    }

    protected function getBlockName(): string
    {
        return self::DISPLAY_BLOCK;
    }

    protected function getFullActionName(): string
    {
        return self::FULL_ACTION_NAME;
    }

    private function getDescriptionWithoutHtmlTags(?string $description): string
    {
        if (null === $description) {
            return '';
        }

        $description = strlen($description) > 30000
            ? substr($description, 0, 30000 - 3) . '...'
            : $description;

        return str_replace(['\n', '\r'], '', strip_tags($description));
    }

    private function getCategories(Product $product): string
    {
        $categories = [];
        foreach ($product->getCategoryIds() as $categoryId) {
            $category = $this->categoryRepository->get($categoryId, $product->getStoreId());
            $categories[] = $category->getName();
        }

        return implode(' > ', $categories);
    }

    private function getImageUrl(Product $product): string
    {
        $images = [];
        foreach ($product->getMediaGalleryImages() as $image) {
            $images[] = $image->getData('url');
        }

        return !empty($images) ? $images[0] : '';
    }
}
