<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Product;

use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GrShareCode\Product\Category\Category;
use GrShareCode\Product\Category\CategoryCollection;
use Magento\Catalog\Model\Product;

/**
 * Class CategoriesFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Product
 */
class CategoriesFactory
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
     * @return CategoryCollection
     */
    public function fromProduct(Product $magentoProduct)
    {
        $categoryCollection = new CategoryCollection();

        foreach ($magentoProduct->getCategoryIds() as $categoryId) {

            $category = $this->magentoRepository->getCategoryById($categoryId);

            $productCategory = (new Category($category->getName()))
                ->setParentId($category->getParentId())
                ->setUrl($category->getUrl())
                ->setExternalId($category->getId());

            $categoryCollection->add($productCategory);

        }

        return $categoryCollection;
    }

}