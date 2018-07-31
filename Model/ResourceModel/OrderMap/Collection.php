<?php
namespace GetResponse\GetResponseIntegration\Model\ResourceModel\OrderMap;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package GetResponse\GetResponseIntegration\Model\ResourceModel\OrderMap
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
            'GetResponse\GetResponseIntegration\Model\OrderMap',
            'GetResponse\GetResponseIntegration\Model\ResourceModel\OrderMap'
        );
    }
}
