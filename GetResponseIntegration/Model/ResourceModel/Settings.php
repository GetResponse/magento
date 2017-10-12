<?php
namespace GetResponse\GetResponseIntegration\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Settings
 * @package GetResponse\GetResponseIntegration\Model\ResourceModel
 */
class Settings extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('getresponse_settings', 'id');
    }
}