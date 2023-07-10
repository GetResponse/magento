<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\CustomerData\TrackingCode;

use Magento\Catalog\Block\Category\View as Subject;

class CategoryView extends TrackingCodeView
{
    const DISPLAY_BLOCK = 'category.cms';

    public function afterToHtml(Subject $subject, string $html): string
    {
        $category = $subject->getCurrentCategory();

        if (false === $this->isAllowed($subject, $category->getStoreId())) {
            return $html;
        }

        $payload = [
            'shop' => ['id' => $this->getGetresponseShopId($category->getStoreId())],
            'id' => $category->getId(),
            'name' => $category->getName()
        ];

        $html .= '<script type="text/javascript">const GrViewCategoryItem = ' . json_encode($payload) . '</script>';

        return $html;
    }

    protected function getBlockName(): string
    {
        return self::DISPLAY_BLOCK;
    }
}
