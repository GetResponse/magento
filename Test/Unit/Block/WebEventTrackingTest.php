<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Block;

use GetResponse\GetResponseIntegration\Block\WebEventTracking;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTrackingSettings;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class WebEventTrackingTest
 * @package GetResponse\GetResponseIntegration\Test\Unit\Block
 */
class WebEventTrackingTest extends BaseTestCase
{
    /** @var Context|\PHPUnit_Framework_MockObject_MockObject */
    private $context;

    /** @var Repository|\PHPUnit_Framework_MockObject_MockObject */
    private $repository;

    /** @var ApiClientFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $apiClientFactory;

    /** @var WebEventTracking */
    private $trackingBlock;

    public function setUp()
    {
        $this->context = $this->getMockWithoutConstructing(Context::class);
        $this->repository = $this->getMockWithoutConstructing(Repository::class);
        $this->apiClientFactory = $this->getMockWithoutConstructing(ApiClientFactory::class);
        $this->trackingBlock = new WebEventTracking($this->context, $this->repository);
    }

    /**
     * @test
     *
     * @param array $rawTracking
     * @param WebEventTrackingSettings $expectedWebEventTrackingSettings
     *
     * @dataProvider shouldReturnWebEventTrackingCodeProvider
     */
    public function shouldReturnWebEventTrackingCode(array $rawTracking, WebEventTrackingSettings $expectedWebEventTrackingSettings)
    {
        $this->repository->expects($this->atLeastOnce())->method('getWebEventTracking')->willReturn($rawTracking);
        $trackingSettings = $this->trackingBlock->getWebEventTracking();

        self::assertEquals($expectedWebEventTrackingSettings, $trackingSettings);
    }

    /**
     * @return array
     */
    public function shouldReturnWebEventTrackingCodeProvider()
    {
        return [
            [[], new WebEventTrackingSettings(false, false, '')],
            [
                [
                    'isEnabled' => 0,
                    'isFeatureTrackingEnabled' => 1,
                    'codeSnippet' => 'testCodeSnippet'
                ], new WebEventTrackingSettings(false, true, 'testCodeSnippet')
            ]
        ];
    }
}
