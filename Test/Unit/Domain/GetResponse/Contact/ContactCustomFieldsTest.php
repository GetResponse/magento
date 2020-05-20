<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\Contact;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactCustomFieldsCollectionFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMapping;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute\MagentoCustomerAttributeService;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\Contact\ContactCustomField\ContactCustomField;
use GrShareCode\Contact\ContactCustomField\ContactCustomFieldsCollection;
use Magento\Customer\Model\Customer;
use PHPUnit\Framework\MockObject\MockObject;

class ContactCustomFieldsTest extends BaseTestCase
{
    /** @var ContactCustomFieldsCollectionFactory */
    private $sut;

    /** @var MagentoCustomerAttributeService|MockObject */
    private $magentoCustomerAttributeService;

    protected function setUp()
    {
        $this->magentoCustomerAttributeService = $this->getMockWithoutConstructing(MagentoCustomerAttributeService::class);
        $this->sut = new ContactCustomFieldsCollectionFactory($this->magentoCustomerAttributeService);
    }

    /**
     * @test
     */
    public function shouldReturnEmptyContactCustomFieldsCollectionForSubscriber()
    {
        self::assertEquals(new ContactCustomFieldsCollection(), $this->sut->createForSubscriber());
    }

    /**
     * @test
     */
    public function shouldReturnEmptyContactCustomFieldsCollectionWhenUpdateContactIsDisabled()
    {
        $getResponseCustomId1 = 'grCustomId1';
        $getResponseCustomId2 = 'grCustomId2';
        $getResponseCustomId3 = 'grCustomId3';

        $attributeCode1 = 'attrCode1';
        $attributeCode2 = 'attrCode2';
        $attributeCode3 = 'attrCode3';

        $attributeType1 = 'customer';
        $attributeType2 = 'address';
        $attributeType3 = 'customer';

        /** @var Customer|MockObject $customer */
        $customer = $this->getMockWithoutConstructing(Customer::class);
        $customFieldsMappingCollection = CustomFieldsMappingCollection::createDefaults();

        $customFieldsMapping1 = new CustomFieldsMapping($getResponseCustomId1, $attributeCode1, $attributeType1, false, '');
        $customFieldsMapping2 = new CustomFieldsMapping($getResponseCustomId2, $attributeCode2, $attributeType2, false, '');
        $customFieldsMapping3 = new CustomFieldsMapping($getResponseCustomId3, $attributeCode3, $attributeType3, false, '');

        $customFieldsMappingCollection->add($customFieldsMapping1);
        $customFieldsMappingCollection->add($customFieldsMapping2);
        $customFieldsMappingCollection->add($customFieldsMapping3);

        $isCustomFieldUpdateEnabled = false;

        $actualContactCustomFieldsCollection = $this->sut->createForCustomer($customer, $customFieldsMappingCollection, $isCustomFieldUpdateEnabled);

        $this->assertEquals(new ContactCustomFieldsCollection(), $actualContactCustomFieldsCollection);
    }

    /**
     * @test
     */
    public function shouldReturnContactCustomFieldCollectionForCustomer()
    {
        $getResponseCustomId1 = 'grCustomId1';
        $getResponseCustomId2 = 'grCustomId2';
        $getResponseCustomId3 = 'grCustomId3';

        $attributeCode1 = 'attrCode1';
        $attributeCode2 = 'attrCode2';
        $attributeCode3 = 'attrCode3';

        $attributeType1 = 'customer';
        $attributeType2 = 'address';
        $attributeType3 = 'customer';

        $customerAttributeValue1 = 'AttributeValue1';
        $customerAttributeValue2 = 'AttributeValue2';
        $customerAttributeValue3 = 'AttributeValue3';

        /** @var Customer|MockObject $customer */
        $customer = $this->getMockWithoutConstructing(Customer::class);
        $customFieldsMappingCollection = CustomFieldsMappingCollection::createDefaults();

        $customFieldsMapping1 = new CustomFieldsMapping($getResponseCustomId1, $attributeCode1, $attributeType1, false, '');
        $customFieldsMapping2 = new CustomFieldsMapping($getResponseCustomId2, $attributeCode2, $attributeType2, false, '');
        $customFieldsMapping3 = new CustomFieldsMapping($getResponseCustomId3, $attributeCode3, $attributeType3, false, '');

        $customFieldsMappingCollection->add($customFieldsMapping1);
        $customFieldsMappingCollection->add($customFieldsMapping2);
        $customFieldsMappingCollection->add($customFieldsMapping3);

        $this->magentoCustomerAttributeService
            ->expects(self::exactly(3))
            ->method('getAttributeValue')
            ->withConsecutive(
                [$customFieldsMapping1, $customer],
                [$customFieldsMapping2, $customer],
                [$customFieldsMapping3, $customer]
             )
            ->willReturn($customerAttributeValue1, $customerAttributeValue2, $customerAttributeValue3);

        $isCustomFieldUpdateEnabled = true;

        $expectedContactCustomFieldsCollection = new ContactCustomFieldsCollection();
        $expectedContactCustomFieldsCollection->add(new ContactCustomField($getResponseCustomId1, [$customerAttributeValue1]));
        $expectedContactCustomFieldsCollection->add(new ContactCustomField($getResponseCustomId2, [$customerAttributeValue2]));
        $expectedContactCustomFieldsCollection->add(new ContactCustomField($getResponseCustomId3, [$customerAttributeValue3]));

        $actualContactCustomFieldsCollection = $this->sut->createForCustomer($customer, $customFieldsMappingCollection, $isCustomFieldUpdateEnabled);

        $this->assertEquals($expectedContactCustomFieldsCollection, $actualContactCustomFieldsCollection);
    }

