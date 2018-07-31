<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Product\Variant;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\ProductUrlFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GrShareCode\Product\Variant\Variant;
use GrShareCode\Product\Variant\VariantsCollection;

/**
 * Class VariantFactorySimple
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Product\Variant
 */
class VariantsFactorySimple
{
    /** @var Repository */
    private $magentoRepository;

    /** @var ProductUrlFactory */
    private $productUrlFactory;

    /**
     * @param Repository $magentoRepository
     * @param ProductUrlFactory $productUrlFactory
     */
    public function __construct(Repository $magentoRepository, ProductUrlFactory $productUrlFactory)
    {
        $this->magentoRepository = $magentoRepository;
        $this->productUrlFactory = $productUrlFactory;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @return VariantsCollection
     */
    public function fromQuoteItem(\Magento\Quote\Model\Quote\Item $quoteItem)
    {
        $variantCollection = new VariantsCollection();
        $magentoProduct = $this->magentoRepository->getProductById($quoteItem->getProduct()->getId());

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

    /**
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @return VariantsCollection
     */
    public function fromOrderItem(\Magento\Sales\Model\Order\Item $orderItem)
    {
        $variantCollection = new VariantsCollection();
        $magentoProduct = $this->magentoRepository->getProductById($orderItem->getProduct()->getId());

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