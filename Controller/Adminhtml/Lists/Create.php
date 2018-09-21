<?php

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Lists;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\GetResponse\ListValidator;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Helper\Message;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryValidator;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GrShareCode\ContactList\AddContactListCommand;
use GrShareCode\ContactList\ContactListService;
use GrShareCode\GetresponseApiClient;
use GrShareCode\GetresponseApiException;
use Magento\Framework\App\ResponseInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Request\Http;

/**
 * Class Create
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Lists
 */
class Create extends AbstractController
{
    const PAGE_TITLE = 'New Contact List';

    /** @var PageFactory */
    protected $resultPageFactory;

    /** @var Repository */
    private $repository;

    /** @var GetresponseApiClient */
    private $grApiClient;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Repository $repository
     * @param RepositoryFactory $repositoryFactory
     * @param RepositoryValidator $repositoryValidator
     * @throws RepositoryException
     * @throws \GrShareCode\Api\ApiTypeException
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Repository $repository,
        RepositoryFactory $repositoryFactory,
        RepositoryValidator $repositoryValidator
    ) {
        parent::__construct($context, $repositoryValidator);
        $this->resultPageFactory = $resultPageFactory;
        $this->repository = $repository;
        $this->grApiClient = $repositoryFactory->createGetResponseApiClient();

        return $this->checkGetResponseConnection();
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     */
    public function execute()
    {
        $backUrl = $this->getRequest()->getParam('back_url');
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);

        /** @var Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();

        if (empty($data)) {
            return $resultPage;
        }

        $error = ListValidator::validateNewListParams($data);

        if (!empty($error)) {
            $this->messageManager->addErrorMessage($error);
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);

            return $resultPage;
        }

        $data['lang'] = substr($this->repository->getMagentoCountryCode(), 0, 2);

        $service = new ContactListService($this->grApiClient);
        try {
            $service->createContactList(new AddContactListCommand(
                $data['campaign_name'],
                $data['from_field'],
                $data['reply_to_field'],
                $data['confirmation_body'],
                $data['confirmation_subject'],
                $data['lang']
            ));

            $this->messageManager->addSuccessMessage(Message::LIST_CREATED);
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath($backUrl);

            return $resultRedirect;

        } catch (GetresponseApiException $e) {
            $this->messageManager->addErrorMessage(Message::CANNOT_CREATE_LIST . ' - ' . $e->getMessage());
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);
            return $resultPage;
        }
    }
}
