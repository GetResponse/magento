<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Product\Variant;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\ProductUrlFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Product\ReadModel\ProductReadModel;
use GetResponse\GetResponseIntegration\Domain\Magento\Product\ReadModel\Query\GetProduct;
use GrShareCode\Product\Variant\Variant;
use GrShareCode\Product\Variant\VariantsCollection;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class ComplexVariantFactory
{
    private $productUrlFactory;
    private $productReadModel;

    public function __construct(
        ProductUrlFactory $productUrlFactory,
        ProductReadModel $productReadModel
    ) {
        $this->productUrlFactory = $productUrlFactory;
        $this->productReadModel = $productReadModel;
    }

    public function fromQuoteItem(Quote\Item $quoteItem): VariantsCollection
    {
        $variantCollection = new VariantsCollection();

        foreach ($quoteItem->getChildren() as $childQuoteItem) {
            $magentoVariant = $this->productReadModel->getProduct(
                new GetProduct($childQuoteItem->getProduct()->getId())
            );

            $amountBase = $magentoVariant->getPriceInfo()->getPrice('final_price')->getAmount();

            $productVariant = new Variant(
                (int)$magentoVariant->getId(),
                $magentoVariant->getName(),
                (float)$amountBase->getBaseAmount(),
                (float)$amountBase->getValue(),
                $magentoVariant->getSku()
            );

            $productVariant
                ->setUrl($this->productUrlFactory->fromProduct($quoteItem->getProduct()))
                ->setQuantity((int)$quoteItem->getQty())
                ->setDescription(mb_substr($magentoVariant->getShortDescription(), 0, Variant::DESCRIPTION_MAX_LENGTH))
                ->setImages(Images\ImagesFactory::fromProduct($magentoVariant));

            $variantCollection->add($productVariant);
        }

        return $variantCollection;
    }

    public function fromOrderItem(Order\Item $orderItem): VariantsCollection
    {
        $variantCollection = new VariantsCollection();

        foreach ($orderItem->getChildrenItems() as $childOrderItem) {
            $magentoVariant = $this->productReadModel->getProduct(
                new GetProduct($childOrderItem->getProduct()->getId())
            );

            $amountBase = $magentoVariant->getPriceInfo()->getPrice('final_price')->getAmount();

            $productVariant = new Variant(
                (int)$magentoVariant->getId(),
                $magentoVariant->getName(),
                (float)$amountBase->getBaseAmount(),
                (float)$amountBase->getValue(),
                $magentoVariant->getSku()
            );

            $productVariant
                ->setUrl($this->productUrlFactory->fromProduct($orderItem->getProduct()))
                ->setQuantity((int)$orderItem->getQty())
                ->setDescription(mb_substr($magentoVariant->getShortDescription(), 0, Variant::DESCRIPTION_MAX_LENGTH))
                ->setImages(Images\ImagesFactory::fromProduct($magentoVariant));

            $variantCollection->add($productVariant);
        }

        return $variantCollection;
    }

}