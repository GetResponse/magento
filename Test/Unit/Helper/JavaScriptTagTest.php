<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Helper;

use GetResponse\GetResponseIntegration\Helper\JavaScriptTag;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;

class JavaScriptTagTest extends BaseTestCase
{
    public function testWillGenerateJavascriptSnippetWithNonce()
    {
        $const = 'webEventPayload';
        $payload = json_encode(['key' => 'value', 'pageData' => []]);
        $nonceValue = '1234567890';

        $expectedResult = '<script type="text/javascript" nonce="' . $nonceValue . '">const ' . $const . ' = ' . $payload . '</script>';

        $result = JavaScriptTag::generateForConst($const, $payload, $nonceValue);

        self::assertEquals($expectedResult, $result);
    }

    public function testWillGenerateJavascriptSnippetWithoutNonce()
    {
        $const = 'webEventPayload';
        $payload = json_encode(['key' => 'value', 'pageData' => []]);
        $nonceValue = null;

        $expectedResult = '<script type="text/javascript">const ' . $const . ' = ' . $payload . '</script>';

        $result = JavaScriptTag::generateForConst($const, $payload, $nonceValue);

        self::assertEquals($expectedResult, $result);
    }

    public function testWillGenerateJavascriptSnippetWithEmptyNonce()
    {
        $const = 'webEventPayload';
        $payload = json_encode(['key' => 'value', 'pageData' => []]);
        $nonceValue = '';

        $expectedResult = '<script type="text/javascript">const ' . $const . ' = ' . $payload . '</script>';

        $result = JavaScriptTag::generateForConst($const, $payload, $nonceValue);

        self::assertEquals($expectedResult, $result);
    }
}
