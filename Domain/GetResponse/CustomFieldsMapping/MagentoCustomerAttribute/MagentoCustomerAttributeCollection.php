<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute;

use ArrayIterator;
use IteratorAggregate;

/**
 * Class MagentoCustomerAttributeCollection
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute
 */
class MagentoCustomerAttributeCollection implements IteratorAggregate
{
    /** @var array */
    private $items = [];

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @param MagentoCustomerAttribute $item
     */
    public function add(MagentoCustomerAttribute $item)
    {
        $this->items[] = $item;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = [];

        /** @var MagentoCustomerAttribute $item */
        foreach ($this->items as $item) {
            $attributeKey = $item->getAttributeType() . '_' . $item->getAttributeCode();
            $result[$attributeKey] = $item->getFrontendLabel();
        }
        asort($result);

        return $result;
    }
}