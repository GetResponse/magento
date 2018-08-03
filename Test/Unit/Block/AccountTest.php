<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Block;

use GetResponse\GetResponseIntegration\Block\Account as AccountBlock;
use GetResponse\GetResponseIntegration\Block\Getresponse;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Account;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class AccountTest
 * @package GetResponse\GetResponseIntegration\Test\Unit\Block
 */
class AccountTest extends BaseTestCase
{
    /** @var Context|PHPUnit_Framework_MockObject_MockObject */
    private $context;

    /** @var Repository|PHPUnit_Framework_MockObject_MockObject */
    private $repository;

    /** @var RepositoryFactory|PHPUnit_Framework_MockObject_MockObject */
    private $repositoryFactory;

    /** @var AccountBlock accountBlock */
    private $accountBlock;

    public function setUp()
    {
        $this->context = $this->getMockWithoutConstructing(Context::class);
        $this->repository = $this->getMockWithoutConstructing(Repository::class);
        $this->repositoryFactory = $this->getMockWithoutConstructing(RepositoryFactory::class);
        $getresponse = new Getresponse($this->repository, $this->repositoryFactory);
        $this->accountBlock = new AccountBlock(
            $this->context,
            $this->repository,
            $this->repositoryFactory,
            $getresponse
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
        $accountInfo = $this->accountBlock->getAccountInfo();
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

    /**
     * @test
     * @param array $response
     * @param bool $expectedValue
     *
     * @dataProvider shouldCheckIsConnectedToGetResponseProvider
     */
    public function shouldCheckIsConnectedToGetResponse($response, $expectedValue)
    {
        $this->repository->expects($this->once())->method('getConnectionSettings')->willReturn($response);
        $isConnected = $this->accountBlock->isConnectedToGetResponse();
        self::assertEquals($expectedValue, $isConnected);

    }

    /**
     * @return array
     */
    public function shouldCheckIsConnectedToGetResponseProvider()
    {
        return [
            [[], false],
            [['apiKey' => '433939'], true],
            [['apiKey' => ''], false]
        ];
    }

    /**
     * @test
     * @param string $apiKey
     * @param string $hiddenApiKey
     *
     * @dataProvider shouldReturnHiddenApiKeyProvider
     */
    public function shouldReturnHiddenApiKey($apiKey, $hiddenApiKey)
    {
        $this->repository->expects($this->once())->method('getConnectionSettings')->willReturn([
            'apiKey' => $apiKey,
            'url' => 'http://example.com',
            'domain' => ''
        ]);
        $isConnected = $this->accountBlock->getHiddenApiKey();
        self::assertEquals($hiddenApiKey, $isConnected);

    }

    /**
     * @return array
     */
    public function shouldReturnHiddenApiKeyProvider()
    {
        return [
            ['123456789', '***456789'],
            ['123456789ABCDEFG', '**********BCDEFG'],
        ];
    }
}
