<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Lists;

use Exception;
use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Lists\AddContactListCommandFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\ListValidator;
use GetResponse\GetResponseIntegration\Domain\Magento\Store\ReadModel\StoreReadModel;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Exception\ListValidationException;
use GetResponse\GetResponseIntegration\Helper\Message;
use GrShareCode\ContactList\ContactListService;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;

class Create extends AbstractController
{
    private $apiClientFactory;
    private $storeReadModel;

    public function __construct(
        Context $context,
        ApiClientFactory $apiClientFactory,
        StoreReadModel $storeReadModel
    ) {
        parent::__construct($context);
        $this->storeReadModel = $storeReadModel;
        $this->apiClientFactory = $apiClientFactory;
    }

    public function execute()
    {
        parent::execute();

        $backUrl = $this->getRequest()->getParam('back_url');

        try {
            /** @var Http $request */
            $data = $this->request->getPostValue();

            if (empty($data)) {
                return $this->redirect($backUrl);
            }

            $error = ListValidator::validateNewListParams($data);

            if (!empty($error)) {
                throw new ListValidationException($error);
            }

            $data['lang'] = $this->storeReadModel->getStoreLanguage($this->scope);

            $service = new ContactListService(
                $this->apiClientFactory->createGetResponseApiClient($this->scope)
            );

            $service->createContactList(AddContactListCommandFactory::createFromArray($data));

            return $this->redirect($backUrl, Message::LIST_CREATED);
        } catch (Exception $e) {
            return $this->redirect($backUrl, $e->getMessage(), true);
        }
    }
}
