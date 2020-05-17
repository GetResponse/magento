<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Webform;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\WebformSettings;
use GetResponse\GetResponseIntegration\Domain\Magento\WebformSettingsFactory;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Helper\Message;
use GetResponse\GetResponseIntegration\Helper\Route;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\PageCache\Model\Cache\Type;

class Save extends AbstractController
{
    const PAGE_TITLE = 'Add contacts via GetResponse forms';

    private $resultPageFactory;
    private $repository;
    private $cacheTypeList;
    private $magentoStore;

    public function __construct(
        Context $context,
        TypeListInterface $cacheTypeList,
        PageFactory $resultPageFactory,
        Repository $repository,
        MagentoStore $magentoStore
    ) {
        parent::__construct($context);
        $this->cacheTypeList = $cacheTypeList;
        $this->resultPageFactory = $resultPageFactory;
        $this->request = $this->getRequest();
        $this->repository = $repository;
        $this->magentoStore = $magentoStore;
    }

    public function execute()
    {
        $webForm = WebformSettingsFactory::createFromArray($this->request->getPostValue());

        if ($webForm->isEnabled()) {
            $error = $this->validateWebFormData($webForm);

            if (!empty($error)) {
                $this->messageManager->addErrorMessage($error);
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);

                return $resultPage;
            }
        }

        $this->repository->saveWebformSettings(
            $webForm,
            $this->magentoStore->getStoreIdFromUrl()
        );

        $this->cacheTypeList->cleanType(Type::TYPE_IDENTIFIER);
        $message = $webForm->isEnabled() ? Message::FORM_PUBLISHED : Message::FORM_UNPUBLISHED;
        $this->messageManager->addSuccessMessage($message);

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(Route::WEBFORM_INDEX_ROUTE);

        return $resultRedirect;
    }

    private function validateWebFormData(WebformSettings $webForm): string
    {
        if ($webForm->getWebformId() === '' && $webForm->getSidebar() === '') {
            return Message::SELECT_FORM_POSITION_AND_PLACEMENT;
        }

        if ($webForm->getWebformId() === '') {
            return Message::SELECT_FORM;
        }

        if ($webForm->getSidebar() === '') {
            return Message::SELECT_FORM_POSITION;
        }

        return '';
    }
}
