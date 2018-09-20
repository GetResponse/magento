<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Block;

use GetResponse\GetResponseIntegration\Block\Lists as ListsBlock;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\GetresponseApiClient;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit_Framework_MockObject_MockObject;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Repository as GrRepository;

/**
 * Class ListsTest
 * @package GetResponse\GetResponseIntegration\Test\Unit\Block
 */
class ListsTest extends BaseTestCase
{
    /** @var Context|PHPUnit_Framework_MockObject_MockObject */
    private $context;

    /** @var Repository|PHPUnit_Framework_MockObject_MockObject */
    private $repository;

    /** @var RepositoryFactory|PHPUnit_Framework_MockObject_MockObject */
    private $repositoryFactory;

    /** @var ListsBlock */
    private $listsBlock;

    /** @var ObjectManagerInterface|PHPUnit_Framework_MockObject_MockObject */
    private $objectManager;

    /** @var GetresponseApiClient|PHPUnit_Framework_MockObject_MockObject */
    private $grApiClient;

    public function setUp()
    {
        $this->context = $this->getMockWithoutConstructing(Context::class);
        $this->repository = $this->getMockWithoutConstructing(Repository::class);
        $this->repositoryFactory = $this->getMockWithoutConstructing(RepositoryFactory::class);
        $this->objectManager = $this->getMockWithoutConstructing(ObjectManagerInterface::class);
        $this->grApiClient = $this->getMockWithoutConstructing(GetresponseApiClient::class);

        $this->repositoryFactory->expects($this->once())->method('createGetResponseApiClient')->willReturn($this->grApiClient);
        $this->listsBlock = new ListsBlock($this->context, $this->repository, $this->repositoryFactory);
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
