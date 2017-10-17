<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AccessValidator;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\Config;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;

/**
 * Class Webformpost
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings
 */
class Webformpost extends Action
{
    protected $resultPageFactory;

    /** @var Http */
    private $request;

    /** @var Repository */
    private $repository;

    /**
     * @param Context $context
     * @param Repository $repository
     * @param AccessValidator $accessValidator
     */
    public function __construct(
        Context $context,
        Repository $repository,
        AccessValidator $accessValidator
    )
    {
        parent::__construct($context);

        if (false === $accessValidator->checkAccess()) {
            $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        }

        $this->request = $this->getRequest();
        $this->repository = $repository;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('getresponseintegration/settings/webform');

        $data = $this->request->getPostValue();
        $error = $this->validateWebformData($data);

        if (!empty($error)) {
            $this->messageManager->addErrorMessage($error);
            return $resultRedirect;
        }

        $publish = isset($data['publish']) ? $data['publish'] : 0;
        $webformId = isset($data['webform_id']) ? $data['webform_id'] : null;
        $webformUrl = isset($data['webform_url']) ? $data['webform_url'] : null;
        $sidebar = isset($data['sidebar']) ? $data['sidebar'] : null;

        $this->repository->updateWebform(
            $publish,
            $webformUrl,
            $webformId,
            $sidebar
        );

        $this->messageManager->addSuccessMessage($publish ? 'Form published' : 'Form unpublished');
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