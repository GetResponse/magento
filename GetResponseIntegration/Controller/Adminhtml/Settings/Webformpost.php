<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AccessValidator;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\WebformSettingsFactory;
use GetResponse\GetResponseIntegration\Helper\Config;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Webformpost
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings
 */
class Webformpost extends Action
{
    const BACK_URL = 'getresponseintegration/settings/webform';

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
     * @param AccessValidator $accessValidator
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Repository $repository,
        AccessValidator $accessValidator
    ) {
        parent::__construct($context);

        if (false === $accessValidator->isConnectedToGetResponse()) {
            $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        }

        $this->resultPageFactory = $resultPageFactory;
        $this->request = $this->getRequest();
        $this->repository = $repository;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect|Page
     */
    public function execute()
    {
        $data = $this->request->getPostValue();

        if (empty($data)) {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);

            return $resultPage;
        }

        $error = $this->validateWebformData($data);

        if (!empty($error)) {
            $this->messageManager->addErrorMessage($error);
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);

            return $resultPage;
        }

        $webform = WebformSettingsFactory::createFromArray($data);

        $this->repository->saveWebformSettings($webform);

        $this->messageManager->addSuccessMessage($webform->isEnabled() ? 'Form published' : 'Form unpublished');

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(self::BACK_URL);

        return $resultRedirect;
    }

    /**
     * @param array $data
     *
     * @return string
     */
    private function validateWebformData($data)
    {
        $webformId = isset($data['webform_id']) ? $data['webform_id'] : '';
        $position = isset($data['sidebar']) ? $data['sidebar'] : '';

        if (strlen($webformId) === 0 && strlen($position) === 0) {
            return 'You need to select a form and its placement';
        }

        if (strlen($webformId) === 0) {
            return 'You need to select form';
        }

        if (strlen($position) === 0) {
            return 'You need to select positioning of the form';
        }

        return '';
    }
}