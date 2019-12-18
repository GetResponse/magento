<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto;

use ArrayIterator;
use IteratorAggregate;
use Countable;

/**
 * Class CustomFieldMappingDtoCollection
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto
 */
class CustomFieldMappingDtoCollection implements Countable, IteratorAggregate
{
    /** @var CustomFieldMappingDtoFactory */
    private $customFieldMappingDtoFactory;

    /** @var array */
    private $items = [];

    /**
     * @param CustomFieldMappingDtoFactory $customFieldMappingDtoFactory
     */
    public function __construct(CustomFieldMappingDtoFactory $customFieldMappingDtoFactory)
    {
        $this->customFieldMappingDtoFactory = $customFieldMappingDtoFactory;
    }

    /**
     * @param array $data
     * @return CustomFieldMappingDtoCollection
     * @throws InvalidPrefixException
     */
    public function createFromRequestData(array $data)
    {
        $collection = new self($this->customFieldMappingDtoFactory);

        if (!isset($data['gr_sync_order_data'], $data['custom'])) {
            return $collection;
        }

        foreach ($data['custom'] as $key => $customs) {

            $collection->add(
                $this->customFieldMappingDtoFactory->createFromRequestData(
                    $data['custom'][$key],
                    $data['gr_custom'][$key]
                )
            );
        }

        return $collection;
    }

    /**
     * @param CustomFieldMappingDto $item
     */
    public function add(CustomFieldMappingDto $item)
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
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }
}