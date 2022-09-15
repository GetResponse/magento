<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\CustomField;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomField\CustomFieldService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomField\CustomFieldServiceFactory;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\CustomField\CustomField;
use GrShareCode\CustomField\CustomFieldCollection;
use GrShareCode\CustomField\CustomFieldService as GrCustomFieldService;
use PHPUnit\Framework\MockObject\MockObject;

class CustomFieldServiceTest extends BaseTestCase
{
    /** @var CustomFieldServiceFactory|MockObject */
    private $customFieldServiceFactory;
    /** @var Scope|MockObject */
    private $scope;
    /** @var CustomFieldService */
    private $sut;

    protected function setUp(): void
    {
        $this->customFieldServiceFactory = $this->getMockWithoutConstructing(CustomFieldServiceFactory::class);
        $this->scope = $this->getMockWithoutConstructing(Scope::class);
        $this->sut = new CustomFieldService($this->customFieldServiceFactory);
    }

    /**
     * @test
     */
    public function shouldReturnCustomFieldsWithTextFilter()
    {
        $customField = new CustomField('393498439', 'CustomField', 'text', 'test');

        $expectedCollection = new CustomFieldCollection();
        $expectedCollection->add($customField);

        $grCustomFieldsService = $this->getMockWithoutConstructing(GrCustomFieldService::class);
        $grCustomFieldsService
            ->expects(self::once())
            ->method('getCustomFieldsForMapping')
            ->willReturn($expectedCollection);

        $this->customFieldServiceFactory
            ->expects(self::once())
            ->method('create')
            ->with($this->scope)
            ->willReturn($grCustomFieldsService);

        $customFields = $this->sut->getCustomFields($this->scope);
        self::assertEquals($expectedCollection, $customFields);
    }
}
