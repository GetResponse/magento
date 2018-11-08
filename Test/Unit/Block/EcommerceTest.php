<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Block;

use GetResponse\GetResponseIntegration\Block\Ecommerce as EcommerceBlock;
use GetResponse\GetResponseIntegration\Block\Getresponse;
use GetResponse\GetResponseIntegration\Domain\GetResponse\GetresponseApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\RegistrationSettings;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class EcommerceTest
 * @package GetResponse\GetResponseIntegration\Test\Unit\Block
 */
class EcommerceTest extends BaseTestCase
{
    /** @var Context|\PHPUnit_Framework_MockObject_MockObject */
    private $context;

    /** @var Repository|\PHPUnit_Framework_MockObject_MockObject */
    private $repository;

    /** @var GetresponseApiClientFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $apiClientFactory;

    /** @var EcommerceBlock */
    private $accountBlock;

    /** @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $objectManager;

    public function setUp()
    {
        $this->context = $this->getMockWithoutConstructing(Context::class);
        $this->repository = $this->getMockWithoutConstructing(Repository::class);
        $this->apiClientFactory = $this->getMockWithoutConstructing(GetresponseApiClientFactory::class);
        $this->objectManager = $this->getMockWithoutConstructing(ObjectManagerInterface::class);

        $getresponseBlock = new Getresponse($this->repository, $this->apiClientFactory);
        $this->accountBlock = new EcommerceBlock($this->context, $this->repository, $this->apiClientFactory, $getresponseBlock);
    }

    /**
     * @return array
     */
    public function shouldReturnValidRegistrationSettingsProvider()
    {
        return [
            [[], new RegistrationSettings(0, 0, '', 0, 'x3')],
            [
                [
                    'status' => '1',
                    'customFieldsStatus' => '1',
                    'campaignId' => 9,
                    'cycleDay' => 2,
                    'autoresponderId' => 'x3'
                ], new RegistrationSettings(1, 1, '9', 2, 'x3')
            ]
        ];
    }

}
