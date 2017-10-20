<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Rules;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AccessValidator;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RuleFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\Config;
use Magento\Backend\App\Action;
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

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param AccessValidator $accessValidator
     * @param Repository $repository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        AccessValidator $accessValidator,
        Repository $repository
    )
    {
        parent::__construct($context);

        if (false === $accessValidator->isConnectedToGetResponse()) {
            $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        }

        $this->resultPageFactory = $resultPageFactory;
        $this->repository = $repository;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
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