    /**
     * @test
     */
    public function shouldReturnEmptyContactCustomFieldCollectionForEmptyCustomFieldMapping()
    {
        /** @var Customer|MockObject $customer */
        $customer = $this->getMockWithoutConstructing(Customer::class);

        $customFieldsMappingCollection = CustomFieldsMappingCollection::createDefaults();
        $isCustomFieldUpdateEnabled = true;

        $expectedContactCustomFieldsCollection = new ContactCustomFieldsCollection();
        $actualContactCustomFieldsCollection = $this->sut->createForCustomer($customer, $customFieldsMappingCollection, $isCustomFieldUpdateEnabled);

        $this->assertEquals($expectedContactCustomFieldsCollection, $actualContactCustomFieldsCollection);
    }

    /**
     * @test
     */
    public function shouldReturnContactCustomFieldCollectionForEmptyAttributesValue()
    {
        $getResponseCustomId1 = 'grCustomId1';
        $getResponseCustomId2 = 'grCustomId2';
        $getResponseCustomId3 = 'grCustomId3';

        $attributeCode1 = 'attrCode1';
        $attributeCode2 = 'attrCode2';
        $attributeCode3 = 'attrCode3';

        $attributeType1 = 'customer';
        $attributeType2 = 'address';
        $attributeType3 = 'customer';

        $customerAttributeValue1 = null;
        $customerAttributeValue2 = 'AttributeValue2';
        $customerAttributeValue3 = null;

        /** @var Customer|MockObject $customer */
        $customer = $this->getMockWithoutConstructing(Customer::class);
        $customFieldsMappingCollection = CustomFieldsMappingCollection::createDefaults();

        $customFieldsMapping1 = new CustomFieldsMapping($getResponseCustomId1, $attributeCode1, $attributeType1, false, '');
        $customFieldsMapping2 = new CustomFieldsMapping($getResponseCustomId2, $attributeCode2, $attributeType2, false, '');
        $customFieldsMapping3 = new CustomFieldsMapping($getResponseCustomId3, $attributeCode3, $attributeType3, false, '');

        $customFieldsMappingCollection->add($customFieldsMapping1);
        $customFieldsMappingCollection->add($customFieldsMapping2);
        $customFieldsMappingCollection->add($customFieldsMapping3);

        $this->magentoCustomerAttributeService
            ->expects(self::exactly(3))
            ->method('getAttributeValue')
            ->withConsecutive(
                [$customFieldsMapping1, $customer],
                [$customFieldsMapping2, $customer],
                [$customFieldsMapping3, $customer]
            )
            ->willReturn($customerAttributeValue1, $customerAttributeValue2, $customerAttributeValue3);

        $isCustomFieldUpdateEnabled = true;

        $expectedContactCustomFieldsCollection = new ContactCustomFieldsCollection();
        $expectedContactCustomFieldsCollection->add(new ContactCustomField($getResponseCustomId2, [$customerAttributeValue2]));

        $actualContactCustomFieldsCollection = $this->sut->createForCustomer($customer, $customFieldsMappingCollection, $isCustomFieldUpdateEnabled);

        $this->assertEquals($expectedContactCustomFieldsCollection, $actualContactCustomFieldsCollection);
    }

}
