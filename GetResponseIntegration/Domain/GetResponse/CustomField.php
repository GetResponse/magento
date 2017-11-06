<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

/**
 * Class Custom
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class CustomField
{
    /** @var string */
    private $id;

    /** @var string */
    private $customField;

    /** @var string */
    private $customValue;

    /** @var string */
    private $customName;

    /** @var int */
    private $isDefault;

    /** @var int */
    private $isActive;

    /**
     * @param string $id
     * @param string $customField
     * @param string $customValue
     * @param string $customName
     * @param int $isDefault
     * @param int $isActive
     */
    public function __construct($id, $customField, $customValue, $customName, $isDefault, $isActive)
    {
        $this->id = $id;
        $this->customField = $customField;
        $this->customValue = $customValue;
        $this->customName = $customName;
        $this->isDefault = $isDefault;
        $this->isActive = $isActive;
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
     * @return string
     */
    public function getCustomName()
    {
        return $this->customName;
    }

    /**
     * @return bool
     */
    public function isDefault()
    {
        return (bool)$this->isDefault;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return (bool)$this->isActive;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'customField' => $this->customField,
            'customValue' => $this->customValue,
            'customName' => $this->customName,
            'isDefault' => $this->isDefault,
            'isActive' => $this->isActive
        ];
    }
}
