<?php
namespace GetResponse\GetResponseIntegration\Model\ResourceModel\CartMap;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package GetResponse\GetResponseIntegration\Model\ResourceModel\CartMap
 */
class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'GetResponse\GetResponseIntegration\Model\CartMap',
            'GetResponse\GetResponseIntegration\Model\ResourceModel\CartMap'
        );
    }
}
