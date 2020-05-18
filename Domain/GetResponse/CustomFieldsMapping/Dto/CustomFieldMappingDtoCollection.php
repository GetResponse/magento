<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto;

use ArrayIterator;
use IteratorAggregate;
use Countable;

class CustomFieldMappingDtoCollection implements Countable, IteratorAggregate
{
    private $customFieldMappingDtoFactory;
    private $items = [];

    public function __construct(CustomFieldMappingDtoFactory $customFieldMappingDtoFactory)
    {
        $this->customFieldMappingDtoFactory = $customFieldMappingDtoFactory;
    }

    /**
     * @param array $data
     * @return CustomFieldMappingDtoCollection
     * @throws InvalidPrefixException
     */
    public function createFromRequestData(array $data): CustomFieldMappingDtoCollection
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

    public function add(CustomFieldMappingDto $item)
    {
        $this->items[] = $item;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    public function count()
    {
        return count($this->items);
    }
}
