<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Rules;

use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Helper\Config;
use GetResponse\GetResponseIntegration\Helper\Message;
use Magento\Backend\App\Action;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryValidator;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RuleFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Framework\App\ResponseInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Request\Http;

/**
 * Class Edit
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Rules
 */
class Edit extends Action
{
    const PAGE_TITLE = 'Edit rule';
    const AUTOMATION_URL = 'getresponse/lists/rules';

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
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->repository = $repository;
        $this->repositoryValidator = $repositoryValidator;
    }

    /**
     * Dispatch request
     *
     * @return ResultInterface|ResponseInterface
     * @throws RepositoryException
     */
    public function execute()
    {
        if (!$this->repositoryValidator->validate()) {
            $this->messageManager->addErrorMessage(Message::INCORRECT_API_RESPONSE_MESSAGE);

            return $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        }

        $id = $this->getRequest()->getParam('id');

        if (empty($id)) {
            $this->messageManager->addErrorMessage(Message::CANNOT_EDIT_RULE);
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath(self::AUTOMATION_URL);

            return $resultRedirect;
        }

        $rule = $this->repository->getRuleById($id);

        if (empty($rule)) {
            $this->messageManager->addErrorMessage(Message::CANNOT_EDIT_RULE);
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

        $data['id'] = uniqid();
        $rule = RuleFactory::createFromArray($data);
        $this->repository->updateRule($id, $rule);

        $this->messageManager->addSuccessMessage(Message::RULE_UPDATED);
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(self::AUTOMATION_URL);

        return $resultRedirect;
    }
}
