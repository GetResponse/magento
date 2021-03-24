<?php

declare(strict_types=1);

use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Api\Cart;
use GetResponse\GetResponseIntegration\Api\Customer;
use GetResponse\GetResponseIntegration\Api\HttpClient;
use GetResponse\GetResponseIntegration\Domain\Magento\LiveSynchronization;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Customer\Model\Customer as MagentoCustomer;
use Magento\Quote\Model\Quote;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Checkout\Helper\Cart as CartHelper;

class ApiServiceTest extends BaseTestCase
{
    /** @var object|PHPUnit_Framework_MockObject_MockObject|Repository */
    private $repositoryMock;
    /** @var object|PHPUnit_Framework_MockObject_MockObject|CartHelper */
    private $cartHelperMock;
    /** @var object|PHPUnit_Framework_MockObject_MockObject|HttpClient */
    private $httpClientMock;
    /** @var object|PHPUnit_Framework_MockObject_MockObject|CategoryRepository */
    private $categoryRepositoryMock;

    private $sut;

    public function setUp(): void
    {
        $this->repositoryMock = $this->getMockWithoutConstructing(Repository::class);
        $this->cartHelperMock = $this->getMockWithoutConstructing(CartHelper::class);
        $this->httpClientMock = $this->getMockWithoutConstructing(HttpClient::class);
        $this->categoryRepositoryMock = $this->getMockWithoutConstructing(CategoryRepository::class);

        $this->sut = new ApiService(
            $this->repositoryMock,
            $this->cartHelperMock,
            $this->httpClientMock,
            $this->categoryRepositoryMock
        );

    }

    /**
     * @test
     */
    public function shouldCreateCart(): void
    {
        $cartId = '679';
        $customerId = '393';
        $customerEmail = 'customer@getresponse.com';
        $customerFirstName = 'John';
        $customerLastName = 'Doe';
        $isMarketingAccepted = true;
        $tags = [];
        $customFields = [];

        $totalTaxPrice = 129.99;
        $totalPrice = 104.29;
        $currency = 'EUR';
        $cartUrl = 'http://magento.com/cart/3d938d9ff';

        $scope = new Scope(1);

        $liveSynchronization = new LiveSynchronization(
            true,
            'http://app.getresponse.com/callback/#d93jd9dj39'
        );

        $this->repositoryMock
            ->expects(self::once())
            ->method('getLiveSynchronization')
            ->with($scope->getScopeId())
            ->willReturn($liveSynchronization->toArray());

        $customerMock = $this->getMockBuilder(MagentoCustomer::class)
            ->disableOriginalConstructor()->getMock();
        $customerMock
            ->method('__call')
            ->withConsecutive(['getId', 'getEmail', 'getFirstname', 'getLastname'])
            ->willReturnOnConsecutiveCalls([$customerId, $customerEmail, $customerFirstName, $customerLastName]);

        $cartMock = $this->getMockBuilder(Quote::class)->disableOriginalConstructor()->getMock();
        $cartMock
            ->method('getId')
            ->willReturn($cartId);
        $cartMock
            ->method('getCustomer')
            ->willReturn($customerMock);

        $customerDTO = new Customer(
            (int) $customerId,
            $customerEmail,
            $customerFirstName,
            $customerLastName,
            $isMarketingAccepted,
            $tags,
            $customFields
        );

        $cartDTO = new Cart(
            (int) $cartId,
            $customerDTO,
            [],
            $totalPrice,
            $totalTaxPrice,
            $currency,
            $cartUrl,
            '2020-03-22 06:04:22',
            '2020-03-22 06:04:22'
        );

        $this->httpClientMock
            ->expects(self::once())
            ->method('post')
            ->with([$liveSynchronization->getCallbackUrl(), $cartDTO]);

        $this->sut->createCart($cartMock, $scope);
    }
}
