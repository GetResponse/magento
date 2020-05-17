<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Lists;

use Exception;
use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\ListValidator;
use GetResponse\GetResponseIntegration\Domain\Magento\Store\ReadModel\StoreReadModel;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Helper\Message;
use GrShareCode\ContactList\Command\AddContactListCommand;
use GrShareCode\ContactList\ContactListService;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Result\PageFactory;

class Create extends AbstractController
{
    const PAGE_TITLE = 'New Contact List';

    protected $resultPageFactory;
    private $apiClientFactory;
    private $magentoStore;
    private $storeReadModel;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ApiClientFactory $apiClientFactory,
        MagentoStore $magentoStore,
        StoreReadModel $storeReadModel
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->apiClientFactory = $apiClientFactory;
        $this->magentoStore = $magentoStore;
        $this->storeReadModel = $storeReadModel;
    }

    public function execute()
    {
        try {
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
                throw new Exception($error);
            }

            $data['lang'] = $this->storeReadModel->getStoreLanguage(
                new Scope($this->magentoStore->getStoreIdFromUrl())
            );

            $apiClient = $this->apiClientFactory->createGetResponseApiClient(
                new Scope($this->magentoStore->getStoreIdFromUrl())
            );
            $service = new ContactListService($apiClient);
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
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);

            return $resultPage;
        }
    }
}
