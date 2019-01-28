<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Product;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\Variant\ComplexVariantFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\Variant\SimpleVariantFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GrShareCode\Product\Product;
use InvalidArgumentException;
use Magento\Catalog\Model\Product\Interceptor;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Downloadable\Model\Product\Type as DownloadableType;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Model\Order\Item as OrderItem;

/**
 * Class ProductFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Product
 */
class ProductFactory
{
    /** @var Repository */
    private $magentoRepository;

    /** @var SimpleVariantFactory */
    private $variantFactorySimple;

    /** @var ProductUrlFactory */
    private $productUrlFactory;

    /** @var CategoriesFactory */
    private $categoriesFactory;

    /** @var ComplexVariantFactory */
    private $variantsFactoryComplex;

    /**
     * @param Repository $magentoRepository
     * @param SimpleVariantFactory $variantFactorySimple
     * @param ComplexVariantFactory $variantsFactoryComplex
     * @param ProductUrlFactory $productUrlFactory
     * @param CategoriesFactory $categoriesFactory
     */
    public function __construct(
        Repository $magentoRepository,
        SimpleVariantFactory $variantFactorySimple,
        ComplexVariantFactory $variantsFactoryComplex,
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

            case Configurable::TYPE_CODE:
            case Type::TYPE_BUNDLE:

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

            case Type::TYPE_SIMPLE:
            case Type::TYPE_VIRTUAL:
            case Grouped::TYPE_CODE:
            case DownloadableType::TYPE_DOWNLOADABLE:

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
                throw new InvalidArgumentException('Invalid Quote type.');
        }
    }

    /**
     * @param Interceptor $magentoProduct
     * @return int
     */
    private function getProductIdFromMagentoProduct(Interceptor $magentoProduct)
    {
        if ((int)$magentoProduct->getVisibility() === Visibility::VISIBILITY_NOT_VISIBLE) {

            if ($parentProductIds = $this->magentoRepository->getProductParentConfigurableById($magentoProduct->getId())) {
                $magentoParentProduct = $this->magentoRepository->getProductById($parentProductIds[0]);

                return (int)$magentoParentProduct->getId();
            }
        }

        return (int)$magentoProduct->getId();
    }

    /**
     * @param Interceptor $magentoProduct
     * @return string
     */
    private function getProductNameFromMagentoProduct(Interceptor $magentoProduct)
    {
        if ((int)$magentoProduct->getVisibility() === Visibility::VISIBILITY_NOT_VISIBLE) {

            if ($parentProductIds = $this->magentoRepository->getProductParentConfigurableById($magentoProduct->getId())) {
                $magentoParentProduct = $this->magentoRepository->getProductById($parentProductIds[0]);

                return $magentoParentProduct->getName();
            }
        }

        return $magentoProduct->getName();
    }

    /**
     * @param OrderItem $orderItem
     * @return Product
     */
    public function fromMagentoOrderItem(OrderItem $orderItem)
    {
        switch ($orderItem->getProductType()) {

            case Configurable::TYPE_CODE:
            case Type::TYPE_BUNDLE:

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

            case Type::TYPE_SIMPLE:
            case Type::TYPE_VIRTUAL:
            case Grouped::TYPE_CODE:
            case DownloadableType::TYPE_DOWNLOADABLE:


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
                throw new InvalidArgumentException('Invalid Quote Type.');

        }

    }

}