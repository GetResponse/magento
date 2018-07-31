<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Product;

use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;

/**
 * Class ProductUrlFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Product
 */
class ProductUrlFactory
{
    /** @var Repository */
    private $magentoRepository;

    /**
     * @param Repository $magentoRepository
     */
    public function __construct(Repository $magentoRepository)
    {
        $this->magentoRepository = $magentoRepository;
    }

    /**
     * @param Product $magentoProduct
     * @return null|string
     */
    public function fromProduct(Product $magentoProduct)
    {
        if ((int)$magentoProduct->getVisibility() !== Visibility::VISIBILITY_NOT_VISIBLE) {
            return $magentoProduct->getProductUrl();
        }

        if ($parentProductIds = $this->magentoRepository->getProductParentConfigurableById($magentoProduct->getId())) {
            $magentoParentProduct = $this->magentoRepository->getProductById($parentProductIds[0]);

            return $magentoParentProduct->getProductUrl();
        }

        return null;
    }

}