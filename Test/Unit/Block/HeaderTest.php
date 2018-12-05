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

        $this->headerBlock = new Header(
            $this->context,
            $this->repository,
            $this->session
        );
    }

    /**
     * @test
     */
    public function shouldReturnLoggedInCustomerEmailAndTrackingCodeSnippet()
    {
        $customerEmail = 'adam.kowslaski@getresponse.com';
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

        $this->session
            ->expects(self::once())
            ->method('isLoggedIn')
            ->willReturn(true);

        $customer = $this->getMockWithoutConstructing(Customer::class);
        $customer
            ->expects(self::once())
            ->method('__call')
            ->with('getEmail')
            ->willReturn($customerEmail);

        $this->session
            ->expects(self::once())
            ->method('getCustomer')
            ->willReturn($customer);

        $expected = [
            'isTrackingCodeEnabled' => $isTrackingCodeEnabled,
            'trackingCodeSnippet' => $trackingCodeSnippet,
            'customerEmail' => $customerEmail,
        ];
        $this->assertSame($expected, $this->headerBlock->getTrackingData());
    }

    /**
     * @test
     */
    public function shouldReturnEmptyEmailIfCustomerIsNotLoggedIn()
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

        $this->session
            ->expects(self::once())
            ->method('isLoggedIn')
            ->willReturn(false);

        $expected = [
            'isTrackingCodeEnabled' => $isTrackingCodeEnabled,
            'trackingCodeSnippet' => $trackingCodeSnippet,
            'customerEmail' => '',
        ];

        $this->assertSame($expected, $this->headerBlock->getTrackingData());
    }

    /**
     * @test
     */
    public function shouldReturnEmptyTrackingCodeSnippet()
    {
        $customerEmail = 'adam.kowslaski@getresponse.com';
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

        $this->session
            ->expects(self::once())
            ->method('isLoggedIn')
            ->willReturn(true);

        $customer = $this->getMockWithoutConstructing(Customer::class);
        $customer
            ->expects(self::once())
            ->method('__call')
            ->with('getEmail')
            ->willReturn($customerEmail);

        $this->session
            ->expects(self::once())
            ->method('getCustomer')
            ->willReturn($customer);

        $expected = [
            'isTrackingCodeEnabled' => $isTrackingCodeEnabled,
            'trackingCodeSnippet' => '',
            'customerEmail' => $customerEmail,
        ];
        $this->assertSame($expected, $this->headerBlock->getTrackingData());
    }

}