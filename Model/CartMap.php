<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Model;

use GetResponse\GetResponseIntegration\Model\ResourceModel\CartMap as ResourceCartMap;
use Magento\Framework\Model\AbstractModel;

class CartMap extends AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(ResourceCartMap::class);
    }
}
