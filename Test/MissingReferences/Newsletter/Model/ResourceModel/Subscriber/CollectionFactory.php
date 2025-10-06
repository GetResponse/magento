<?php
declare(strict_types=1);

namespace Magento\Newsletter\Model\ResourceModel\Subscriber;

class CollectionFactory
{
    /**
     * @param array $data
     * @return Collection
     */
    public function create(array $data = [])
    {
        return new Collection();
    }
}