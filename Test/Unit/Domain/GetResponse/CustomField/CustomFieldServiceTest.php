<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\CustomField;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomField\CustomFieldService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomField\CustomFieldServiceFactory;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\CustomField\CustomFieldFilter\TextFieldCustomFieldFilter;
use GrShareCode\CustomField\CustomFieldService as GrCustomFieldService;
use PHPUnit_Framework_MockObject_MockObject;

class CustomFieldServiceTest extends BaseTestCase
{
    /** @var CustomFieldServiceFactory|PHPUnit_Framework_MockObject_MockObject */
    private $customFieldServiceFactory;

    /** @var CustomFieldService */
    private $sut;

    protected function setUp()
    {
        $this->customFieldServiceFactory = $this->getMockWithoutConstructing(CustomFieldServiceFactory::class);
        $this->sut = new CustomFieldService($this->customFieldServiceFactory);
    }

    /**
     * @test
     */
    public function shouldReturnCustomFieldsWithTextFilter()
    {
        $grCustomFieldsService = $this->getMockWithoutConstructing(GrCustomFieldService::class);
        $grCustomFieldsService
            ->expects(self::once())
            ->method('getCustomFieldsForMapping');

        $this->customFieldServiceFactory
            ->expects(self::once())
            ->method('create')
            ->willReturn($grCustomFieldsService);

        $this->sut->getCustomFields();
    }
}
