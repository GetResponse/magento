<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Block;

use GetResponse\GetResponseIntegration\Block\Header;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\MockObject\MockObject;

class HeaderTest extends BaseTestCase
{
    /** @var Repository|MockObject */
    private $repository;

    /** @var Header */
    private $headerBlock;

    public function setUp()
    {
        /** @var Context $context */
        $context = $this->getMockWithoutConstructing(Context::class);
        /** @var MagentoStore $magentoStore */
        $magentoStore = $this->getMockWithoutConstructing(MagentoStore::class);

        $this->repository = $this->getMockWithoutConstructing(Repository::class);

        $this->headerBlock = new Header($context, $this->repository, $magentoStore);
    }

    /**
     * @test
     */
    public function shouldReturnTrackingCodeSnippet()
    {
        $trackingCodeSnippet = 'trackingCodeSnippet';
        $isTrackingCodeEnabled = true;

        $this->repository
            ->expects(self::once())
            ->method('getWebEventTracking')
            ->willReturn(
                [
                    'isEnabled' => $isTrackingCodeEnabled,
                    'isFeatureTrackingEnabled' => true,
                    'codeSnippet' => $trackingCodeSnippet
                ]
            );

        $expected = ['trackingCodeSnippet' => $trackingCodeSnippet];

        self::assertSame($expected, $this->headerBlock->getTrackingData());
    }

    /**
     * @test
     */
    public function shouldReturnEmptyTrackingCodeSnippet()
    {
        $trackingCodeSnippet = 'trackingCodeSnippet';
        $isTrackingCodeEnabled = false;

        $this->repository
            ->expects(self::once())
            ->method('getWebEventTracking')
            ->willReturn(
                [
                    'isEnabled' => $isTrackingCodeEnabled,
                    'isFeatureTrackingEnabled' => true,
                    'codeSnippet' => $trackingCodeSnippet
                ]
            );

        $expected = [
            'trackingCodeSnippet' => ''
        ];
        self::assertSame($expected, $this->headerBlock->getTrackingData());
    }
}
