<?php
namespace GetResponse\GetResponseIntegration\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Account
 * @package GetResponse\GetResponseIntegration\Model\ResourceModel
 */
class Account extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('getresponse_account', 'id');
    }
}