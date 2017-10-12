<?php
namespace GetResponse\GetResponseIntegration\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Customs
 * @package GetResponse\GetResponseIntegration\Model\ResourceModel
 */
class Customs extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('getresponse_customs', 'id');
    }
}