<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\CustomerData\Recommendation;

use GetResponse\GetResponseIntegration\Helper\JavaScriptTag;
use Magento\Catalog\Block\Category\View as Subject;

class CategoryView extends RecommendationView
{
    public const DISPLAY_BLOCK = 'category.cms';
    public const FULL_ACTION_NAME = 'catalog_category_view';
    public const PAGE_TYPE = 'category';

    public function afterToHtml(Subject $subject, string $html): string
    {
        if (false === $this->isAllowed($subject)) {
            return $html;
        }

        $payload = [
            'pageType' => self::PAGE_TYPE,
            'pageData' => []
        ];

        $html .= JavaScriptTag::generateForConst('recommendationPayload', json_encode($payload), $this->cspNonceProvider->generateNonce());

        return $html;
    }

    protected function getBlockName(): string
    {
        return self::DISPLAY_BLOCK;
    }

    protected function getFullActionName(): string
    {
        return self::FULL_ACTION_NAME;
    }
}
