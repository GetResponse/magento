<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\CustomFieldsMapping;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute\MagentoCustomerAttribute;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute\MagentoCustomerAttributeCollection;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Customer\Model\ResourceModel\Address\Attribute\CollectionFactory as AddressCollectionFactory;
use Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;

class CustomFieldsMappingServiceTest extends BaseTestCase
{
    /** @var Repository|MockObject */
    private $repository;
    /** @var CollectionFactory|MockObject */
    private $customerAttributeCollectionFactory;
    /** @var AddressCollectionFactory|MockObject */
    private $addressAttributeCollectionFactory;
    /** @var Scope|MockObject */
    private $scope;
    /** @var CustomFieldsMappingService */
    private $sut;

    protected function setUp(): void
    {
        $this->repository = $this->getMockWithoutConstructing(Repository::class);
        $this->customerAttributeCollectionFactory = $this->getMockWithoutConstructing(CollectionFactory::class, [], ['create']);
        $this->addressAttributeCollectionFactory = $this->getMockWithoutConstructing(AddressCollectionFactory::class, [], ['create']);
        $this->scope = $this->getMockWithoutConstructing(Scope::class);

        $this->sut = new CustomFieldsMappingService(
            $this->repository,
            $this->customerAttributeCollectionFactory,
            $this->addressAttributeCollectionFactory
        );
    }

    /**
     * @test
     */
    public function shouldSetDefaultCustomFieldsMapping()
    {
        $defaultCustomFieldMappingCollection = [
            [
                'getResponseCustomId' => null,
                'magentoAttributeCode' => 'email',
                'getResponseDefaultLabel' => 'Email',
                'default' => true,
                'magentoAttributeType' => 'customer'
            ],
            [
                'getResponseCustomId' => null,
                'magentoAttributeCode' => 'firstname',
                'getResponseDefaultLabel' => 'First Name',
                'default' => true,
                'magentoAttributeType' => 'customer'
            ],
            [
                'getResponseCustomId' => null,
                'magentoAttributeCode' => 'lastname',
                'getResponseDefaultLabel' => 'Last Name',
                'default' => true,
                'magentoAttributeType' => 'customer'
            ]
        ];

        $this->repository
            ->expects(self::once())
            ->method('setCustomsOnInit')
            ->with($defaultCustomFieldMappingCollection);

        $this->sut->setDefaultCustomFields($this->scope);
    }

    /**
     * @test
     */
    public function shouldReturnCustomerAttributes()
    {
        $attribute1GenderCode = 'gender';
        $attribute1GenderLabel = 'Gender';
        $attribute2City = 'city';
        $attribute2CityLabel = 'City';

        $customerAttribute1 = $this->getMockWithoutConstructing(\Magento\Customer\Model\Attribute::class);

        $customerAttribute1
            ->expects(self::exactly(2))
            ->method('getAttributeCode')
            ->willReturn($attribute1GenderCode);

        $customerAttribute1
            ->expects(self::exactly(2))
            ->method('__call')
            ->with('getFrontendLabel')
            ->willReturn($attribute1GenderLabel);

        $customerAttribute2 = $this->getMockWithoutConstructing(\Magento\Customer\Model\Attribute::class);

        $customerAttribute2
            ->expects(self::once())
            ->method('getAttributeCode')
            ->willReturn($attribute2City);

        $customerAttribute2
            ->expects(self::exactly(2))
            ->method('__call')
            ->with('getFrontendLabel')
            ->willReturn($attribute2CityLabel);

        $this->customerAttributeCollectionFactory
            ->expects(self::once())
            ->method('create')
            ->willReturn([$customerAttribute1]);

        $this->addressAttributeCollectionFactory
            ->expects(self::once())
            ->method('create')
            ->willReturn([$customerAttribute2]);

        $expectedAttributeCollection =  new MagentoCustomerAttributeCollection();
        $expectedAttributeCollection->add(new MagentoCustomerAttribute($attribute1GenderCode, 'customer', $attribute1GenderLabel));
        $expectedAttributeCollection->add(new MagentoCustomerAttribute($attribute2City, 'address', 'Ship. Address: ' . $attribute2CityLabel));

        $this->assertEquals($expectedAttributeCollection, $this->sut->getMagentoCustomerAttributes());
    }

    /**
     * @test
     */
    public function shouldNotReturnCustomerAttributesIfLabelNotFound()
    {
        $customerAttribute1 = $this->getMockWithoutConstructing(\Magento\Customer\Model\Attribute::class);

        $customerAttribute1
            ->expects(self::once())
            ->method('__call')
            ->with('getFrontendLabel')
            ->willReturn(null);

        $customerAttribute2 = $this->getMockWithoutConstructing(\Magento\Customer\Model\Attribute::class);

        $customerAttribute2
            ->expects(self::once())
            ->method('__call')
            ->with('getFrontendLabel')
            ->willReturn(null);

        $this->customerAttributeCollectionFactory
            ->expects(self::once())
            ->method('create')
            ->willReturn([$customerAttribute1, $customerAttribute2]);

        $this->addressAttributeCollectionFactory
            ->expects(self::once())
            ->method('create')
            ->willReturn([]);

        $this->assertEquals(new MagentoCustomerAttributeCollection(), $this->sut->getMagentoCustomerAttributes());
    }

    /**
     * @test
     */
    public function shouldNotReturnCustomerAttributesIfAttributeCodeIsBlacklisted()
    {
        $attribute1Code = 'disable_auto_group_change';
        $attribute1Label = 'Disable Auto Group Change';
        $attribute2Code = 'store_id';
        $attribute2Label = 'StoreId';

        $customerAttribute1 = $this->getMockWithoutConstructing(\Magento\Customer\Model\Attribute::class);

        $customerAttribute1
            ->expects(self::once())
            ->method('getAttributeCode')
            ->willReturn($attribute1Code);

        $customerAttribute1
            ->expects(self::once())
            ->method('__call')
            ->with('getFrontendLabel')
            ->willReturn($attribute1Label);

        $customerAttribute2 = $this->getMockWithoutConstructing(\Magento\Customer\Model\Attribute::class);

        $customerAttribute2
            ->expects(self::once())
            ->method('getAttributeCode')
            ->willReturn($attribute2Code);

        $customerAttribute2
            ->expects(self::once())
            ->method('__call')
            ->with('getFrontendLabel')
            ->willReturn($attribute2Label);

        $this->customerAttributeCollectionFactory
            ->expects(self::once())
            ->method('create')
            ->willReturn([$customerAttribute1, $customerAttribute2]);

        $this->addressAttributeCollectionFactory
            ->expects(self::once())
            ->method('create')
            ->willReturn([]);

        $expectedAttributeCollection =  new MagentoCustomerAttributeCollection();
        $this->assertEquals($expectedAttributeCollection, $this->sut->getMagentoCustomerAttributes());
    }
}
