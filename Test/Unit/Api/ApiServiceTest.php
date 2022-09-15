<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Api;

use GetResponse\GetResponseIntegration\Api\ApiService;
use GetResponse\GetResponseIntegration\Api\CartFactory;
use GetResponse\GetResponseIntegration\Api\CustomerFactory;
use GetResponse\GetResponseIntegration\Api\HttpClient;
use GetResponse\GetResponseIntegration\Api\OrderFactory;
use GetResponse\GetResponseIntegration\Api\ProductFactory;
use GetResponse\GetResponseIntegration\Api\SubscriberFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\LiveSynchronization;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Catalog\Model\Product;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use GetResponse\GetResponseIntegration\Api\Product as GrProduct;

class ApiServiceTest extends BaseTestCase
{
    private const CALLBACK_URL = 'http://app.getresponse.com/callback/#d93jd9dj39';

    /** @var MockObject|Repository */
    private $repositoryMock;
    /** @var MockObject|HttpClient */
    private $httpClientMock;
    /** @var MockObject|CartFactory */
    private $cartFactoryMock;
    /** @var MockObject|OrderFactory */
    private $orderFactoryMock;
    /** @var MockObject|ProductFactory */
    private $productFactoryMock;
    /** @var MockObject|CustomerFactory */
    private $customerFactoryMock;
    /** @var MockObject|SubscriberFactory */
    private $subscriberFactoryMock;
    /** @var ApiService */
    private $sut;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->getMockWithoutConstructing(Repository::class);
        $this->httpClientMock = $this->getMockWithoutConstructing(HttpClient::class);
        $this->cartFactoryMock = $this->getMockWithoutConstructing(CartFactory::class);
        $this->orderFactoryMock = $this->getMockWithoutConstructing(OrderFactory::class);
        $this->productFactoryMock = $this->getMockWithoutConstructing(ProductFactory::class);
        $this->customerFactoryMock = $this->getMockWithoutConstructing(CustomerFactory::class);
        $this->subscriberFactoryMock = $this->getMockWithoutConstructing(SubscriberFactory::class);

