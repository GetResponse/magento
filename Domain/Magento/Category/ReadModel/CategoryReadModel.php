<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento\Category\ReadModel;

use GetResponse\GetResponseIntegration\Domain\Magento\Category\ReadModel\Query\CategoryId;
use Magento\Catalog\Model\Category;
use Magento\Framework\ObjectManagerInterface;

class CategoryReadModel
{
    private $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function getCategoryById(CategoryId $categoryId): Category
    {
        return $this->objectManager->create(Category::class)->load($categoryId->getId());
    }
}
