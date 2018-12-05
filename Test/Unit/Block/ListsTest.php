<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Block;

use GetResponse\GetResponseIntegration\Block\Lists as ListsBlock;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\Api\GetresponseApiClient;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class ListsTest
 * @package GetResponse\GetResponseIntegration\Test\Unit\Block
 */
class ListsTest extends BaseTestCase
{
    /** @var Context|\PHPUnit_Framework_MockObject_MockObject */
    private $context;

    /** @var Repository|\PHPUnit_Framework_MockObject_MockObject */
    private $repository;

    /** @var ApiClientFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $apiClientFactory;

    /** @var ListsBlock */
    private $listsBlock;

    /** @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $objectManager;

    /** @var GetresponseApiClient|\PHPUnit_Framework_MockObject_MockObject */
    private $grApiClient;

    /** @var  ManagerInterface|\PHPUnit_Framework_MockObject_MockObject*/
    private $messageManager;

    /** @var RedirectFactory|\PHPUnit_Framework_MockObject_MockObject*/
    private $redirectFactory;

    /** @var Logger|\PHPUnit_Framework_MockObject_MockObject */
    private $logger;

    public function setUp()
    {
        $this->context = $this->getMockWithoutConstructing(Context::class);
        $this->repository = $this->getMockWithoutConstructing(Repository::class);
        $this->apiClientFactory = $this->getMockWithoutConstructing(ApiClientFactory::class);
        $this->objectManager = $this->getMockWithoutConstructing(ObjectManagerInterface::class);
        $this->grApiClient = $this->getMockWithoutConstructing(GetresponseApiClient::class);
        $this->messageManager = $this->getMockWithoutConstructing(ManagerInterface::class);
        $this->redirectFactory = $this->getMockWithoutConstructing(RedirectFactory::class);
        $this->logger = $this->getMockWithoutConstructing(Logger::class);

        $this->listsBlock = new ListsBlock(
            $this->context,
            $this->messageManager,
            $this->redirectFactory,
            $this->apiClientFactory,
            $this->logger,
            $this->repository
        );
    }

    /**
     * @test
     */
    public function shouldReturnValidBackUrl()
    {
        self::assertEquals('getresponse/export/index', $this->listsBlock->getBackUrl('export'));
        self::assertEquals('getresponse/registration/index', $this->listsBlock->getBackUrl('registration'));
        self::assertEquals('', $this->listsBlock->getBackUrl(''));
    }
}
