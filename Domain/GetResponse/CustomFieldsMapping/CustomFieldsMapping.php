<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping;

/**
 * Class CustomsMapping
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class CustomFieldsMapping
{
    const DEFAULT_LABEL_EMAIL = 'Email';
    const DEFAULT_LABEL_FIRST_NAME = 'First Name';
    const DEFAULT_LABEL_LAST_NAME = 'Last Name';

    const DEFAULT_YES = true;
    const DEFAULT_NO = false;

    const TYPE_CUSTOMER = 'customer';
    const TYPE_ADDRESS = 'address';

    /** @var string|null */
    private $getResponseCustomId;

    /** @var string|null */
    private $magentoAttributeCode;

    /** @var bool */
    private $default;

    /** @var string */
    private $type;

    /** @var string|null */
    private $getResponseDefaultLabel;

    /**
     * @param string|null $getResponseCustomId
     * @param string|null $magentoAttributeCode
     * @param string $type
     * @param bool $default
     * @param string|null $getResponseDefaultLabel
     */
    public function __construct($getResponseCustomId, $magentoAttributeCode, $type, $default, $getResponseDefaultLabel)
    {
        $this->getResponseCustomId = $getResponseCustomId;
        $this->magentoAttributeCode = $magentoAttributeCode;
        $this->type = $type;
        $this->default = $default;
        $this->getResponseDefaultLabel = $getResponseDefaultLabel;
    }

    /**
     * @param array $data
     * @return CustomFieldsMapping
     */
    public static function fromArray(array $data)
    {
        return new self(
            $data['getResponseCustomId'],
            $data['magentoAttributeCode'],
            $data['magentoAttributeType'],
            $data['default'],
            $data['getResponseDefaultLabel']
        );
    }

    /**
     * @return string
     */
    public function getMagentoAttributeType()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'getResponseCustomId' => $this->getGetResponseCustomId(),
            'magentoAttributeCode' => $this->getMagentoAttributeCode(),
            'magentoAttributeType' => $this->getMagentoAttributeType(),
            'getResponseDefaultLabel' => $this->getGetResponseDefaultLabel(),
            'default' => $this->isDefault(),
        ];
    }

    /**
     * @return null|string
     */
    public function getGetResponseCustomId()
    {
        return $this->getResponseCustomId;
    }

    /**
     * @return null|string
     */
    public function getMagentoAttributeCode()
    {
        return $this->magentoAttributeCode;
    }

    /**
     * @return null|string
     */
    public function getGetResponseDefaultLabel()
    {
        return $this->getResponseDefaultLabel;
    }

    /**
     * @return bool
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * @return bool
     */
    public function isTypeCustomer()
    {
        return $this->getMagentoAttributeType() === self::TYPE_CUSTOMER;
    }

    /**
     * @return bool
     */
    public function isTypeAddress()
    {
        return $this->getMagentoAttributeType() === self::TYPE_ADDRESS;
    }

}
