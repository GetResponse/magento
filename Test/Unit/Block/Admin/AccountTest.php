<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Block\Admin;

use GetResponse\GetResponseIntegration\Block\Admin\Account as AccountBlock;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Account\Account;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Account\ReadModel\AccountReadModel;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\MockObject\MockObject;

class AccountTest extends BaseTestCase
{
    /** @var AccountReadModel|MockObject */
    private $accountReadModel;

    /** @var AccountBlock accountBlock */
    private $accountBlock;

    public function setUp()
    {
        /** @var Context|MockObject $context */
        $context = $this->getMockWithoutConstructing(Context::class);
        /** @var MagentoStore|MockObject $magentoStore */
        $magentoStore = $this->getMockWithoutConstructing(MagentoStore::class);

        $this->accountReadModel = $this->getMockWithoutConstructing(AccountReadModel::class);

        $this->accountBlock = new AccountBlock(
            $context,
            $magentoStore,
            $this->accountReadModel
        );
    }

    /**
     * @test
     */
    public function shouldReturnAccountInfo()
    {
        $account = new Account(
            '33303939',
            'testName',
            'testLastName',
            'testEmail',
            'testCompanyName',
            'testPhone',
            'testState',
            'testCity',
            'testStreet',
            'testZipCode'
        );

        $this->accountReadModel
            ->expects(self::once())->method('getAccount')
            ->willReturn($account);

        $accountInfo = $this->accountBlock->getAccountInfo();

        self::assertEquals($account, $accountInfo);
    }

    /**
     * @test
     */
    public function shouldCheckIsConnectedToGetResponse()
    {
        $this->accountReadModel
            ->expects(self::once())
            ->method('isConnected')
            ->willReturn(true);

        self::assertEquals(true, $this->accountBlock->isConnectedToGetResponse());
    }

    /**
     * @test
     */
    public function shouldReturnHiddenApiKey()
    {
        $hiddenApiKey = 'adk3******dk3';

        $this->accountReadModel
            ->expects(self::once())
            ->method('getHiddenApiKey')
            ->willReturn($hiddenApiKey);

        $apiKey = $this->accountBlock->getHiddenApiKey();

        self::assertEquals($hiddenApiKey, $apiKey);
    }
}
