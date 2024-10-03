<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\CustomerData\Recommendation;

use GetResponse\GetResponseIntegration\Helper\JavaScriptTag;
use Magento\Cms\Block\Page as Subject;

class HomePageView extends RecommendationView
{
    public const FULL_ACTION_NAME = 'cms_index_index';
    public const DISPLAY_BLOCK = 'cms_page';
    public const PAGE_TYPE = 'home';

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
