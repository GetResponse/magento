<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute;

use ArrayIterator;
use IteratorAggregate;

class MagentoCustomerAttributeCollection implements IteratorAggregate
{
    private $items = [];

    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    public function add(MagentoCustomerAttribute $item)
    {
        $this->items[] = $item;
    }

    public function toArray(): array
    {
        $result = [];

        foreach ($this->items as $item) {
            $attributeKey = $item->getAttributeType() . '_' . $item->getAttributeCode();
            $result[$attributeKey] = $item->getFrontendLabel();
        }
        asort($result);

        return $result;
    }
}