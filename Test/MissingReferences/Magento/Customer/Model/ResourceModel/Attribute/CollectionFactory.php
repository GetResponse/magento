<?php

declare(strict_types=1);

namespace Magento\Customer\Model\ResourceModel\Attribute;

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
