<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\Cart;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Cart\CartServiceFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\ShareCodeCache;
use GetResponse\GetResponseIntegration\Domain\Magento\ShareCodeRepository;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\Api\GetresponseApiClient;
use GrShareCode\Cart\CartService as GrCartService;
use GrShareCode\Cart\CartServiceFactory as GrCartServiceFactory;

class CartServiceFactoryTest extends BaseTestCase
{
    /** @var ShareCodeRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $shareCodeRepository;

    /** @var CartServiceFactory */
    private $cartServiceFactory;

    /** @var ShareCodeCache */
    private $shareCodeCache;

    /** @var ApiClientFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $apiClientFactory;

    /** @var GrCartServiceFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $grCartServiceFactory;

    /**
     * @test
     */
    public function shouldCreateCartService()
    {
        $apiClient = $this->getMockWithoutConstructing(GetresponseApiClient::class);
        $grCartService = $this->getMockWithoutConstructing(GrCartService::class);

        $this->apiClientFactory
            ->expects(self::once())
            ->method('createGetResponseApiClient')
            ->willReturn($apiClient);

        $this->grCartServiceFactory
            ->expects(self::once())
            ->method('create')
            ->with($apiClient, $this->shareCodeRepository, $this->shareCodeCache)
            ->willReturn($grCartService);

        $this->cartServiceFactory->create();
    }

    protected function setUp()
    {
        $this->shareCodeRepository = $this->getMockWithoutConstructing(ShareCodeRepository::class);
        $this->shareCodeCache = $this->getMockWithoutConstructing(ShareCodeCache::class);
        $this->apiClientFactory = $this->getMockWithoutConstructing(ApiClientFactory::class);
        $this->grCartServiceFactory = $this->getMockWithoutConstructing(GrCartServiceFactory::class);

        $this->cartServiceFactory = new CartServiceFactory(
            $this->shareCodeRepository,
            $this->shareCodeCache,
            $this->apiClientFactory,
            $this->grCartServiceFactory
        );
    }
}
