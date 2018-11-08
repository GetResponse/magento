<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Block;

use GetResponse\GetResponseIntegration\Block\Webform as WebformBlock;
use GetResponse\GetResponseIntegration\Domain\GetResponse\GetresponseApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\GetresponseApiClient;
use GrShareCode\WebForm\WebForm;
use GrShareCode\WebForm\WebFormCollection;
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

    /** @var GetresponseApiClientFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $repositoryFactory;

    /** @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $objectManager;

    /** @var WebformBlock */
    private $webformBlock;

    /** @var GetresponseApiClient|\PHPUnit_Framework_MockObject_MockObject */
    private $grApiClient;

    public function setUp()
    {
        $this->context = $this->getMockWithoutConstructing(Context::class);
        $this->repository = $this->getMockWithoutConstructing(Repository::class);
        $this->objectManager = $this->getMockWithoutConstructing(ObjectManagerInterface::class);
        $this->repositoryFactory = $this->getMockWithoutConstructing(GetresponseApiClientFactory::class);
        $this->grApiClient = $this->getMockWithoutConstructing(GetresponseApiClient::class);
        $this->repositoryFactory->expects($this->once())->method('createGetResponseApiClient')->willReturn($this->grApiClient);
        $this->webformBlock = new WebformBlock($this->context, $this->repository, $this->repositoryFactory);
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
        $this->grApiClient->method('getForms')->willReturn($rawFormsData);
        $this->grApiClient->method('getWebForms')->willReturn($rawWebformsData);

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
        $collection->add(new Webform($secondFormId, $secondFormName, $secondFormScriptUrl, $secondFormCampaignName, Webform::STATUS_DISABLED));
        $collection->add(new Webform($firstFormId, $firstFormName, $firstFormScriptUrl, $firstFormCampaignName, Webform::STATUS_ENABLED));

        return [
            [[], [], new WebformCollection()],
            [[$form], [$webForm], $collection]
        ];
    }
}
