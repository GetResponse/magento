<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Product;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\Variant\ComplexVariantFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\Variant\SimpleVariantFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Product\ReadModel\ProductReadModel;
use GetResponse\GetResponseIntegration\Domain\Magento\Product\ReadModel\Query\GetProduct;
use GrShareCode\Product\Product;
use InvalidArgumentException;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Downloadable\Model\Product\Type as DownloadableType;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Model\Order\Item as OrderItem;

class ProductFactory
{
    private $variantFactorySimple;
    private $productUrlFactory;
    private $categoriesFactory;
    private $complexVariantFactory;
    private $productReadModel;

    public function __construct(
        SimpleVariantFactory $variantFactorySimple,
        ComplexVariantFactory $complexVariantFactory,
        ProductUrlFactory $productUrlFactory,
        CategoriesFactory $categoriesFactory,
        ProductReadModel $productReadModel
    ) {
        $this->variantFactorySimple = $variantFactorySimple;
        $this->complexVariantFactory = $complexVariantFactory;
        $this->productUrlFactory = $productUrlFactory;
        $this->categoriesFactory = $categoriesFactory;
        $this->productReadModel = $productReadModel;
    }

    public function fromMagentoQuoteItem(Item $quoteItem)
    {
        switch ($quoteItem->getProductType()) {
            case Configurable::TYPE_CODE:
            case Type::TYPE_BUNDLE:
                $magentoProduct = $this->productReadModel->getProduct(
                    new GetProduct($quoteItem->getProduct()->getId())
                );

                $product = new Product(
                    (int)$quoteItem->getProduct()->getId(),
                    $quoteItem->getProduct()->getName(),
                    $this->complexVariantFactory->fromQuoteItem($quoteItem),
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
                $magentoProduct = $this->productReadModel->getProduct(
                    new GetProduct($quoteItem->getProduct()->getId())
                );

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

    private function getProductIdFromMagentoProduct(MagentoProduct $magentoProduct): int
    {
        if ($magentoProduct->getVisibility() !== Visibility::VISIBILITY_NOT_VISIBLE) {
            return (int)$magentoProduct->getId();
        }

        $magentoParentProduct = $this->productReadModel->getProductParent(
            new GetProduct($magentoProduct->getId())
        );

        return (int)$magentoParentProduct->getId();
    }

    private function getProductNameFromMagentoProduct(MagentoProduct $magentoProduct): string
    {
        if ($magentoProduct->getVisibility() !== Visibility::VISIBILITY_NOT_VISIBLE) {
            return $magentoProduct->getName();
        }

        $magentoParentProduct = $this->productReadModel->getProductParent(
            new GetProduct($magentoProduct->getId())
        );

        return $magentoParentProduct->getName();
    }

    public function fromMagentoOrderItem(OrderItem $orderItem)
    {
        switch ($orderItem->getProductType()) {
            case Configurable::TYPE_CODE:
            case Type::TYPE_BUNDLE:
                $magentoProduct = $this->productReadModel->getProduct(
                    new GetProduct($orderItem->getProduct()->getId())
                );

                $product = new Product(
                    (int)$orderItem->getProduct()->getId(),
                    $orderItem->getProduct()->getName(),
                    $this->complexVariantFactory->fromOrderItem($orderItem),
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
                $magentoProduct = $this->productReadModel->getProduct(
                    new GetProduct($orderItem->getProduct()->getId())
                );

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
