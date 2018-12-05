<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute;

use Magento\Eav\Model\Entity\Attribute;

/**
 * Class MagentoCustomerAttribute
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute
 */
class MagentoCustomerAttribute
{
    const ATTRIBUTE_CODE_EMAIL = 'email';
    const ATTRIBUTE_CODE_FIRST_NAME = 'firstname';
    const ATTRIBUTE_CODE_LAST_NAME = 'lastname';

    const ATTRIBUTE_TYPE_CUSTOMER = 'customer';
    const ATTRIBUTE_TYPE_ADDRESS = 'address';

    /** @var int */
    private $attributeCode;

    /** @var string */
    private $frontendLabel;

    /** @var string */
    private $attributeType;

    /**
     * @param string $attributeCode
     * @param string $attributeType
     * @param string $frontendLabel
     */
    public function __construct($attributeCode, $attributeType, $frontendLabel)
    {
        $this->attributeCode = $attributeCode;
        $this->attributeType = $attributeType;
        $this->frontendLabel = $frontendLabel;
    }

    /**
     * @param Attribute $attribute
     * @return MagentoCustomerAttribute
     */
    public static function createFromCustomerAttribute(Attribute $attribute)
    {
        return new self(
            $attribute->getAttributeCode(),
            self::ATTRIBUTE_TYPE_CUSTOMER,
            $attribute->getFrontendLabel()
        );
    }

    /**
     * @param Attribute $addressAttribute
     * @return MagentoCustomerAttribute
     */
    public static function createFromAddressAttribute(Attribute $addressAttribute)
    {
        return new self(
            $addressAttribute->getAttributeCode(),
            self::ATTRIBUTE_TYPE_ADDRESS,
            'Ship. Address: ' . $addressAttribute->getFrontendLabel()
        );
    }

    /**
     * @return string
     */
    public function getFrontendLabel()
    {
        return $this->frontendLabel;
    }

    /**
     * @return bool
     */
    public function isAttributeTypeCustomer()
    {
        return self::ATTRIBUTE_TYPE_CUSTOMER === $this->getAttributeType();
    }

    /**
     * @return string
     */
    public function getAttributeType()
    {
        return $this->attributeType;
    }

    /**
     * @param string $attributeCode
     * @return bool
     */
    public function hasSameCode($attributeCode)
    {
        return $this->getAttributeCode() === $attributeCode;
    }

    /**
     * @return int
     */
    public function getAttributeCode()
    {
        return $this->attributeCode;
    }
}