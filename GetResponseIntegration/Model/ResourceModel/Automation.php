<?php
namespace GetResponse\GetResponseIntegration\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Automation
 * @package GetResponse\GetResponseIntegration\Model\ResourceModel
 */
class Automation extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('getresponse_automation', 'id');
    }
}