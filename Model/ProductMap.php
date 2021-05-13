<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Model;

use GetResponse\GetResponseIntegration\Model\ResourceModel\ProductMap as ResourceProductMap;
use Magento\Framework\Model\AbstractModel;

class ProductMap extends AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(ResourceProductMap::class);
    }
}
