<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping;

class CustomFieldsMapping
{
    const DEFAULT_LABEL_EMAIL = 'Email';
    const DEFAULT_LABEL_FIRST_NAME = 'First Name';
    const DEFAULT_LABEL_LAST_NAME = 'Last Name';

    const DEFAULT_YES = true;
    const DEFAULT_NO = false;

    const TYPE_CUSTOMER = 'customer';
    const TYPE_ADDRESS = 'address';

    private $getResponseCustomId;
    private $magentoAttributeCode;
    private $default;
    private $type;
    private $getResponseDefaultLabel;

    public function __construct(
        $getResponseCustomId,
        $magentoAttributeCode,
        string $type,
        bool $default,
        $getResponseDefaultLabel
    ) {
        $this->getResponseCustomId = $getResponseCustomId;
        $this->magentoAttributeCode = $magentoAttributeCode;
        $this->type = $type;
        $this->default = $default;
        $this->getResponseDefaultLabel = $getResponseDefaultLabel;
    }

    public static function fromArray(array $data): CustomFieldsMapping
    {
        return new self(
            $data['getResponseCustomId'],
            $data['magentoAttributeCode'],
            $data['magentoAttributeType'],
            $data['default'],
            $data['getResponseDefaultLabel']
        );
    }

    public function getMagentoAttributeType(): string
    {
        return $this->type;
    }

    public function toArray(): array
    {
        return [
            'getResponseCustomId' => $this->getGetResponseCustomId(),
            'magentoAttributeCode' => $this->getMagentoAttributeCode(),
            'magentoAttributeType' => $this->getMagentoAttributeType(),
            'getResponseDefaultLabel' => $this->getGetResponseDefaultLabel(),
            'default' => $this->isDefault(),
        ];
    }

    public function getGetResponseCustomId()
    {
        return $this->getResponseCustomId;
    }

    public function getMagentoAttributeCode()
    {
        return $this->magentoAttributeCode;
    }

    public function getGetResponseDefaultLabel()
    {
        return $this->getResponseDefaultLabel;
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function isTypeCustomer(): bool
    {
        return $this->getMagentoAttributeType() === self::TYPE_CUSTOMER;
    }

    public function isTypeAddress(): bool
    {
        return $this->getMagentoAttributeType() === self::TYPE_ADDRESS;
    }
}
