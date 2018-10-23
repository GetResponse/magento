<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Block;

use GetResponse\GetResponseIntegration\Block\Getresponse;
use GetResponse\GetResponseIntegration\Block\Settings;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Account;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class SettingsTest
 * @package GetResponse\GetResponseIntegration\Test\Unit\Block
 */
class SettingsTest extends BaseTestCase
{
    /** @var Context|\PHPUnit_Framework_MockObject_MockObject */
    private $context;

    /** @var Repository|\PHPUnit_Framework_MockObject_MockObject */
    private $repository;

    /** @var RepositoryFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $repositoryFactory;

    /** @var Settings */
    private $settingsBlock;

    public function setUp()
    {
        $this->context = $this->getMockWithoutConstructing(Context::class);
        $this->repository = $this->getMockWithoutConstructing(Repository::class);
        $this->repositoryFactory = $this->getMockWithoutConstructing(RepositoryFactory::class);

        $getresponseBlock = new Getresponse($this->repository, $this->repositoryFactory);
        $this->settingsBlock = new Settings(
            $this->context,
            $this->repository,
            $this->repositoryFactory,
            $getresponseBlock
        );
    }

    /**
     * @test
     *
     * @param array $rawInfo
     * @param Account $expectedAccount
     *
     * @dataProvider shouldReturnAccountInfoProvider
     *
     */
    public function shouldReturnAccountInfo(array $rawInfo, Account $expectedAccount)
    {
        $this->repository->expects($this->once())->method('getAccountInfo')->willReturn($rawInfo);
        $accountInfo = $this->settingsBlock->getAccountInfo();

        self::assertEquals($expectedAccount, $accountInfo);
    }

    /**
     * @return array
     */
    public function shouldReturnAccountInfoProvider()
    {
        return [
            [[], new Account('', '', '', '', '', '', '', '', '', '', '')],
            [
                [
                    'accountId' => '33303939',
                    'firstName' => 'testName',
                    'lastName' => 'testLastName',
                    'email' => 'testEmail',
                    'companyName' => 'testCompanyName',
                    'phone' => 'testPhone',
                    'state' => 'testState',
                    'city' => 'testCity',
                    'street' => 'testStreet',
                    'zipCode' => 'testZipCode'
                ],
                new Account(
                    '33303939', 'testName', 'testLastName', 'testEmail', 'testCompanyName', 'testPhone', 'testState',
                    'testCity', 'testStreet', 'testZipCode', '')
            ]
        ];
    }
}
