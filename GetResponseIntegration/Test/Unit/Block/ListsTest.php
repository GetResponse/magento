<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Block;

use GetResponse\GetResponseIntegration\Block\Lists as ListsBlock;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit_Framework_MockObject_MockObject;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Repository as GrRepository;

/**
 * Class ListsTest
 * @package GetResponse\GetResponseIntegration\Test\Unit\Block
 */
class ListsTest extends TestCase
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

    /** @var GrRepository|PHPUnit_Framework_MockObject_MockObject */
    private $grRepository;

    public function setUp()
    {
        $this->context = $this->createMock(Context::class);
        $this->repository = $this->createMock(Repository::class);
        $this->repositoryFactory = $this->createMock(RepositoryFactory::class);
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);
        $this->grRepository = $this->createMock(GrRepository::class);

        $this->repositoryFactory->expects($this->once())->method('createRepository')->willReturn($this->grRepository);
        $this->listsBlock = new ListsBlock($this->context, $this->objectManager, $this->repository, $this->repositoryFactory);
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
