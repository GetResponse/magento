<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Rules;

use GetResponse\GetResponseIntegration\Helper\Config;
use Magento\Backend\App\Action;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryValidator;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RuleFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Framework\App\ResponseInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Request\Http;

/**
 * Class Edit
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Rules
 */
class Edit extends Action
{
    const PAGE_TITLE = 'Edit rule';
    const AUTOMATION_URL = 'getresponseintegration/settings/automation';

    /** @var PageFactory */
    protected $resultPageFactory;

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
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        if (!$this->repositoryValidator->validate()) {
            $this->messageManager->addErrorMessage(Config::INCORRECT_API_RESOONSE_MESSAGE);
            return $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        }

        $id = $this->getRequest()->getParam('id');

        if (empty($id)) {
            $this->messageManager->addErrorMessage('Incorrect rule');
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath(self::AUTOMATION_URL);
            return $resultRedirect;
        }

        $rule = $this->repository->getRuleById($id);

        if (empty($rule)) {
            $this->messageManager->addErrorMessage('Incorrect rule');
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath(self::AUTOMATION_URL);
            return $resultRedirect;
        }

        /** @var Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();

        if (empty($data)) {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);
            return $resultPage;
        }

        $error = RuleValidator::validateForPostedParams($data);

        if (!empty($error)) {
            $this->messageManager->addErrorMessage($error);
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);
            return $resultPage;
        }

        $rule = RuleFactory::buildFromPayload($data);
        $this->repository->updateRule($id, $rule);

        $this->messageManager->addSuccessMessage('Rule has been updated');
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(self::AUTOMATION_URL);
        return $resultRedirect;
    }
}
