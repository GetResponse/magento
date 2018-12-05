<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Block;

use GetResponse\GetResponseIntegration\Block\Export;
use GetResponse\GetResponseIntegration\Block\Export as ExportBlock;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomField\CustomFieldService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMapping;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingService;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\Api\GetresponseApiClient;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class ExportTest
 * @package GetResponse\GetResponseIntegration\Test\Unit\Block
 */
class ExportTest extends BaseTestCase
{
    /** @var Context|\PHPUnit_Framework_MockObject_MockObject */
    private $context;

    /** @var Repository|\PHPUnit_Framework_MockObject_MockObject */
    private $repository;

    /** @var ApiClientFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $apiClientFactory;

    /** @var ExportBlock */
    private $exportBlock;

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var GetresponseApiClient|\PHPUnit_Framework_MockObject_MockObject */
    private $grApiClient;

    /** @var CustomFieldService|\PHPUnit_Framework_MockObject_MockObject */
    private $customFieldsService;

    /** @var CustomFieldsMappingService|\PHPUnit_Framework_MockObject_MockObject */
    private $customFieldsMappingService;

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
        $this->customFieldsService = $this->getMockWithoutConstructing(CustomFieldService::class);
        $this->customFieldsMappingService = $this->getMockWithoutConstructing(CustomFieldsMappingService::class);
        $this->messageManager = $this->getMockWithoutConstructing(ManagerInterface::class);
        $this->redirectFactory = $this->getMockWithoutConstructing(RedirectFactory::class);
        $this->logger = $this->getMockWithoutConstructing(Logger::class);

        $this->exportBlock = new ExportBlock(
            $this->context,
            $this->messageManager,
            $this->redirectFactory,
            $this->apiClientFactory,
            $this->logger,
            $this->repository,
            $this->customFieldsService,
            $this->customFieldsMappingService
        );
    }

    /**
     * @test
     *
     * @param array $rawCustoms
     * @param CustomFieldsMapping $expectedFirstCustom
     * @dataProvider shouldReturnCustomsProvider
     */
    public function shouldReturnCustoms(array $rawCustoms, CustomFieldsMapping $expectedFirstCustom)
    {
        $this->repository->expects($this->once())->method('getCustomFieldsMappingForRegistration')->willReturn($rawCustoms);

        $customFieldMappingCollection = $this->exportBlock->getCustomFieldsMapping();
        self::assertInstanceOf(CustomFieldsMappingCollection::class, $customFieldMappingCollection);

        if (count($customFieldMappingCollection->getIterator())) {

            $custom = $customFieldMappingCollection->getIterator()[0];
            self::assertInstanceOf(CustomFieldsMapping::class, $custom);
            self::assertEquals($expectedFirstCustom->getMagentoAttributeCode(), $custom->getMagentoAttributeCode());
            self::assertEquals($expectedFirstCustom->getGetResponseCustomId(), $custom->getGetResponseCustomId());
            self::assertEquals($expectedFirstCustom->isDefault(), $custom->isDefault());
            self::assertEquals($expectedFirstCustom->getGetResponseDefaultLabel(), $custom->getGetResponseDefaultLabel());
        }
    }

    /**
     * @return array
     */
    public function shouldReturnCustomsProvider()
    {
        $getResponseCustomId = 'getResponseCustomId';
        $magentoAttributeCode = 'magentoAttributeCode';
        $magentoAttributeType = 'magentoAttributeType';
        $isDefault = false;
        $getResponseDefaultLabel = '';

        $rawCustomField = [
            'getResponseCustomId' => $getResponseCustomId,
            'magentoAttributeCode' => $magentoAttributeCode,
            'magentoAttributeType' => $magentoAttributeType,
            'getResponseDefaultLabel' => $getResponseDefaultLabel,
            'default' => $isDefault
        ];

        return [
            [
                [],
                new CustomFieldsMapping(0, '', '', '', '')
            ],
            [
                [$rawCustomField],
                new CustomFieldsMapping(
                    $getResponseCustomId,
                    $magentoAttributeCode,
                    $magentoAttributeType,
                    $isDefault,
                    $getResponseDefaultLabel
                )
            ]
        ];
    }
}
