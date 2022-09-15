<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Block\Admin;

use GetResponse\GetResponseIntegration\Block\Admin\WebEventTracking;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTracking as WebEventTrackingSettings;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\MockObject\MockObject;

class WebEventTrackingTest extends BaseTestCase
{
    /** @var Repository|MockObject */
    private $repository;

    /** @var WebEventTracking */
    private $trackingBlock;

    protected function setUp(): void
    {
        /** @var Context $context */
        $context = $this->getMockWithoutConstructing(Context::class);
        $this->repository = $this->getMockWithoutConstructing(Repository::class);

        /** @var MagentoStore $magentoStore */
        $magentoStore = $this->getMockWithoutConstructing(MagentoStore::class);

        $this->trackingBlock = new WebEventTracking(
            $context,
            $magentoStore,
            $this->repository
        );
    }

    /**
     * @test
     */
    public function shouldReturnWebEventTrackingCode(): void
    {
        $isEnabled = true;
        $snippet = '<script>function trackingCodeSnippet() {}</script>';

        $this->repository
            ->expects(self::once())
            ->method('getWebEventTracking')
            ->willReturn([
                'isEnabled' => $isEnabled,
                'isFeatureTrackingEnabled' => true,
                'codeSnippet' => $snippet
            ]);

        $expectedSettings = new WebEventTrackingSettings($isEnabled, true, $snippet);
        $settings = $this->trackingBlock->getWebEventTracking();

        self::assertEquals($expectedSettings, $settings);
    }
}
