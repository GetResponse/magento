<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand\Dto\ExportOnDemandDto;

/**
 * Class ExportOnDemand
 */
class ExportOnDemand
{
    /** @var string */
    private $contactListId;

    /** @var null|int */
    private $dayOfCycle;

    /** @var null|string */
    private $shopId;

    /** @var CustomFieldsMappingCollection */
    private $customFieldsMappingCollection;

    /**
     * @param string $contactListId
     * @param int|null $dayOfCycle
     * @param null|string $shopId
     * @param CustomFieldsMappingCollection $customFieldsMappingCollection
     */
    public function __construct(
        $contactListId,
        $dayOfCycle,
        $shopId,
        CustomFieldsMappingCollection $customFieldsMappingCollection
    ) {
        $this->contactListId = $contactListId;
        $this->dayOfCycle = $dayOfCycle;
        $this->shopId = $shopId;
        $this->customFieldsMappingCollection = $customFieldsMappingCollection;
    }

    /**
     * @param ExportOnDemandDto $exportOnDemandDto
     * @return ExportOnDemand
     */
    public static function createFromDto(ExportOnDemandDto $exportOnDemandDto)
    {
        return new self(
            $exportOnDemandDto->getContactListId(),
            $exportOnDemandDto->getDayOfCycle(),
            $exportOnDemandDto->getShopId(),
            CustomFieldsMappingCollection::createFromDto($exportOnDemandDto->getCustomFieldMappingDtoCollection())
        );
    }

    /**
     * @return string
     */
    public function getContactListId()
    {
        return $this->contactListId;
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
    public function isUpdateContactCustomFieldEnabled()
    {
        return 0 !== count($this->getCustomFieldsMappingCollection());
    }

    /**
     * @return CustomFieldsMappingCollection
     */
    public function getCustomFieldsMappingCollection()
    {
        return $this->customFieldsMappingCollection;
    }

    /**
     * @return bool
     */
    public function isSendEcommerceDataEnabled()
    {
        return null !== $this->getShopId();
    }

    /**
     * @return null|string
     */
    public function getShopId()
    {
        return $this->shopId;
    }
}