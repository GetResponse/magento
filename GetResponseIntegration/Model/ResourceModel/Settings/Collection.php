<?php
namespace GetResponse\GetResponseIntegration\Model\ResourceModel\Settings;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package GetResponse\GetResponseIntegration\Model\ResourceModel\Settings
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
        $this->_init('GetResponse\GetResponseIntegration\Model\Settings', 'GetResponse\GetResponseIntegration\Model\ResourceModel\Settings');
    }
}