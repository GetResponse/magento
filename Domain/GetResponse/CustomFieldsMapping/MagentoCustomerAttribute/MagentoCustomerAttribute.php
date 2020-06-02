<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute;

use Magento\Eav\Model\Entity\Attribute;

class MagentoCustomerAttribute
{
    const ATTRIBUTE_CODE_EMAIL = 'email';
    const ATTRIBUTE_CODE_FIRST_NAME = 'firstname';
    const ATTRIBUTE_CODE_LAST_NAME = 'lastname';
    const ATTRIBUTE_TYPE_CUSTOMER = 'customer';
    const ATTRIBUTE_TYPE_ADDRESS = 'address';

    private $attributeCode;
    private $frontendLabel;
    private $attributeType;

    public function __construct(string $attributeCode, string $attributeType, string $frontendLabel)
    {
        $this->attributeCode = $attributeCode;
        $this->attributeType = $attributeType;
        $this->frontendLabel = $frontendLabel;
    }

    public static function createFromCustomerAttribute(Attribute $attribute): MagentoCustomerAttribute
    {
        return new self(
            $attribute->getAttributeCode(),
            self::ATTRIBUTE_TYPE_CUSTOMER,
            $attribute->getFrontendLabel()
        );
    }

    public static function createFromAddressAttribute(Attribute $addressAttribute): MagentoCustomerAttribute
    {
        return new self(
            $addressAttribute->getAttributeCode(),
            self::ATTRIBUTE_TYPE_ADDRESS,
            'Ship. Address: ' . $addressAttribute->getFrontendLabel()
        );
    }

    public function getFrontendLabel(): string
    {
        return $this->frontendLabel;
    }

    public function isAttributeTypeCustomer(): bool
    {
        return self::ATTRIBUTE_TYPE_CUSTOMER === $this->getAttributeType();
    }

    public function getAttributeType(): string
    {
        return $this->attributeType;
    }

    public function hasSameCode(string $attributeCode): bool
    {
        return $this->getAttributeCode() === $attributeCode;
    }

    public function getAttributeCode(): string
    {
        return $this->attributeCode;
    }
}
