<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Api;

use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Api\CartFactory;
use GetResponse\GetResponseIntegration\Api\CustomerFactory;
use GetResponse\GetResponseIntegration\Api\HttpClient;
use GetResponse\GetResponseIntegration\Api\OrderFactory;
use GetResponse\GetResponseIntegration\Api\ProductFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\LiveSynchronization;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GetResponse\GetResponseIntegration\Test\Unit\ApiFaker;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Customer as MagentoCustomer;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;

class ApiServiceTest extends BaseTestCase
{
    private const CALLBACK_URL = 'http://app.getresponse.com/callback/#d93jd9dj39';

    /** @var object|MockObject|Repository */
    private $repositoryMock;
    /** @var object|MockObject|HttpClient */
    private $httpClientMock;
    /** @var object|MockObject|CartFactory */
    private $cartFactory;
    /** @var object|MockObject|OrderFactory */
    private $orderFactory;
    /** @var object|MockObject|ProductFactory */
    private $productFactory;
    /** @var object|MockObject|CustomerFactory */
    private $customerFactory;

    private $sut;

    public function setUp(): void
    {
        $this->repositoryMock = $this->getMockWithoutConstructing(Repository::class);
        $this->httpClientMock = $this->getMockWithoutConstructing(HttpClient::class);
        $this->cartFactory = $this->getMockWithoutConstructing(CartFactory::class);
        $this->orderFactory = $this->getMockWithoutConstructing(OrderFactory::class);
        $this->productFactory = $this->getMockWithoutConstructing(ProductFactory::class);
        $this->customerFactory = $this->getMockWithoutConstructing(CustomerFactory::class);

        $this->sut = new ApiService(
            $this->repositoryMock,
            $this->httpClientMock,
            $this->cartFactory,
            $this->orderFactory,
            $this->productFactory,
            $this->customerFactory
        );
    }

    /**
     * @test
     */
    public function shouldCreateCart(): void
    {
        $customer = ApiFaker::createCustomer();
        $cart = ApiFaker::createCart();

        $scope = new Scope(1);

        $liveSynchronization = new LiveSynchronization(true, self::CALLBACK_URL, LiveSynchronization::TYPE_ECOMMERCE);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getLiveSynchronization')
            ->with($scope->getScopeId())
            ->willReturn($liveSynchronization->toArray());

        $customerMock = $this->getMockBuilder(MagentoCustomer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerMock
            ->method('__call')
            ->withConsecutive(['getId', 'getEmail', 'getFirstname', 'getLastname'])
            ->willReturnOnConsecutiveCalls(
                $customer->getId(),
                $customer->getEmail(),
                $customer->getFirstName(),
                $customer->getLastName()
            );

        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();

        $quoteMock
            ->method('getId')
            ->willReturn($cart->getId());
        $quoteMock
            ->method('getCustomer')
            ->willReturn($customerMock);

        $this->cartFactory
            ->expects(self::once())
            ->method('create')
            ->with($quoteMock)
            ->willReturn($cart);

        $this->httpClientMock
            ->expects(self::once())
            ->method('post')
            ->with($liveSynchronization->getCallbackUrl(), $cart);

        $this->sut->createCart($quoteMock, $scope);
    }

    /**
     * @test
     */
    public function shouldCreateProduct(): void
    {
        $product = ApiFaker::createProduct();

        $scope = new Scope(1);
        $liveSynchronization = new LiveSynchronization(true, self::CALLBACK_URL, LiveSynchronization::TYPE_PRODUCT);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getLiveSynchronization')
            ->with($scope->getScopeId())
            ->willReturn($liveSynchronization->toArray());

        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productMock
            ->method('getId')
            ->willReturn($product->getId());
        $productMock
            ->method('getName')
            ->willReturn($product->getName());
        $productMock
            ->method('getTypeId')
            ->willReturn($product->getType());
        $productMock
            ->method('getCreatedAt')
            ->willReturn($product->getCreatedAt());
        $productMock
            ->method('getUpdatedAt')
            ->willReturn($product->getUpdatedAt());

        $this->productFactory
            ->expects(self::once())
            ->method('create')
            ->with($productMock, $scope)
            ->willReturn($product);

        $this->httpClientMock
            ->expects(self::once())
            ->method('post')
            ->with($liveSynchronization->getCallbackUrl(), $product);

        $this->sut->upsertProductCatalog($productMock, $scope);
    }
}
