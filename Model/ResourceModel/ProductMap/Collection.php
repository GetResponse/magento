<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Model\ResourceModel\ProductMap;

use GetResponse\GetResponseIntegration\Model\ResourceModel\ProductMap as ResourceProductMap;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use GetResponse\GetResponseIntegration\Model\ProductMap;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            ProductMap::class,
            ResourceProductMap::class
        );
    }
}
