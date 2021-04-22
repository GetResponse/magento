<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\Account\ReadModel;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Account\Account;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Account\ReadModel\AccountReadModel;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettings;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

class AccountReadModelTest extends BaseTestCase
{
    /** @var Repository|MockObject */
    private $repository;
    /** @var AccountReadModel */
    private $accountReadModel;
    /** @var Scope|MockObject */
    private $scope;

    public function setUp()
    {
        $this->repository = $this->getMockWithoutConstructing(Repository::class);
        $this->accountReadModel = new AccountReadModel($this->repository);
        $this->scope = $this->getMockWithoutConstructing(Scope::class);
    }

    /**
     * @test
     */
    public function shouldReturnConnectionSettings()
    {
        $apiKey = 'API_3949384934';
        $url = 'http://getresponse.com';
        $domain = 'getresponse';

        $expectedSettings = new ConnectionSettings(
            $apiKey,
            $url,
            $domain
        );

        $this->repository
            ->expects(self::once())
            ->method('getConnectionSettings')
            ->willReturn([
                'apiKey' => $apiKey,
                'url' => $url,
                'domain' => $domain
            ]);

        $settings = $this->accountReadModel->getConnectionSettings($this->scope);

        self::assertEquals($expectedSettings, $settings);
    }

    /**
     * @test
     */
    public function shouldReturnAccount()
    {
        $firstName = 'Jan';
        $lastName = 'Nowak';
        $email = 'jan.nowak@example.com';
        $companyName = 'GetResponse';
        $phone = '544494499';
        $state = 'Pomorskie';
        $city = 'Gdańsk';
        $street = 'Nowa';
        $zipCode = '90-090';
        $countryCode = 'PL';

        $countryCodeObject = (new stdClass());
        $countryCodeObject->countryCodeId = 393;
        $countryCodeObject->countryCode = $countryCode;

        $expectedAccount = new Account(
            $firstName,
            $lastName,
            $email,
            $companyName,
            $phone,
            $state,
            $city,
            $street,
            $zipCode,
            $countryCode
        );

        $this->repository
            ->expects(self::once())
            ->method('getAccountInfo')
            ->willReturn([
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $email,
                'companyName' => $companyName,
                'phone' => $phone,
                'state' => $state,
                'city' => $city,
                'street' => $street,
                'zipCode' => $zipCode,
                'countryCode' => $countryCodeObject
            ]);

        $account = $this->accountReadModel->getAccount($this->scope);

        self::assertEquals($expectedAccount, $account);
    }

    /**
     * @test
     */
    public function shouldCheckIfConnected()
    {
        $this->repository
            ->expects(self::once())
            ->method('getConnectionSettings')
            ->willReturn([
                'apiKey' => 'API_3939393'
            ]);

        self::assertTrue($this->accountReadModel->isConnected($this->scope));
    }

    /**
     * @test
     */
    public function shouldReturnHiddenApiKey()
    {
        $apiKey = '320480234820420982309';
        $hiddenApiKey = '***************982309';

        $this->repository
            ->expects(self::once())
            ->method('getConnectionSettings')
            ->willReturn([
                'apiKey' => $apiKey,
                'url' => 'http://getresponse.com',
                'domain' => 'getresponse'
            ]);

        $result = $this->accountReadModel->getHiddenApiKey($this->scope);
        self::assertEquals($hiddenApiKey, $result);
    }
}
