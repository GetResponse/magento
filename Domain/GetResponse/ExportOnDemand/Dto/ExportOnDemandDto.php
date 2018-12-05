<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand\Dto;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto\CustomFieldMappingDtoCollection;

/**
 * Class ExportOnDemandDto
 */
class ExportOnDemandDto
{
    /** @var string */
    private $contactListId;

    /** @var bool */
    private $autoresponderEnabled;

    /** @var null|int */
    private $dayOfCycle;

    /** @var bool */
    private $ecommerceEnabled;

    /** @var null|string */
    private $shopId;

    /** @var bool */
    private $updateContactCustomFieldEnabled;

    /** @var CustomFieldMappingDtoCollection */
    private $customFieldMappingDtoCollection;

    /**
     * @param string $contactListId
     * @param bool $autoresponderEnabled
     * @param int|null $dayOfCycle
     * @param bool $ecommerceEnabled
     * @param string|null $shopId
     * @param bool $updateContactCustomFieldEnabled
     * @param CustomFieldMappingDtoCollection $customFieldMappingDtoCollection
     */
    public function __construct(
        $contactListId,
        $autoresponderEnabled,
        $dayOfCycle,
        $ecommerceEnabled,
        $shopId,
        $updateContactCustomFieldEnabled,
        CustomFieldMappingDtoCollection $customFieldMappingDtoCollection
    ) {
        $this->contactListId = $contactListId;
        $this->autoresponderEnabled = $autoresponderEnabled;
        $this->dayOfCycle = $dayOfCycle;
        $this->ecommerceEnabled = $ecommerceEnabled;
        $this->shopId = $shopId;
        $this->updateContactCustomFieldEnabled = $updateContactCustomFieldEnabled;
        $this->customFieldMappingDtoCollection = $customFieldMappingDtoCollection;
    }

    /**
     * @return string
     */
    public function getContactListId()
    {
        return $this->contactListId;
    }

    /**
     * @return bool
     */
    public function isAutoresponderEnabled()
    {
        return $this->autoresponderEnabled;
    }

    /**
     * @return int|null
     */
    public function getDayOfCycle()
    {
        return $this->dayOfCycle;
    }

    /**
     * @return bool
     */
    public function isEcommerceEnabled()
    {
        return $this->ecommerceEnabled;
    }

    /**
     * @return string|null
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * @return bool
     */
    public function isUpdateContactCustomFieldEnabled()
    {
        return $this->updateContactCustomFieldEnabled;
    }

    /**
     * @return CustomFieldMappingDtoCollection
     */
    public function getCustomFieldMappingDtoCollection()
    {
        return $this->customFieldMappingDtoCollection;
    }

}