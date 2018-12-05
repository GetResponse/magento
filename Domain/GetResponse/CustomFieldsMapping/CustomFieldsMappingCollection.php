<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping;

use ArrayIterator;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto\CustomFieldMappingDto;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto\CustomFieldMappingDtoCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute\MagentoCustomerAttribute;
use IteratorAggregate;

/**
 * Class CustomFieldsMappingCollection
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping
 */
class CustomFieldsMappingCollection implements IteratorAggregate
{
    /** @var array */
    private $items = [];

    /**
     * @return CustomFieldsMappingCollection
     */
    public static function createDefaults()
    {
        $collection = new self();

        $collection->add(
            new CustomFieldsMapping(
                null,
                MagentoCustomerAttribute::ATTRIBUTE_CODE_EMAIL,
                CustomFieldsMapping::TYPE_CUSTOMER,
                CustomFieldsMapping::DEFAULT_YES,
                CustomFieldsMapping::DEFAULT_LABEL_EMAIL
            )
        );

        $collection->add(
            new CustomFieldsMapping(
                null,
                MagentoCustomerAttribute::ATTRIBUTE_CODE_FIRST_NAME,
                CustomFieldsMapping::TYPE_CUSTOMER,
                CustomFieldsMapping::DEFAULT_YES,
                CustomFieldsMapping::DEFAULT_LABEL_FIRST_NAME
            )
        );

        $collection->add(
            new CustomFieldsMapping(
                null,
                MagentoCustomerAttribute::ATTRIBUTE_CODE_LAST_NAME,
                CustomFieldsMapping::TYPE_CUSTOMER,
                CustomFieldsMapping::DEFAULT_YES,
                CustomFieldsMapping::DEFAULT_LABEL_LAST_NAME
            )
        );

        return $collection;
    }

    /**
     * @param CustomFieldMappingDtoCollection $customFieldMappingDtoCollection
     * @return CustomFieldsMappingCollection
     */
    public static function createFromDto(CustomFieldMappingDtoCollection $customFieldMappingDtoCollection)
    {
        $collection = new self();

        if (!count($customFieldMappingDtoCollection)) {
            return $collection;
        }

        /** @var CustomFieldMappingDto $customFieldMappingDto */
        foreach ($customFieldMappingDtoCollection as $customFieldMappingDto) {

            $collection->add(

                new CustomFieldsMapping(
                    $customFieldMappingDto->getGetResponseCustomFieldId(),
                    $customFieldMappingDto->getMagentoAttributeCode(),
                    $customFieldMappingDto->getMagentoAttributeType(),
                    CustomFieldsMapping::DEFAULT_NO,
                    ''
                )
            );

        }

        return $collection;
    }

    /**
     * @param array $data
     * @return CustomFieldsMappingCollection
     */
    public static function createFromRepository(array $data)
    {
        $collection = new self();

        if (empty($data)) {
            return $collection;
        }

        foreach ($data as $row) {
            $collection->add(CustomFieldsMapping::fromArray($row));
        }

        return $collection;
    }

    /**
     * @param CustomFieldsMapping $item
     */
    public function add(CustomFieldsMapping $item)
    {
        $this->items[] = $item;
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = [];

        /** @var CustomFieldsMapping $item */
        foreach ($this->items as $item) {
            $result[] = $item->toArray();
        }

        return $result;
    }

}