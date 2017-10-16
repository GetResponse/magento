<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings;

use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Model\Account as ModelAccount;
use GetResponse\GetResponseIntegration\Model\Automation as ModelAutomation;
use GetResponse\GetResponseIntegration\Model\Settings as ModelSettings;
use GetResponse\GetResponseIntegration\Model\Webform as ModelWebform;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Delete
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings
 */
class Delete extends Action
{
    /** @var Repository */
    private $repository;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Repository $repository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Repository $repository)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }


    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->repository->clearSettings();
        $this->repository->clearAccount();
        $this->repository->clearWebforms();
        $this->repository->clearAutomation();

        $this->messageManager->addSuccessMessage('GetResponse account disconnected');

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('GetResponse_GetResponseIntegration::settings');
        $resultPage->getConfig()->getTitle()->prepend('GetResponse Account');

        return $resultPage;
    }
}