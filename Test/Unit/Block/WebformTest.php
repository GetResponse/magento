<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Block;

use GetResponse\GetResponseIntegration\Block\Webform as WebformBlock;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\Api\GetresponseApiClient;
use GrShareCode\WebForm\WebForm;
use GrShareCode\WebForm\WebFormCollection;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class WebformTest
 * @package GetResponse\GetResponseIntegration\Test\Unit\Block
 */
class WebformTest extends BaseTestCase
{
    /** @var Context|\PHPUnit_Framework_MockObject_MockObject */
    private $context;

    /** @var Repository|\PHPUnit_Framework_MockObject_MockObject */
    private $repository;

    /** @var \GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $repositoryFactory;

    /** @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $objectManager;

    /** @var WebformBlock */
    private $webformBlock;

    /** @var  ManagerInterface|\PHPUnit_Framework_MockObject_MockObject*/
    private $messageManager;

    /** @var RedirectFactory|\PHPUnit_Framework_MockObject_MockObject*/
    private $redirectFactory;

    /** @var \GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $apiClientFactory;

    /** @var Logger|\PHPUnit_Framework_MockObject_MockObject */
    private $logger;

    public function setUp()
    {
        $this->context = $this->getMockWithoutConstructing(Context::class);
        $this->repository = $this->getMockWithoutConstructing(Repository::class);
        $this->objectManager = $this->getMockWithoutConstructing(ObjectManagerInterface::class);
        $this->repositoryFactory = $this->getMockWithoutConstructing(ApiClientFactory::class);
        $this->messageManager = $this->getMockWithoutConstructing(ManagerInterface::class);
        $this->redirectFactory = $this->getMockWithoutConstructing(RedirectFactory::class);
        $this->apiClientFactory = $this->getMockWithoutConstructing(ApiClientFactory::class);
        $this->logger = $this->getMockWithoutConstructing(Logger::class);

        $this->webformBlock = new WebformBlock(
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
     *
     * @param array $rawFormsData
     * @param array $rawWebformsData
     * @param WebformCollection $expectedCollection
     *
     * @dataProvider shouldReturnValidWebFormsCollectionProvider
     */
    public function shouldReturnValidWebFormsCollection(array $rawFormsData, array $rawWebformsData,  WebformCollection $expectedCollection)
    {
        $grApiClient = $this->getMockWithoutConstructing(GetresponseApiClient::class);
        $grApiClient->method('getForms')->willReturn($rawFormsData);
        $grApiClient->method('getWebForms')->willReturn($rawWebformsData);

        $this->apiClientFactory
            ->expects($this->once())
            ->method('createGetResponseApiClient')
            ->willReturn($grApiClient);

        $collection = $this->webformBlock->getWebForms();

        self::assertEquals($expectedCollection, $collection);
    }

    /**
     * @return array
     */
    public function shouldReturnValidWebFormsCollectionProvider()
    {
        $firstFormId = '4d39';
        $firstFormName = 'testForm';
        $firstFormScriptUrl = 'testFormUrl';
        $firstFormCampaignName = 'testForm';
        $firstFormStatus = 'published';

        $secondFormId = 'd3Ei';
        $secondFormName = 'testForm';
        $secondFormScriptUrl = 'testWebFormUrl';
        $secondFormCampaignName = 'testWebForm';
        $secondFormStatus = WebForm::STATUS_DISABLED;

        $form = [
            'webformId' => $firstFormId,
            'name' => $firstFormName,
            'scriptUrl' => $firstFormScriptUrl,
            'campaign' => ['name' => $firstFormCampaignName],
            'status' => $firstFormStatus
        ];

        $webForm = [
            'webformId' => $secondFormId,
            'name' => $secondFormName,
            'scriptUrl' => $secondFormScriptUrl,
            'campaign' => ['name' => $secondFormCampaignName],
            'status' => $secondFormStatus
        ];

        $collection = new WebformCollection();
        $collection->add(new Webform($secondFormId, $secondFormName, $secondFormScriptUrl, $secondFormCampaignName, Webform::STATUS_DISABLED, Webform::VERSION_V1));
        $collection->add(new Webform($firstFormId, $firstFormName, $firstFormScriptUrl, $firstFormCampaignName, Webform::STATUS_ENABLED, Webform::VERSION_V2));

        return [
            [[], [], new WebformCollection()],
            [[$form], [$webForm], $collection]
        ];
    }
}
