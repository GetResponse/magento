<?php

namespace Domain\GetResponse\Order;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\Config;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\OrderServiceFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\RepositoryForSharedCode;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GrShareCode\Api\ApiKeyAuthorization;
use GrShareCode\Api\Authorization;
use GrShareCode\Api\UserAgentHeader;
use GrShareCode\GetresponseApi;
use GrShareCode\GetresponseApiClient;
use GrShareCode\Order\OrderService;
use GrShareCode\Product\ProductService;


/**
 * Class OrderServiceFactoryTest
 * @package Domain\GetResponse\Order
 */
class OrderServiceFactoryTest extends BaseTestCase
{
    /** @var Repository|\PHPUnit_Framework_MockObject_MockObject */
    private $magentoRepositoryMock;

    /** @var RepositoryForSharedCode|\PHPUnit_Framework_MockObject_MockObject */
    private $sharedCodeRepositoryMock;

    public function setUp()
    {
        $this->magentoRepositoryMock = $this->getMockWithoutConstructing(Repository::class);
        $this->sharedCodeRepositoryMock = $this->getMockWithoutConstructing(RepositoryForSharedCode::class);
    }

    /**
     * @test
     */
    public function shouldCreateOrderService()
    {
        $apiKey = '494j49j9j49f';
        $url = '';
        $domain = 'mx_us';
        $pluginVersion = '1.1';

        $rawSettings = [
            'apiKey' => $apiKey,
            'url' => $url,
            'domain' => $domain
        ];

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
            )
        );

        $this->magentoRepositoryMock->expects($this->once())->method('getConnectionSettings')->willReturn($rawSettings);
        $this->magentoRepositoryMock->expects($this->once())->method('getGetResponsePluginVersion')->willReturn($pluginVersion);

        $orderServiceFactory = new OrderServiceFactory(
            $this->magentoRepositoryMock,
            $this->sharedCodeRepositoryMock
        );

        $orderService = $orderServiceFactory->create();

        $this->assertInstanceOf(OrderService::class, $orderService);
        $this->assertEquals($expectedOrderService, $orderService);

    }
}
