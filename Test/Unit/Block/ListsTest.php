<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Block;

use GetResponse\GetResponseIntegration\Block\Lists as ListsBlock;
use GetResponse\GetResponseIntegration\Domain\GetResponse\GetresponseApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\GetresponseApiClient;
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

    /** @var GetresponseApiClientFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $apiClientFactory;

    /** @var ListsBlock */
    private $listsBlock;

    /** @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $objectManager;

    /** @var GetresponseApiClient|\PHPUnit_Framework_MockObject_MockObject */
    private $grApiClient;

    public function setUp()
    {
        $this->context = $this->getMockWithoutConstructing(Context::class);
        $this->repository = $this->getMockWithoutConstructing(Repository::class);
        $this->apiClientFactory = $this->getMockWithoutConstructing(GetresponseApiClientFactory::class);
        $this->objectManager = $this->getMockWithoutConstructing(ObjectManagerInterface::class);
        $this->grApiClient = $this->getMockWithoutConstructing(GetresponseApiClient::class);
        $this->listsBlock = new ListsBlock($this->context, $this->repository, $this->apiClientFactory);
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
