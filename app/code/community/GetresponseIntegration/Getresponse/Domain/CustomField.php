<?php

/**
 * Class GetresponseIntegration_Getresponse_Domain_CustomField
 */
class GetresponseIntegration_Getresponse_Domain_CustomField
{
    /** @var string */
    private $id;

    /** @var string */
    private $customField;

    /** @var string */
    private $customValue;

    /** @var bool */
    private $isDefault;

    /** @var bool */
    private $isActive;

    /**
     * @param string $id
     * @param string $customField
     * @param string $customValue
     * @param bool $isDefault
     * @param bool $activeCustom
     */
    public function __construct($id, $customField, $customValue, $isDefault, $activeCustom)
    {
        $this->id = $id;
        $this->customField = $customField;
        $this->customValue = $customValue;
        $this->isDefault = $isDefault;
        $this->isActive = $activeCustom;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCustomField()
    {
        return $this->customField;
    }

    /**
     * @return string
     */
    public function getCustomValue()
    {
        return $this->customValue;
    }

    /**
     * @return bool
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'id_custom' => $this->id,
            'custom_field' => $this->customField,
            'custom_value' => $this->customValue,
            'default' => $this->isDefault,
            'custom_active' => $this->isActive
        );
    }
}