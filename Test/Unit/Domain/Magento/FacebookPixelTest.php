<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\Magento;

use GetResponse\GetResponseIntegration\Domain\Magento\FacebookPixel;
use GetResponse\GetResponseIntegration\Domain\Magento\RequestValidationException;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;

class FacebookPixelTest extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldCreateFacebookPixel()
    {
        $isActive = true;
        $snippet = '<script>alert("1")</script>';

        $facebookPixel = new FacebookPixel($isActive, $snippet);

        self::assertEquals($isActive, $facebookPixel->isActive());
        self::assertEquals($snippet, $facebookPixel->getCodeSnippet());
    }

    /**
     * @test
     */
    public function shouldCreateFacebookPixelFromRepository()
    {
        $facebookPixel = FacebookPixel::createFromRepository([]);

        self::assertEquals(false, $facebookPixel->isActive());
        self::assertEquals('', $facebookPixel->getCodeSnippet());

        $isActive = true;
        $snippet = '<script>alert("1")</script>';

        $data = [
            'isEnabled' => (int)$isActive,
            'codeSnippet' => $snippet
        ];

        $facebookPixel = FacebookPixel::createFromRepository($data);

        self::assertEquals($isActive, $facebookPixel->isActive());
        self::assertEquals($snippet, $facebookPixel->getCodeSnippet());
    }

    /**
     * @test
     */
    public function shouldCreateFacebookPixelFromRequest()
    {
        $isActive = true;
        $snippet = '<script>alert("1")</script>';

        $data = [
            'facebookPixel' => [
                'isActive' => $isActive,
                'codeSnippet' => $snippet
            ]
        ];

        $facebookPixel = FacebookPixel::createFromRequest($data);

        self::assertEquals($isActive, $facebookPixel->isActive());
        self::assertEquals($snippet, $facebookPixel->getCodeSnippet());
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenCreateFacebookPixelFromRequest()
    {
        $this->expectException(RequestValidationException::class);
        FacebookPixel::createFromRequest([]);
    }
}
