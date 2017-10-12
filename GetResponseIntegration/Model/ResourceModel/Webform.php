<?php
namespace GetResponse\GetResponseIntegration\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Webform
 * @package GetResponse\GetResponseIntegration\Model\ResourceModel
 */
class Webform extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('getresponse_webform', 'id');
    }
}