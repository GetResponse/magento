<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Webform;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\Magento\WebformSettings;
use GetResponse\GetResponseIntegration\Helper\Message;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryValidator;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\WebformSettingsFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Save
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Webform
 */
class Save extends AbstractController
{
    const BACK_URL = 'getresponse/webform/index';
    const PAGE_TITLE = 'Add contacts via GetResponse forms';

    /** @var PageFactory */
    protected $resultPageFactory;

    /** @var Http */
    private $request;

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
        $this->request = $this->getRequest();
        $this->repository = $repository;
    }

    /**
     * @return ResponseInterface|Redirect|Page
     */
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

        $this->repository->saveWebformSettings($webForm);

        $this->messageManager->addSuccessMessage($webForm->isEnabled() ? Message::FORM_PUBLISHED : Message::FORM_UNPUBLISHED);

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(self::BACK_URL);

        return $resultRedirect;
    }

    /**
     * @param WebformSettings $webForm
     * @return string
     */
    private function validateWebFormData(WebformSettings $webForm)
    {
        if (strlen($webForm->getWebformId()) === 0 && strlen($webForm->getSidebar()) === 0) {
            return Message::SELECT_FORM_POSITION_AND_PLACEMENT;
        }

        if (strlen($webForm->getWebformId()) === 0) {
            return Message::SELECT_FORM;
        }

        if (strlen($webForm->getSidebar()) === 0) {
            return Message::SELECT_FORM_POSITION;
        }

        return '';
    }
}
