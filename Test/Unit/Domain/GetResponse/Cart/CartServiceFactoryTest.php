<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\Cart;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Cart\CartServiceFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\ShareCodeCache;
use GetResponse\GetResponseIntegration\Domain\Magento\ShareCodeRepository;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\Cart\CartService as GrCartService;
use GrShareCode\DbRepositoryInterface;
use GrShareCode\GetresponseApiClient;
use GrShareCode\Product\ProductService;

class CartServiceFactoryTest extends BaseTestCase
{

    /** @var Repository|\PHPUnit_Framework_MockObject_MockObject */
    private $magentoRepository;

    /** @var ShareCodeRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $shareCodeRepository;

    /** @var CartServiceFactory */
    private $cartServiceFactory;

    /** @var ShareCodeCache */
    private $shareCodeCache;

    protected function setUp()
    {
        $this->magentoRepository = $this->getMockWithoutConstructing(Repository::class);
        $this->shareCodeRepository = $this->getMockWithoutConstructing(ShareCodeRepository::class);
        $this->shareCodeCache = $this->getMockWithoutConstructing(ShareCodeCache::class);

        $this->cartServiceFactory = new CartServiceFactory(
            $this->magentoRepository,
            $this->shareCodeRepository,
            $this->shareCodeCache
        );
    }

    /**
     * @test
     */
    public function shouldCreateCartService()
    {
        $this->magentoRepository
            ->expects(self::once())
            ->method('getConnectionSettings')
            ->willReturn([
                'domain' => '',
                'url' => '',
                'apiKey' => 'GetResponseApiKey'
            ]);

        $this->magentoRepository
            ->expects(self::once())
            ->method('getGetResponsePluginVersion')
            ->willReturn('20.1.1');

        $result = $this->cartServiceFactory->create();

        $this->assertInstanceOf(GrCartService::class, $result);

        $this->assertInstanceOf(
            ProductService::class,
            $this->getObjectAttribute($result, 'productService')
        );

        $this->assertInstanceOf(
            GetresponseApiClient::class,
            $this->getObjectAttribute($result, 'getresponseApiClient')
        );

        $this->assertInstanceOf(
            DbRepositoryInterface::class,
            $this->getObjectAttribute($result, 'dbRepository')
        );
    }
}
