<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Rules;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Helper\Message;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryValidator;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Delete
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Rules
 */
class Delete extends AbstractController
{
    /** @var PageFactory */
    private $resultPageFactory;

    const AUTOMATION_URL = 'getresponse/lists/rules';

    /** @var Repository */
    private $repository;

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
        parent::__construct($context, $repositoryValidator);
        $this->resultPageFactory = $resultPageFactory;
        $this->repository = $repository;

        return $this->checkGetResponseConnection();
    }

    /**
     * @return ResultInterface
     */

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(self::AUTOMATION_URL);

        $this->repository->deleteRule($this->getRequest()->getParam('id'));
        $this->messageManager->addSuccessMessage(Message::RULE_DELETED);
        return $resultRedirect;
    }
}
