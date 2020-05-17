<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Product\Variant;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\ProductUrlFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Product\ReadModel\ProductReadModel;
use GetResponse\GetResponseIntegration\Domain\Magento\Product\ReadModel\Query\GetProduct;
use GrShareCode\Product\Variant\Variant;
use GrShareCode\Product\Variant\VariantsCollection;
use Magento\Sales\Model\Order\Item;

class SimpleVariantFactory
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

    public function fromQuoteItem(\Magento\Quote\Model\Quote\Item $quoteItem): VariantsCollection
    {
        $variantCollection = new VariantsCollection();
        $magentoProduct = $this->productReadModel->getProduct(
            new GetProduct($quoteItem->getProduct()->getId())
        );

        $amountBase = $magentoProduct->getPriceInfo()->getPrice('final_price')->getAmount();

        $productVariant = new Variant(
            (int)$quoteItem->getProduct()->getId(),
            $quoteItem->getProduct()->getName(),
            (float)$amountBase->getBaseAmount(),
            (float)$amountBase->getValue(),
            $quoteItem->getSku()
        );

        $productVariant
            ->setUrl($this->productUrlFactory->fromProduct($quoteItem->getProduct()))
            ->setQuantity($quoteItem->getQty())
            ->setDescription(mb_substr($magentoProduct->getShortDescription(), 0, Variant::DESCRIPTION_MAX_LENGTH))
            ->setImages(Images\ImagesFactory::fromProduct($magentoProduct));

        $variantCollection->add($productVariant);

        return $variantCollection;
    }

    public function fromOrderItem(Item $orderItem): VariantsCollection
    {
        $variantCollection = new VariantsCollection();
        $magentoProduct = $this->productReadModel->getProduct(
            new GetProduct($orderItem->getProduct()->getId())
        );

        $amountBase = $magentoProduct->getPriceInfo()->getPrice('final_price')->getAmount();

        $productVariant = new Variant(
            (int)$orderItem->getProduct()->getId(),
            $orderItem->getProduct()->getName(),
            (float)$amountBase->getBaseAmount(),
            (float)$amountBase->getValue(),
            $orderItem->getSku()
        );

        $productVariant
            ->setUrl($this->productUrlFactory->fromProduct($orderItem->getProduct()))
            ->setQuantity((int)$orderItem->getQtyOrdered())
            ->setDescription(mb_substr($magentoProduct->getShortDescription(), 0, Variant::DESCRIPTION_MAX_LENGTH))
            ->setImages(Images\ImagesFactory::fromProduct($magentoProduct));

        $variantCollection->add($productVariant);

        return $variantCollection;
    }
}
