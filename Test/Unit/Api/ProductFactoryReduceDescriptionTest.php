<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Api;

use GetResponse\GetResponseIntegration\Api\ProductFactory;
use GetResponse\GetResponseIntegration\Api\ProductType;
use GetResponse\GetResponseIntegration\Domain\Magento\Product\ReadModel\ProductReadModel;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Catalog\Model\CategoryRepository;
use ReflectionClass;

/**
 * @covers \GetResponse\GetResponseIntegration\Api\ProductFactory
 */
class ProductFactoryReduceDescriptionTest extends BaseTestCase
{
    /** @var ProductFactory */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductFactory(
            $this->createMock(CategoryRepository::class),
            $this->createMock(ProductReadModel::class),
            $this->createMock(ProductType::class)
        );
    }

    /**
     * @test
     * @dataProvider descriptionDataProvider
     */
    public function shouldReduceDescription(string $input, int $maxLength, string $expected): void
    {
        $result = $this->invokePrivateMethod($this->sut, 'reduceDescription', [$input, $maxLength]);

        self::assertEquals($expected, $result);
    }

    public function descriptionDataProvider(): array
    {
        return [
            'simple string within limit' => [
                'input' => 'Simple description',
                'maxLength' => 100,
                'expected' => 'Simple description'
            ],
            'html tags stripping' => [
                'input' => '<p><strong>Bold</strong> text</p><br/>',
                'maxLength' => 100,
                'expected' => 'Bold text'
            ],
            'script and style removal' => [
                'input' => 'Text <script>alert("xss")</script> <style>body { color: red; }</style> content',
                'maxLength' => 100,
                'expected' => 'Text content'
            ],
            'whitespace normalization' => [
                'input' => "  Text   with  \n  multiple   spaces  ",
                'maxLength' => 100,
                'expected' => 'Text with multiple spaces'
            ],
            'html entity decoding' => [
                'input' => 'Text &amp; &quot;Quotes&quot;',
                'maxLength' => 100,
                'expected' => 'Text & "Quotes"'
            ],
            'truncation with ellipsis' => [
                'input' => 'This is a very long description',
                'maxLength' => 10,
                'expected' => 'This is...'
            ],
            'truncation exact length' => [
                'input' => '1234567890',
                'maxLength' => 5,
                'expected' => '12...'
            ],
            'empty string' => [
                'input' => '',
                'maxLength' => 100,
                'expected' => ''
            ],
            'complex html with entities and truncation' => [
                'input' => 'A<script>alert(1)</script>B<style>.x{}</style> C &amp; D <b>Bold</b>   E&nbsp;&nbsp;F  ',
                'maxLength' => 10,
                'expected' => 'A B C &...'
            ],
            'complex combination with truncation' => [
                'input' => '<p>  Start  </p> <script>var x=1;</script> Middle &amp; End',
                'maxLength' => 15,
                'expected' => 'Start Middle...'
            ]
        ];
    }

    private function invokePrivateMethod($object, string $methodName, array $parameters = [])
    {
        $reflection = new ReflectionClass($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
