<?php
namespace GetResponse\GetResponseIntegration\Model\ResourceModel\Customs;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package GetResponse\GetResponseIntegration\Model\ResourceModel\Customs
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
        $this->_init('GetResponse\GetResponseIntegration\Model\Customs', 'GetResponse\GetResponseIntegration\Model\ResourceModel\Customs');
    }
}