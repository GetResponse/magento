<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AccessValidator;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\Config;
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
     * @param AccessValidator $accessValidator
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Repository $repository,
        AccessValidator $accessValidator
    )
    {
        parent::__construct($context);

        if (false === $accessValidator->checkAccess()) {
            $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        }

        $this->resultPageFactory = $resultPageFactory;
        $this->repository = $repository;
    }


    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();

        $this->repository->clearSettings();
        $this->repository->clearAccount();
        $this->repository->clearWebforms();
        $this->repository->clearAutomation();

        $this->messageManager->addSuccessMessage('GetResponse account disconnected');

        $resultPage->getConfig()->getTitle()->prepend('GetResponse Account');
        return $resultPage;
    }
}