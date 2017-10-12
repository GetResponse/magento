<?php
namespace GetResponse\GetResponseIntegration\Model\ResourceModel\Account;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package GetResponse\GetResponseIntegration\Model\ResourceModel\Account
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
        $this->_init('GetResponse\GetResponseIntegration\Model\Account', 'GetResponse\GetResponseIntegration\Model\ResourceModel\Account');
    }
}