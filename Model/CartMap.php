<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Model;

use Magento\Framework\Model\AbstractModel;
use GetResponse\GetResponseIntegration\Model\ResourceModel\CartMap as ResourceCartMap;

class CartMap extends AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(ResourceCartMap::class);
    }
}
