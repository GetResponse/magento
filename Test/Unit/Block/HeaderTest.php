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

    public function setUp(): void
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
    public function shouldReturnTrackingCodeSnippet(): void
    {
        $trackingCodeSnippet = 'trackingCodeSnippet';
        $facebookPixelSnippet = 'facebookPixelSnippet';
        $facebookAdsPixelSnippet = 'facebookAdsPixelSnippet';
        $facebookBusinessExtensionSnippet = 'facebookBusinessExtensionSnippet';
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

        $this->repository
            ->expects(self::once())
            ->method('getFacebookPixelSnippet')
            ->willReturn(
                [
                    'isEnabled' => $isTrackingCodeEnabled,
                    'codeSnippet' => $facebookPixelSnippet
                ]
            );

        $this->repository
            ->expects(self::once())
            ->method('getFacebookAdsPixelSnippet')
            ->willReturn(
                [
                    'isEnabled' => $isTrackingCodeEnabled,
                    'codeSnippet' => $facebookAdsPixelSnippet
                ]
            );

        $this->repository
            ->expects(self::once())
            ->method('getFacebookBusinessExtensionSnippet')
            ->willReturn(
                [
                    'isEnabled' => $isTrackingCodeEnabled,
                    'codeSnippet' => $facebookBusinessExtensionSnippet
                ]
            );

        $expected = [
            'trackingCodeSnippet' => $trackingCodeSnippet,
            'facebookPixelCodeSnippet' => $facebookPixelSnippet,
            'facebookAdsPixelCodeSnippet' => $facebookAdsPixelSnippet,
            'facebookBusinessExtensionCodeSnippet' => $facebookBusinessExtensionSnippet
        ];

        self::assertSame($expected, $this->headerBlock->getTrackingData());
    }

    /**
     * @test
     */
    public function shouldReturnEmptyTrackingCodeSnippet(): void
    {
        $trackingCodeSnippet = 'trackingCodeSnippet';
        $facebookPixelCodeSnippet = 'facebookPixelCodeSnippet';
        $facebookAdsPixelSnippet = 'facebookAdsPixelSnippet';
        $facebookBusinessExtensionSnippet = 'facebookBusinessExtensionSnippet';

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

        $this->repository
            ->expects(self::once())
            ->method('getFacebookPixelSnippet')
            ->willReturn(
                [
                    'isEnabled' => $isTrackingCodeEnabled,
                    'codeSnippet' => $facebookPixelCodeSnippet
                ]
            );

        $this->repository
            ->expects(self::once())
            ->method('getFacebookAdsPixelSnippet')
            ->willReturn(
                [
                    'isEnabled' => $isTrackingCodeEnabled,
                    'codeSnippet' => $facebookAdsPixelSnippet
                ]
            );

        $this->repository
            ->expects(self::once())
            ->method('getFacebookBusinessExtensionSnippet')
            ->willReturn(
                [
                    'isEnabled' => $isTrackingCodeEnabled,
                    'codeSnippet' => $facebookBusinessExtensionSnippet
                ]
            );

        $expected = [
            'trackingCodeSnippet' => null,
            'facebookPixelCodeSnippet' => null,
            'facebookAdsPixelCodeSnippet' => null,
            'facebookBusinessExtensionCodeSnippet' => null
        ];
        self::assertSame($expected, $this->headerBlock->getTrackingData());
    }
}
