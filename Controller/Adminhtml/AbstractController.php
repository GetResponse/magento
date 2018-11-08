<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

/**
 * Class GetResponse
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml
 */
abstract class AbstractController extends Action
{
    /**
     * @param Context $context
     */
    public function __construct(Context $context) {
        parent::__construct($context);
    }
}
