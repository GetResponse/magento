<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Product\Variant;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Product\ProductUrlFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GrShareCode\Product\Variant\Variant;
use GrShareCode\Product\Variant\VariantsCollection;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

/**
 * Class ComplexVariantFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Product\Variant
 */
class ComplexVariantFactory
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
     * @param Quote\Item $quoteItem
     * @return VariantsCollection
     */
    public function fromQuoteItem(Quote\Item $quoteItem)
    {
        $variantCollection = new VariantsCollection();

        foreach ($quoteItem->getChildren() as $childQuoteItem) {

            $magentoVariant = $this->magentoRepository->getProductById($childQuoteItem->getProduct()->getId());

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

    /**
     * @param Order\Item $orderItem
     * @return VariantsCollection
     */
    public function fromOrderItem(Order\Item $orderItem)
    {
        $variantCollection = new VariantsCollection();

        foreach ($orderItem->getChildrenItems() as $childOrderItem) {

            $magentoVariant = $this->magentoRepository->getProductById($childOrderItem->getProduct()->getId());

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