        $this->sut = new ApiService(
            $this->repositoryMock,
            $this->httpClientMock,
            $this->cartFactoryMock,
            $this->orderFactoryMock,
            $this->productFactoryMock,
            $this->customerFactoryMock,
            $this->subscriberFactoryMock
        );
    }

    /**
     * @test
     */
    public function shouldUpsertCustomerAddress(): void
    {
        /** @var AddressInterface|MockObject $addressMock */
        $addressMock = $this->getMockWithoutConstructing(AddressInterface::class);
        $scope = new Scope(1);

        $liveSynchronization = new LiveSynchronization(true, self::CALLBACK_URL, LiveSynchronization::TYPE_CONTACT);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getLiveSynchronization')
            ->with($scope->getScopeId())
            ->willReturn($liveSynchronization->toArray());

        $this->httpClientMock->expects(self::once())->method('post');

        $this->sut->upsertCustomerAddress($addressMock, $scope);
    }

    /**
     * @test
     */
    public function shouldNotUpsertCustomerAddress(): void
    {
        /** @var AddressInterface|MockObject $addressMock */
        $addressMock = $this->getMockWithoutConstructing(AddressInterface::class);
        $scope = new Scope(1);

        $liveSynchronization = new LiveSynchronization(true, self::CALLBACK_URL, LiveSynchronization::TYPE_PRODUCT);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getLiveSynchronization')
            ->with($scope->getScopeId())
            ->willReturn($liveSynchronization->toArray());

        $this->httpClientMock->expects(self::never())->method('post');

        $this->sut->upsertCustomerAddress($addressMock, $scope);
    }

    /**
     * @test
     */
    public function shouldUpsertCustomer(): void
    {
        /** @var CustomerInterface|MockObject $addressMock */
        $customerMock = $this->getMockWithoutConstructing(CustomerInterface::class);
        $scope = new Scope(1);

        $liveSynchronization = new LiveSynchronization(true, self::CALLBACK_URL, LiveSynchronization::TYPE_CONTACT);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getLiveSynchronization')
            ->with($scope->getScopeId())
            ->willReturn($liveSynchronization->toArray());

        $this->httpClientMock->expects(self::once())->method('post');

        $this->sut->upsertCustomer($customerMock, $scope);
    }

    /**
     * @test
     */
    public function shouldNotUpsertCustomer(): void
    {
        /** @var CustomerInterface|MockObject $addressMock */
        $customerMock = $this->getMockWithoutConstructing(CustomerInterface::class);
        $scope = new Scope(1);

        $liveSynchronization = new LiveSynchronization(true, self::CALLBACK_URL, LiveSynchronization::TYPE_PRODUCT);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getLiveSynchronization')
            ->with($scope->getScopeId())
            ->willReturn($liveSynchronization->toArray());

        $this->httpClientMock->expects(self::never())->method('post');

        $this->sut->upsertCustomer($customerMock, $scope);
    }

    /**
     * @test
     */
    public function shouldUpsertCustomerSubscription(): void
    {
        /** @var Subscriber|MockObject $addressMock */
        $subscriberMock = $this->getMockWithoutConstructing(Subscriber::class);
        $scope = new Scope(1);

        $liveSynchronization = new LiveSynchronization(true, self::CALLBACK_URL, LiveSynchronization::TYPE_CONTACT);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getLiveSynchronization')
            ->with($scope->getScopeId())
            ->willReturn($liveSynchronization->toArray());

        $this->httpClientMock->expects(self::once())->method('post');

        $this->sut->upsertCustomerSubscription($subscriberMock, $scope);
    }

    /**
     * @test
     */
    public function shouldNotUpsertCustomerSubscription(): void
    {
        /** @var Subscriber|MockObject $addressMock */
        $subscriberMock = $this->getMockWithoutConstructing(Subscriber::class);
        $scope = new Scope(1);

        $liveSynchronization = new LiveSynchronization(true, self::CALLBACK_URL, LiveSynchronization::TYPE_PRODUCT);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getLiveSynchronization')
            ->with($scope->getScopeId())
            ->willReturn($liveSynchronization->toArray());

        $this->httpClientMock->expects(self::never())->method('post');

        $this->sut->upsertCustomerSubscription($subscriberMock, $scope);
    }

    /**
     * @test
     */
    public function shouldCreateCart(): void
    {
        /** @var Quote|MockObject $quoteMock */
        $quoteMock = $this->getMockWithoutConstructing(Quote::class);

        $scope = new Scope(1);

        $liveSynchronization = new LiveSynchronization(true, self::CALLBACK_URL, LiveSynchronization::TYPE_ECOMMERCE);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getLiveSynchronization')
            ->with($scope->getScopeId())
            ->willReturn($liveSynchronization->toArray());

        $this->httpClientMock
            ->expects(self::once())
            ->method('post');

        $this->sut->createCart($quoteMock, $scope);
    }

    /**
     * @test
     */
    public function shouldNotCreateCart(): void
    {
        /** @var Quote|MockObject $quoteMock */
        $quoteMock = $this->getMockWithoutConstructing(Quote::class);

        $scope = new Scope(1);

        $liveSynchronization = new LiveSynchronization(true, self::CALLBACK_URL, LiveSynchronization::TYPE_PRODUCT);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getLiveSynchronization')
            ->with($scope->getScopeId())
            ->willReturn($liveSynchronization->toArray());

        $this->httpClientMock
            ->expects(self::never())
            ->method('post');

        $this->sut->createCart($quoteMock, $scope);
    }

    /**
     * @test
     */
    public function shouldCreateOrder(): void
    {
        /** @var Quote|MockObject $quoteMock */
        $quoteMock = $this->getMockWithoutConstructing(Quote::class);

        $scope = new Scope(1);

        $liveSynchronization = new LiveSynchronization(true, self::CALLBACK_URL, LiveSynchronization::TYPE_ECOMMERCE);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getLiveSynchronization')
            ->with($scope->getScopeId())
            ->willReturn($liveSynchronization->toArray());

        $this->httpClientMock
            ->expects(self::once())
            ->method('post');

        $this->sut->createCart($quoteMock, $scope);
    }

    /**
     * @test
     */
    public function shouldNotCreateOrder(): void
    {
        /** @var Quote|MockObject $quoteMock */
        $quoteMock = $this->getMockWithoutConstructing(Quote::class);

        $scope = new Scope(1);

        $liveSynchronization = new LiveSynchronization(true, self::CALLBACK_URL, LiveSynchronization::TYPE_PRODUCT);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getLiveSynchronization')
            ->with($scope->getScopeId())
            ->willReturn($liveSynchronization->toArray());

        $this->httpClientMock
            ->expects(self::never())
            ->method('post');

        $this->sut->createCart($quoteMock, $scope);
    }

    /**
     * @test
     */
    public function shouldUpsertProductCatalog(): void
    {
        /** @var Product|MockObject $quoteMock */
        $productMock = $this->getMockWithoutConstructing(Product::class);
        $productsToUpsert = [$this->getMockWithoutConstructing(GrProduct::class)];
        $scope = new Scope(1);

        $liveSynchronization = new LiveSynchronization(true, self::CALLBACK_URL, LiveSynchronization::TYPE_ECOMMERCE);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getLiveSynchronization')
            ->with($scope->getScopeId())
            ->willReturn($liveSynchronization->toArray());

        $this->productFactoryMock->expects(self::once())->method('create')->willReturn($productsToUpsert);

        $this->httpClientMock
            ->expects(self::once())
            ->method('post');

        $this->sut->upsertProductCatalog($productMock, $scope);
    }

    /**
     * @test
     */
    public function shouldNotUpsertProductCatalog(): void
    {
        /** @var Product|MockObject $quoteMock */
        $productMock = $this->getMockWithoutConstructing(Product::class);

        $scope = new Scope(1);

        $liveSynchronization = new LiveSynchronization(true, self::CALLBACK_URL, LiveSynchronization::TYPE_CONTACT);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getLiveSynchronization')
            ->with($scope->getScopeId())
            ->willReturn($liveSynchronization->toArray());

        $this->productFactoryMock->expects(self::never())->method('create');

        $this->httpClientMock
            ->expects(self::never())
            ->method('post');

        $this->sut->upsertProductCatalog($productMock, $scope);
    }

    /**
     * @test
     */
    public function shouldUpsertSubscriber(): void
    {
        /** @var Subscriber|MockObject $subscriberMock */
        $subscriberMock = $this->getMockWithoutConstructing(Subscriber::class);

        $scope = new Scope(1);

        $liveSynchronization = new LiveSynchronization(true, self::CALLBACK_URL, LiveSynchronization::TYPE_CONTACT);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getLiveSynchronization')
            ->with($scope->getScopeId())
            ->willReturn($liveSynchronization->toArray());

        $this->httpClientMock
            ->expects(self::once())
            ->method('post');

        $this->sut->upsertSubscriber($subscriberMock, $scope);
    }

    /**
     * @test
     */
    public function shouldNotUpsertSubscriber(): void
    {
        /** @var Subscriber|MockObject $subscriberMock */
        $subscriberMock = $this->getMockWithoutConstructing(Subscriber::class);

        $scope = new Scope(1);

        $liveSynchronization = new LiveSynchronization(true, self::CALLBACK_URL, LiveSynchronization::TYPE_PRODUCT);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getLiveSynchronization')
            ->with($scope->getScopeId())
            ->willReturn($liveSynchronization->toArray());

        $this->httpClientMock
            ->expects(self::never())
            ->method('post');

        $this->sut->upsertSubscriber($subscriberMock, $scope);
    }
}
