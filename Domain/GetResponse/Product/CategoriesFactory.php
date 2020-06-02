<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Product;

use GetResponse\GetResponseIntegration\Domain\Magento\Category\ReadModel\CategoryReadModel;
use GetResponse\GetResponseIntegration\Domain\Magento\Category\ReadModel\Query\CategoryId;
use GrShareCode\Product\Category\Category;
use GrShareCode\Product\Category\CategoryCollection;
use Magento\Catalog\Model\Product;

class CategoriesFactory
{
    private $categoryReadModel;

    public function __construct(CategoryReadModel $categoryReadModel)
    {
        $this->categoryReadModel = $categoryReadModel;
    }

    public function fromProduct(Product $magentoProduct): CategoryCollection
    {
        $categoryCollection = new CategoryCollection();

        foreach ($magentoProduct->getCategoryIds() as $categoryId) {
            $category = $this->categoryReadModel->getCategoryById(
                new CategoryId((int)$categoryId)
            );

            $productCategory = (new Category($category->getName()))
                ->setParentId($category->getParentId())
                ->setUrl($category->getUrl())
                ->setExternalId($category->getId());

            $categoryCollection->add($productCategory);
        }

        return $categoryCollection;
    }
}
