<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Rules;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use GetResponse\GetResponseIntegration\Model\Customs as ModelCustoms;

/**
 * Class Delete
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Rules
 */
class Delete extends Action
{
    protected $resultPageFactory;
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $id = $this->getRequest()->getParam('id');

        if (empty($id)) {
            $this->messageManager->addErrorMessage('Incorrect rule');
            $resultRedirect->setPath('getresponseintegration/settings/automation');
            return $resultRedirect;
        }

        $automation = $this->_objectManager->get('GetResponse\GetResponseIntegration\Model\Automation');
        $automation->load($id, 'id')->delete();

        $this->messageManager->addSuccessMessage('Rule deleted');
        $resultRedirect->setPath('getresponseintegration/settings/automation');
        return $resultRedirect;
    }

}
