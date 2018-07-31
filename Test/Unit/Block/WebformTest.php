<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Block;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Repository as GrRepository;
use GetResponse\GetResponseIntegration\Block\Webform as WebformBlock;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Webform;
use GetResponse\GetResponseIntegration\Domain\GetResponse\WebformsCollection;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class WebformTest
 * @package GetResponse\GetResponseIntegration\Test\Unit\Block
 */
class WebformTest extends TestCase
{
    /** @var Context|PHPUnit_Framework_MockObject_MockObject */
    private $context;

    /** @var Repository|PHPUnit_Framework_MockObject_MockObject */
    private $repository;

    /** @var RepositoryFactory|PHPUnit_Framework_MockObject_MockObject */
    private $repositoryFactory;

    /** @var ObjectManagerInterface|PHPUnit_Framework_MockObject_MockObject */
    private $objectManager;

    /** @var WebformBlock */
    private $webformBlock;

    /** @var GrRepository|PHPUnit_Framework_MockObject_MockObject */
    private $grRepository;

    public function setUp()
    {
        $this->context = $this->createMock(Context::class);
        $this->repository = $this->createMock(Repository::class);
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);
        $this->repositoryFactory = $this->createMock(RepositoryFactory::class);
        $this->grRepository = $this->createMock(GrRepository::class);
        $this->repositoryFactory->expects($this->once())->method('createRepository')->willReturn($this->grRepository);
        $this->webformBlock = new WebformBlock($this->context, $this->objectManager, $this->repository, $this->repositoryFactory);
    }

    /**
     * @test
     */
    public function shouldReturnWebFormsCollectionWhenExceptionOccurs()
    {
        $this->grRepository->method('getForms')->willThrowException(new RepositoryException());
        $collection = $this->webformBlock->getWebFormsCollection();

        self::assertEquals(new WebformsCollection(), $collection);
    }

    /**
     * @test
     *
     * @param array $rawFormsData
     * @param array $rawWebformsData
     * @param WebformsCollection $expectedCollection
     *
     * @dataProvider shouldReturnValidWebFormsCollectionProvider
     */
    public function shouldReturnValidWebFormsCollection(array $rawFormsData, array $rawWebformsData,  WebformsCollection $expectedCollection)
    {
        $this->grRepository->method('getForms')->willReturn($rawFormsData);
        $this->grRepository->method('getWebForms')->willReturn($rawWebformsData);

        $collection = $this->webformBlock->getWebFormsCollection();

        self::assertEquals($expectedCollection, $collection);
    }

    /**
     * @return array
     */
    public function shouldReturnValidWebFormsCollectionProvider()
    {
        $form = new \stdClass();
        $form->formId = '4d39';
        $form->name = 'testForm';
        $form->scriptUrl = 'testFormUrl';

        $webForm = new \stdClass();
        $webForm->webformId = '4x09';
        $webForm->name = 'testWebForm';
        $webForm->scriptUrl = 'testWebFormUrl';

        $collection = new WebformsCollection();
        $collection->add(new Webform('4d39', 'testForm', 'testFormUrl'));
        $collection->add(new Webform('4x09', 'testWebForm', 'testWebFormUrl'));
        return [
            [[], [], new WebformsCollection()],
            [[$form], [$webForm], $collection]
        ];
    }
}
