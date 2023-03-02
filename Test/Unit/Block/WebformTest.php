<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Block;

use GetResponse\GetResponseIntegration\Block\Webform;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\MockObject\MockObject;

class WebformTest extends BaseTestCase
{
    /** @var Repository|MockObject */
    private $repository;

    /** @var Webform() */
    private $webformBlock;

    protected function setUp(): void
    {
        /** @var Context $context */
        $context = $this->getMockWithoutConstructing(Context::class);
        /** @var MagentoStore $magentoStore */
        $magentoStore = $this->getMockWithoutConstructing(MagentoStore::class);
        $this->repository = $this->getMockWithoutConstructing(Repository::class);

        $this->webformBlock = new Webform(
            $context,
            $magentoStore,
            $this->repository
        );
    }

    /**
     * @test
     * @dataProvider webFormToDisplayProvider
     */
    public function shouldReturnWebFormUrlToDisplay(
        $expectedUrl,
        bool $isEnabled,
        string $sidebar,
        string $placement,
        string $url
    ): void {
        $this->repository
            ->method('getWebformSettings')
            ->willReturn([
                'isEnabled' => $isEnabled,
                'url' => $url,
                'webformId' => '39489383',
                'sidebar' => $sidebar
            ]);

        $webformUrl = $this->webformBlock->getWebFormUrlToDisplay($placement);
        self::assertEquals($expectedUrl, $webformUrl);
    }

    public static function webFormToDisplayProvider(): array
    {
        return [
            [
               'expectedUrl' => null,
                'isEnabled' => false,
                'sidebar' => 'bottom',
                'placement' => 'bottom',
                'url' => 'http://getresponse.com/script.js'
            ],
            [
                'expectedUrl' => null,
                'isEnabled' => false,
                'sidebar' => 'bottom',
                'placement' => 'bottom',
                'url' => 'http://getresponse.com/script.js'
            ],
            [
                'expectedUrl' => null,
                'isEnabled' => true,
                'sidebar' => 'bottom',
                'placement' => 'top',
                'url' => 'http://getresponse.com/script.js'
            ],
            [
                'expectedUrl' => 'http://getresponse.com/script.js',
                'isEnabled' => true,
                'sidebar' => 'bottom',
                'placement' => 'bottom',
                'url' => 'http://getresponse.com/script.js'
            ],
        ];
    }
}
