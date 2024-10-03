<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\CustomerData\Recommendation;

use GetResponse\GetResponseIntegration\Helper\JavaScriptTag;
use GetResponse\GetResponseIntegration\Helper\NonceGenerator;
use Magento\Checkout\Block\Cart as Subject;

class CartView extends RecommendationView
{
    public const FULL_ACTION_NAME = 'checkout_cart_index';
    public const DISPLAY_BLOCK = 'checkout.cart';
    public const PAGE_TYPE = 'cart';

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
