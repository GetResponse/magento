<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\CustomFieldsMapping\Dto;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto\CustomFieldMappingDto;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto\CustomFieldMappingDtoFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto\InvalidPrefixException;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;

class CustomFieldMappingDtoFactoryTest extends BaseTestCase
{
    /** @var CustomFieldMappingDtoFactory */
    private $sut;

    protected function setUp()
    {
        $this->sut = new CustomFieldMappingDtoFactory();
    }

    /**
     * @test
     * @dataProvider getInvalidMagentoAttributeCode
     * @param string $magentoAttributeCode
     */
    public function shouldThrowInvalidPrefixExceptionOnInvalidAttributeCode($magentoAttributeCode)
    {
        $this->expectException(InvalidPrefixException::class);
        $this->sut->createFromRequestData($magentoAttributeCode, 'getResponseCustomFieldId');
    }

    /**
     * @return array
     */
    public function getInvalidMagentoAttributeCode()
    {
        return [
            ['magentoAttributeCode'],
            ['magento_AttributeCode']
        ];
    }

    /**
     * @test
     * @dataProvider getCustomFieldMappingDtoProvider
     * @param string $magentoAttributeCode
     * @param string $grCustomFieldId
     * @param CustomFieldMappingDto $customFieldMappingDto
     * @throws InvalidPrefixException
     */
    public function shouldReturnCustomFieldMappingDto($magentoAttributeCode, $grCustomFieldId, CustomFieldMappingDto $customFieldMappingDto)
    {
        $this->assertEquals(
            $customFieldMappingDto,
            $this->sut->createFromRequestData($magentoAttributeCode,$grCustomFieldId)
        );
    }

    /**
     * @return array
     */
    public function getCustomFieldMappingDtoProvider()
    {
        return [
            [
                'customer_gender',
                'grCustomFieldIdForGender',
                new CustomFieldMappingDto('gender', 'customer', 'grCustomFieldIdForGender')
            ],
            [
                'customer_date_of_birth',
                'grCustomFieldIdForDOB',
                new CustomFieldMappingDto('date_of_birth', 'customer', 'grCustomFieldIdForDOB')
            ],
            [
                'address_city',
                'grCustomFieldIdForCity',
                new CustomFieldMappingDto('city', 'address', 'grCustomFieldIdForCity')
            ],
        ];
    }
}
