<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute;

use Exception;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMapping;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute\MagentoCustomerAttributeService;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend;

class MagentoCustomerAttributeServiceTest extends BaseTestCase
{
    /** @var MagentoCustomerAttributeService */
    private $sut;

    protected function setUp()
    {
        $this->sut = new MagentoCustomerAttributeService();
    }

    /**
     * @test
     */
    public function shouldReturnCustomerAttributeValue()
    {
        $magentoAttributeValue = 'magentoAttributeValue';
        $magentoAttributeCode = 'magentoAttributeCode';

        $customerMock = $this->getMockWithoutConstructing(Customer::class);

        $attributeFrontend = $this->getMockWithoutConstructing(AbstractFrontend::class);
        $attributeFrontend
            ->expects(self::once())
            ->method('getValue')
            ->with($customerMock)
            ->willReturn($magentoAttributeValue);

        $attribute = $this->getMockWithoutConstructing(Attribute::class);
        $attribute
            ->expects(self::once())
            ->method('getFrontend')
            ->willReturn($attributeFrontend);

        $customerMock
            ->expects(self::once())
            ->method('getAttribute')
            ->with($magentoAttributeCode)
            ->willReturn($attribute);

        $customFieldMapping = new CustomFieldsMapping(
            'getResponseCustomId',
            $magentoAttributeCode,
            'customer',
            false,
            ''
        );

        $this->assertSame(
            $magentoAttributeValue,
            $this->sut->getAttributeValue($customFieldMapping, $customerMock)
        );
    }

    /**
     * @test
     */
    public function shouldReturnNullIfNoAttributeWithCodeFoundedWithinCustomerAttribute()
    {
        $magentoAttributeCode = 'magentoAttributeCode';

        $customerMock = $this->getMockWithoutConstructing(Customer::class);

        $customerMock
            ->expects(self::once())
            ->method('getAttribute')
            ->with($magentoAttributeCode)
            ->willReturn(null);

        $customFieldMapping = new CustomFieldsMapping(
            'getResponseCustomId',
            $magentoAttributeCode,
            'customer',
            false,
            ''
        );

        $this->assertNull(
            $this->sut->getAttributeValue($customFieldMapping, $customerMock)
        );
    }

    /**
     * @test
     */
    public function shouldReturnNullIfExceptionIsThrownWithinCustomerAttribute()
    {
        $magentoAttributeCode = 'magentoAttributeCode';

        $customerMock = $this->getMockWithoutConstructing(Customer::class);

        $customerMock
            ->expects(self::once())
            ->method('getAttribute')
            ->willThrowException(new Exception());

        $customFieldMapping = new CustomFieldsMapping(
            'getResponseCustomId',
            $magentoAttributeCode,
            'customer',
            false,
            ''
        );

        $this->assertNull(
            $this->sut->getAttributeValue($customFieldMapping, $customerMock)
        );
    }

    /**
     * @test
     */
    public function shouldReturnCustomerAddressAttributeValue()
    {
        $magentoAttributeValue = 'magentoAttributeValue';
        $magentoAttributeCode = 'magentoAttributeCode';

        $customerMock = $this->getMockWithoutConstructing(Customer::class);

        $address = $this->getMockWithoutConstructing(Address::class);
        $address
            ->expects(self::once())
            ->method('getAttributes')
            ->willReturn([$magentoAttributeCode => 'somethingNotNull']);

        $address
            ->expects(self::once())
            ->method('getData')
            ->with($magentoAttributeCode)
            ->willReturn($magentoAttributeValue);

        $customerMock
            ->expects(self::once())
            ->method('getDefaultShippingAddress')
            ->willReturn($address);

        $customFieldMapping = new CustomFieldsMapping(
            'getResponseCustomId',
            $magentoAttributeCode,
            'address',
            false,
            ''
        );

        $this->assertSame(
            $magentoAttributeValue,
            $this->sut->getAttributeValue($customFieldMapping, $customerMock)
        );
    }

    /**
     * @test
     */
    public function shouldReturnNullIfNoShippingAddressFoundedWithinAddressAttribute()
    {
        $magentoAttributeCode = 'magentoAttributeCode';

        $customerMock = $this->getMockWithoutConstructing(Customer::class);

        $customerMock
            ->expects(self::once())
            ->method('getDefaultShippingAddress')
            ->willReturn(null);

        $customFieldMapping = new CustomFieldsMapping(
            'getResponseCustomId',
            $magentoAttributeCode,
            'address',
            false,
            ''
        );

        $this->assertNull(
            $this->sut->getAttributeValue($customFieldMapping, $customerMock)
        );
    }

    /**
     * @test
     */
    public function shouldReturnNullIfNoShippingAddressAttributeFoundedWithinAddressAttribute()
    {
        $magentoAttributeCode = 'magentoAttributeCode';

        $customerMock = $this->getMockWithoutConstructing(Customer::class);

        $address = $this->getMockWithoutConstructing(Address::class);
        $address
            ->expects(self::once())
            ->method('getAttributes')
            ->willReturn(null);

        $customerMock
            ->expects(self::once())
            ->method('getDefaultShippingAddress')
            ->willReturn($address);

        $customFieldMapping = new CustomFieldsMapping(
            'getResponseCustomId',
            $magentoAttributeCode,
            'address',
            false,
            ''
        );

        $this->assertNull(
            $this->sut->getAttributeValue($customFieldMapping, $customerMock)
        );
    }

    /**
     * @test
     */
    public function shouldReturnNullIfExceptionIsThrownInAddressAttribute()
    {
        $magentoAttributeCode = 'magentoAttributeCode';

        $customerMock = $this->getMockWithoutConstructing(Customer::class);

        $customerMock
            ->expects(self::once())
            ->method('getDefaultShippingAddress')
            ->willThrowException(new Exception());

        $customFieldMapping = new CustomFieldsMapping(
            'getResponseCustomId',
            $magentoAttributeCode,
            'address',
            false,
            ''
        );

        $this->assertNull(
            $this->sut->getAttributeValue($customFieldMapping, $customerMock)
        );
    }
}
