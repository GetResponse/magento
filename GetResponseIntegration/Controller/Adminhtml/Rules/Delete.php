<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Rules;

use GetResponse\GetResponseIntegration\Helper\Config;
use Magento\Backend\App\Action;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryValidator;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Framework\App\ResponseInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Delete
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Rules
 */
class Delete extends Action
{
    private $resultPageFactory;

    const AUTOMATION_URL = 'getresponseintegration/settings/automation';

    /** @var Repository */
    private $repository;

    /** @var RepositoryValidator */
    private $repositoryValidator;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Repository $repository
     * @param RepositoryValidator $repositoryValidator
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Repository $repository,
        RepositoryValidator $repositoryValidator
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->repository = $repository;
        $this->repositoryValidator = $repositoryValidator;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     */
    public function execute()
    {
        if (!$this->repositoryValidator->validate()) {
            $this->messageManager->addErrorMessage(Config::INCORRECT_API_RESOONSE_MESSAGE);
            return $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(self::AUTOMATION_URL);

        $id = $this->getRequest()->getParam('id');

        try {
            $this->repository->deleteRule($id);
        } catch (RepositoryException $e) {
            $this->messageManager->addErrorMessage('Incorrect rule');
            return $resultRedirect;
        }

        $this->messageManager->addSuccessMessage('Rule deleted');
        return $resultRedirect;
    }
}
