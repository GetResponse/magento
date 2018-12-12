<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Block;

use GetResponse\GetResponseIntegration\Block\Header;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class HeaderTest
 * @package GetResponse\GetResponseIntegration\Test\Unit\Block
 */
class HeaderTest extends BaseTestCase
{

    /** @var Context|\PHPUnit_Framework_MockObject_MockObject */
    private $context;

    /** @var Repository|\PHPUnit_Framework_MockObject_MockObject */
    private $repository;

    /** @var Session|\PHPUnit_Framework_MockObject_MockObject */
    private $session;

    /** @var Header */
    private $headerBlock;

    public function setUp()
    {
        $this->context = $this->getMockWithoutConstructing(Context::class);
        $this->repository = $this->getMockWithoutConstructing(Repository::class);
        $this->session = $this->getMockWithoutConstructing(Session::class);

        $this->headerBlock = new Header($this->context, $this->repository);
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

        $this->assertSame($expected, $this->headerBlock->getTrackingData());
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
        $this->assertSame($expected, $this->headerBlock->getTrackingData());
    }

}