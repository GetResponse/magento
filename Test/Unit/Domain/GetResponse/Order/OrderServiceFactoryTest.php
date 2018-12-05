<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\Order;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\Config;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\OrderServiceFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\ShareCodeRepository;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\Api\Authorization\ApiKeyAuthorization;
use GrShareCode\Api\Authorization\Authorization;
use GrShareCode\Api\GetresponseApi;
use GrShareCode\Api\GetresponseApiClient;
use GrShareCode\Api\UserAgentHeader;
use GrShareCode\Order\OrderPayloadFactory;
use GrShareCode\Order\OrderService;
use GrShareCode\Order\OrderServiceFactory as GrOrderServiceFactory;
use GrShareCode\Product\ProductService;

/**
 * Class OrderServiceFactoryTest
 * @package Domain\GetResponse\Order
 */
class OrderServiceFactoryTest extends BaseTestCase
{
    /** @var ShareCodeRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $sharedCodeRepositoryMock;

    /** @var ApiClientFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $getResponseApiClientFactory;

    /** @var GrOrderServiceFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $grOrderServiceFactory;

    public function setUp()
    {
        $this->sharedCodeRepositoryMock = $this->getMockWithoutConstructing(ShareCodeRepository::class);
        $this->getResponseApiClientFactory = $this->getMockWithoutConstructing(ApiClientFactory::class);
        $this->grOrderServiceFactory = $this->getMockWithoutConstructing(GrOrderServiceFactory::class);
    }

    /**
     * @test
     */
    public function shouldCreateOrderService()
    {
        $apiKey = '494j49j9j49f';
        $domain = 'mx_us';
        $pluginVersion = '1.1';

        $getresponseApiClient = new GetresponseApiClient(
            new GetresponseApi(
                new ApiKeyAuthorization(
                    $apiKey, Authorization::SMB, $domain
                ),
                Config::X_APP_ID,
                new UserAgentHeader(
                    Config::SERVICE_NAME,
                    Config::SERVICE_VERSION,
                    $pluginVersion
                )
            ),
            $this->sharedCodeRepositoryMock
        );

        $expectedOrderService = new OrderService(
            $getresponseApiClient,
            $this->sharedCodeRepositoryMock,
            new ProductService(
                $getresponseApiClient,
                $this->sharedCodeRepositoryMock
            ),
            new OrderPayloadFactory()
        );

        $this->getResponseApiClientFactory
            ->expects($this->once())
            ->method('createGetResponseApiClient')
            ->willReturn($getresponseApiClient);

        $this->grOrderServiceFactory
            ->expects(self::once())
            ->method('create')
            ->with($getresponseApiClient, $this->sharedCodeRepositoryMock)
            ->willReturn($expectedOrderService);

        $orderServiceFactory = new OrderServiceFactory(
            $this->sharedCodeRepositoryMock,
            $this->getResponseApiClientFactory,
            $this->grOrderServiceFactory
        );

        $orderService = $orderServiceFactory->create();

        $this->assertInstanceOf(OrderService::class, $orderService);
        $this->assertEquals($expectedOrderService, $orderService);
    }
}
