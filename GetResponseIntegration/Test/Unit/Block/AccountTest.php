<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Block;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Account;
use GetResponse\GetResponseIntegration\Block\Account as AccountBlock;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use PHPUnit\Framework\TestCase;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class AccountTest
 * @package GetResponse\GetResponseIntegration\Test\Unit\Block
 */
class AccountTest extends TestCase
{
    /** @var Context|PHPUnit_Framework_MockObject_MockObject $contenxt */
    private $context;

    /** @var Repository|PHPUnit_Framework_MockObject_MockObject $repository */
    private $repository;

    /** @var RepositoryFactory|PHPUnit_Framework_MockObject_MockObject $repositoryFactory */
    private $repositoryFactory;

    private $accountBlock;

    public function setUp()
    {
        $this->context = $this->getMockBuilder(Context::class)->disableOriginalConstructor()->getMock();

        $this->repository = $this->getMockBuilder(Repository::class)->disableOriginalConstructor()->getMock();


        $this->repositoryFactory = $this->getMockBuilder(RepositoryFactory::class)->disableOriginalConstructor()
            ->getMock();

        $this->accountBlock = new AccountBlock($this->context, $this->repository, $this->repositoryFactory);
    }

    /**
     * @test
     */
    public function shouldReturnAccountInfo()
    {
        $accountId = 19191;
        $firstName = 'John';
        $lastname = 'Smith';
        $email = 'john@example.com';
        $companyName = 'GetResponse';
        $phone = '544 404 400';
        $state = 'Some state';
        $city = 'Some city';
        $street = 'Some city';
        $zipCode = '01-001';

        $account = [
            'accountId' => $accountId,
            'firstName' => $firstName,
            'lastName' => $lastname,
            'email' => $email,
            'companyName' => $companyName,
            'phone' => $phone,
            'state' => $state,
            'city' => $city,
            'street' => $street,
            'zipCode' => $zipCode
        ];

        $this->repository->expects($this->once())->method('getAccountInfo')->willReturn($account);

        $info = $this->accountBlock->getAccountInfo();
        self::assertInstanceOf(Account::class, $info);
        self::assertEquals($accountId, $info->getAccountId());
        self::assertEquals($firstName, $info->getFirstName());
        self::assertEquals($lastname, $info->getLastName());
        self::assertEquals($email, $info->getEmail());
        self::assertEquals($companyName, $info->getCompanyName());
        self::assertEquals($phone, $info->getPhone());
        self::assertEquals($state, $info->getState());
        self::assertEquals($city, $info->getCity());
        self::assertEquals($street, $info->getStreet());
        self::assertEquals($zipCode, $info->getZipCode());
    }
}
