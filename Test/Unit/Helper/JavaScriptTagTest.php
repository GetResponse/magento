<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Helper;

use GetResponse\GetResponseIntegration\CustomerData\Recommendation\BlogPageView;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Helper\CspNonceProviderFactory;
use GetResponse\GetResponseIntegration\Helper\JavaScriptTag;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Framework\App\Request\Http;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Cms\Block\Page as Subject;

class JavaScriptTagTest extends BaseTestCase
{
    public function testWillGenerateJavascriptSnippetWithNonce()
    {
        $const = 'recommendationPayload';
        $payload = json_encode(['key' => 'value', 'pageData' => []]);
        $nonceValue = '1234567890';

        $expectedResult = '<script type="text/javascript" nonce="' . $nonceValue . '">const ' . $const . ' = ' . $payload . '</script>';

        $result = JavaScriptTag::generateForConst($const, $payload, $nonceValue);

        self::assertEquals($expectedResult, $result);
    }

    public function testWillGenerateJavascriptSnippetWithoutNonce()
    {
        $const = 'recommendationPayload';
        $payload = json_encode(['key' => 'value', 'pageData' => []]);
        $nonceValue = null;

        $expectedResult = '<script type="text/javascript">const ' . $const . ' = ' . $payload . '</script>';

        $result = JavaScriptTag::generateForConst($const, $payload, $nonceValue);

        self::assertEquals($expectedResult, $result);
    }

    public function testWillGenerateJavascriptSnippetWithEmptyNonce()
    {
        $const = 'recommendationPayload';
        $payload = json_encode(['key' => 'value', 'pageData' => []]);
        $nonceValue = '';

        $expectedResult = '<script type="text/javascript">const ' . $const . ' = ' . $payload . '</script>';

        $result = JavaScriptTag::generateForConst($const, $payload, $nonceValue);

        self::assertEquals($expectedResult, $result);
    }
}
