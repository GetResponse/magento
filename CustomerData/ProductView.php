<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\CustomerData;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\View as Subject;
use Magento\Catalog\Model\Product;
use Magento\Store\Model\StoreManagerInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurable;
use Exception;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;

class ProductView extends WebEventView
{
    const DISPLAY_BLOCK = 'product.info';

    private CategoryRepositoryInterface $categoryRepository;

    public function __construct(
        Repository $repository,
        CategoryRepositoryInterface $categoryRepository
    ) {
        parent::__construct($repository);
        $this->categoryRepository = $categoryRepository;
    }

    public function afterToHtml(Subject $subject, string $html): string
    {
        $product = $subject->getProduct();

        if (false === $this->isAllowed($subject, $product->getStoreId())) {
            return $html;
        }

        if ($product->getTypeId() === TypeConfigurable::TYPE_CODE) {
            $usedProducts = $product->getTypeInstance()->getUsedProducts($product);

            $payload = count($usedProducts) > 0 ? $this->getProductPayload($usedProducts[0]) : [];
        } else {
            $payload = $this->getProductPayload($product);
        }

        $html .= '<div id="getresponse-product-view" data-json=\'' . json_encode($payload) . '\'></div>';

        return $html;
    }

    private function getProductPayload(Product $product): array
    {
        try {
            $categories = [];
            foreach ($product->getCategoryIds() as $categoryId) {
                $category = $this->categoryRepository->get($categoryId, $product->getStoreId());
                $categories[] = ['id' => $category->getId(), 'name' => $category->getName()];
            }

            return [
                'shop' => ['id' => $this->getGetresponseShopId($product->getStoreId())],
                'product' => [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'sku' => $product->getSku(),
                    'vendor' => null,
                    'price' => number_format((float)$product->getPrice(), 2),
                    'currency' => $product->getStore()->getBaseCurrencyCode()
                ],
                'categories' => $categories
            ];
        } catch (Exception $e) {
            return [];
        }
    }

    protected function getBlockName(): string
    {
        return self::DISPLAY_BLOCK;
    }
}
