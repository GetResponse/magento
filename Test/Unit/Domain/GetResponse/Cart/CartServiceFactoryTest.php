<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\Cart;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Cart\CartServiceFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\RepositoryForSharedCode;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\Cart\CartService as GrCartService;
use GrShareCode\DbRepositoryInterface;
use GrShareCode\GetresponseApi;
use GrShareCode\Product\ProductService;

class CartServiceFactoryTest extends BaseTestCase
{

    /** @var Repository|\PHPUnit_Framework_MockObject_MockObject */
    private $magentoRepository;

    /** @var RepositoryForSharedCode|\PHPUnit_Framework_MockObject_MockObject */
    private $repositoryForSharedCode;

    /** @var CartServiceFactory */
    private $cartServiceFactory;

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
            GetresponseApi::class,
            $this->getObjectAttribute($result, 'getresponseApi')
        );

        $this->assertInstanceOf(
            DbRepositoryInterface::class,
            $this->getObjectAttribute($result, 'dbRepository')
        );

    }

    protected function setUp()
    {
        $this->magentoRepository = $this->getMockWithoutConstructing(Repository::class);
        $this->repositoryForSharedCode = $this->getMockWithoutConstructing(RepositoryForSharedCode::class);

        $this->cartServiceFactory = new CartServiceFactory($this->magentoRepository, $this->repositoryForSharedCode);
    }
}
