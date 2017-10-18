<?php
namespace GetResponse\GetResponseIntegration\Model\ResourceModel\Automation;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package GetResponse\GetResponseIntegration\Model\ResourceModel\Automation
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
        $this->_init('GetResponse\GetResponseIntegration\Model\Automation', 'GetResponse\GetResponseIntegration\Model\ResourceModel\Automation');
    }
}