<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Product;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\Variant\VariantsFactoryComplex;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\Variant\VariantsFactorySimple;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GrShareCode\Product\Product;
use Magento\Quote\Model\Quote\Item;

/**
 * Class ProductFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Product
 */
class ProductFactory
{
    /** @var Repository */
    private $magentoRepository;

    /** @var VariantsFactorySimple */
    private $variantFactorySimple;

    /** @var ProductUrlFactory */
    private $productUrlFactory;

    /** @var CategoriesFactory */
    private $categoriesFactory;

    /** @var VariantsFactoryComplex */
    private $variantsFactoryComplex;

    /**
     * @param Repository $magentoRepository
     * @param VariantsFactorySimple $variantFactorySimple
     * @param VariantsFactoryComplex $variantsFactoryComplex
     * @param ProductUrlFactory $productUrlFactory
     * @param CategoriesFactory $categoriesFactory
     */
    public function __construct(
        Repository $magentoRepository,
        VariantsFactorySimple $variantFactorySimple,
        VariantsFactoryComplex $variantsFactoryComplex,
        ProductUrlFactory $productUrlFactory,
        CategoriesFactory $categoriesFactory
    ) {
        $this->magentoRepository = $magentoRepository;
        $this->variantFactorySimple = $variantFactorySimple;
        $this->variantsFactoryComplex = $variantsFactoryComplex;
        $this->productUrlFactory = $productUrlFactory;
        $this->categoriesFactory = $categoriesFactory;
    }

    /**
     * @param Item $quoteItem
     * @return Product
     */
    public function fromMagentoQuoteItem(Item $quoteItem)
    {
        switch ($quoteItem->getProductType()) {

            case \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE:
            case \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE:

                $magentoProduct = $this->magentoRepository->getProductById($quoteItem->getProduct()->getId());

                $product = new Product(
                    (int)$quoteItem->getProduct()->getId(),
                    $quoteItem->getProduct()->getName(),
                    $this->variantsFactoryComplex->fromQuoteItem($quoteItem),
                    $this->categoriesFactory->fromProduct($magentoProduct)
                );

                $product->setUrl(
                    $this->productUrlFactory->fromProduct($quoteItem->getProduct())
                );

                return $product;

            case \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE:
            case \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL:
            case \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE:
            case \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE:

                $magentoProduct = $this->magentoRepository->getProductById($quoteItem->getProduct()->getId());

                $product = new Product(
                    $this->getProductIdFromMagentoProduct($magentoProduct),
                    $this->getProductNameFromMagentoProduct($magentoProduct),
                    $this->variantFactorySimple->fromQuoteItem($quoteItem),
                    $this->categoriesFactory->fromProduct($magentoProduct)
                );

                $product->setUrl(
                    $this->productUrlFactory->fromProduct($quoteItem->getProduct())
                );

                return $product;

            default:
                throw new \InvalidArgumentException('Invalid Quote type.');
        }
    }

    /**
     * @param \Magento\Catalog\Model\Product\Interceptor $magentoProduct
     * @return int
     */
    private function getProductIdFromMagentoProduct(\Magento\Catalog\Model\Product\Interceptor $magentoProduct)
    {
        if ((int)$magentoProduct->getVisibility() === \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE) {

            if ($parentProductIds = $this->magentoRepository->getProductParentConfigurableById($magentoProduct->getId())) {
                $magentoParentProduct = $this->magentoRepository->getProductById($parentProductIds[0]);

                return (int)$magentoParentProduct->getId();
            }
        }

        return (int)$magentoProduct->getId();
    }

    /**
     * @param \Magento\Catalog\Model\Product\Interceptor $magentoProduct
     * @return string
     */
    private function getProductNameFromMagentoProduct(\Magento\Catalog\Model\Product\Interceptor $magentoProduct)
    {
        if ((int)$magentoProduct->getVisibility() === \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE) {

            if ($parentProductIds = $this->magentoRepository->getProductParentConfigurableById($magentoProduct->getId())) {
                $magentoParentProduct = $this->magentoRepository->getProductById($parentProductIds[0]);

                return $magentoParentProduct->getName();
            }
        }

        return $magentoProduct->getName();
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @return Product
     */
    public function fromMagentoOrderItem(\Magento\Sales\Model\Order\Item $orderItem)
    {
        switch ($orderItem->getProductType()) {

            case \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE:
            case \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE:

                $magentoProduct = $this->magentoRepository->getProductById($orderItem->getProduct()->getId());

                $product = new Product(
                    (int)$orderItem->getProduct()->getId(),
                    $orderItem->getProduct()->getName(),
                    $this->variantsFactoryComplex->fromOrderItem($orderItem),
                    $this->categoriesFactory->fromProduct($magentoProduct)
                );

                $product->setUrl(
                    $this->productUrlFactory->fromProduct($orderItem->getProduct())
                );

                return $product;

            case \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE:
            case \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL:
            case \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE:
            case \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE:

                $magentoProduct = $this->magentoRepository->getProductById($orderItem->getProduct()->getId());

                $product = new Product(
                    $this->getProductIdFromMagentoProduct($magentoProduct),
                    $this->getProductNameFromMagentoProduct($magentoProduct),
                    $this->variantFactorySimple->fromOrderItem($orderItem),
                    $this->categoriesFactory->fromProduct($magentoProduct)
                );

                $product->setUrl(
                    $this->productUrlFactory->fromProduct($orderItem->getProduct())
                );

                return $product;

            default:
                throw new \InvalidArgumentException('Invalid Quote Type.');

        }

    }

}