<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Block;

use GetResponse\GetResponseIntegration\Block\Ecommerce as EcommerceBlock;
use GetResponse\GetResponseIntegration\Block\Getresponse;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
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

    /** @var RepositoryFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $repositoryFactory;

    /** @var EcommerceBlock */
    private $accountBlock;

    /** @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $objectManager;

    public function setUp()
    {
        $this->context = $this->getMockWithoutConstructing(Context::class);
        $this->repository = $this->getMockWithoutConstructing(Repository::class);
        $this->repositoryFactory = $this->getMockWithoutConstructing(RepositoryFactory::class);
        $this->objectManager = $this->getMockWithoutConstructing(ObjectManagerInterface::class);

        $getresponseBlock = new Getresponse($this->repository, $this->repositoryFactory);
        $this->accountBlock = new EcommerceBlock($this->context, $this->repository, $this->repositoryFactory, $getresponseBlock);
    }

    /**
     * @test
     *
     * @param array $dbResponse
     * @param RegistrationSettings $expectedSettings
     * @dataProvider shouldReturnValidRegistrationSettingsProvider
     */
    public function shouldReturnValidRegistrationSettings(array $dbResponse, RegistrationSettings $expectedSettings)
    {
        $this->repository->expects($this->once())->method('getRegistrationSettings')->willReturn($dbResponse);
        $settings = $this->accountBlock->getRegistrationSettings();

        $this->assertEquals($expectedSettings->isEnabled(), $settings->isEnabled());
        $this->assertEquals($expectedSettings->isCustomFieldsModified(), $settings->isCustomFieldsModified());
        $this->assertEquals($expectedSettings->getCampaignId(), $settings->getCampaignId());
        $this->assertEquals($expectedSettings->getCycleDay(), $settings->getCycleDay());
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
