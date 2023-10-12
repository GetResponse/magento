<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\CustomerData\TrackingCode;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\View as Subject;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurable;
use Exception;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;

class ProductView extends TrackingCodeView
{
    const DISPLAY_BLOCK = 'product.info';

    private $categoryRepository;

    public function __construct(Repository $repository, CategoryRepositoryInterface $categoryRepository)
    {
        parent::__construct($repository);
        $this->categoryRepository = $categoryRepository;
    }

    public function afterToHtml(Subject $subject, string $html): string
    {
        $product = $subject->getProduct();

        if ($product === null || false === $this->isAllowed($subject, $product->getStoreId())) {
            return $html;
        }

        if ($product->getTypeId() === TypeConfigurable::TYPE_CODE) {
            $usedProducts = $product->getTypeInstance()->getUsedProducts($product);

            $payload = count($usedProducts) > 0 ? $this->getProductPayload($usedProducts[0]) : [];
        } else {
            $payload = $this->getProductPayload($product);
        }

        $html .= '<script type="text/javascript">const GrViewProductItem = ' . json_encode($payload) . '</script>';

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

            $finalPrice = $product->getPriceInfo()->getPrice('final_price')->getValue();

            return [
                'shop' => ['id' => $this->getGetresponseShopId($product->getStoreId())],
                'product' => [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'sku' => $product->getSku(),
                    'vendor' => null,
                    'price' => number_format((float)$finalPrice, 2),
                    'currency' => $product->getStore()->getCurrentCurrencyCode()
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
