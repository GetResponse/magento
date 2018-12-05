<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto;

/**
 * Class CustomFieldMappingDto
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto
 */
class CustomFieldMappingDto
{
    /** @var string */
    private $magentoAttributeCode;

    /** @var string */
    private $getResponseCustomFieldId;

    /** @var string */
    private $magentoAttributeType;

    /**
     * @param string $magentoAttributeCode
     * @param string $magentoAttributeType
     * @param string $getResponseCustomFieldId
     */
    public function __construct($magentoAttributeCode, $magentoAttributeType, $getResponseCustomFieldId)
    {
        $this->magentoAttributeCode = $magentoAttributeCode;
        $this->magentoAttributeType = $magentoAttributeType;
        $this->getResponseCustomFieldId = $getResponseCustomFieldId;
    }

    /**
     * @return string
     */
    public function getMagentoAttributeType()
    {
        return $this->magentoAttributeType;
    }

    /**
     * @return string
     */
    public function getMagentoAttributeCode()
    {
        return $this->magentoAttributeCode;
    }

    /**
     * @return string
     */
    public function getGetResponseCustomFieldId()
    {
        return $this->getResponseCustomFieldId;
    }

}