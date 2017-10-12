<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings;

use GetResponse\GetResponseIntegration\Helper\GetResponseAPI3;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Webformpost
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings
 */
class Webformpost extends Action
{
    protected $resultPageFactory;

    /** @var GetResponseAPI3 */
    public $grApi;

    /**
     * Webformpost constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('GetResponse_GetResponseIntegration::settings');
        $resultPage->getConfig()->getTitle()->prepend('Add contacts via GetResponse forms');

        $data = $this->getRequest()->getPostValue();

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('getresponseintegration/settings/webform');

        $error = $this->validateWebformData($data);

        if (!empty($error)) {
            $this->messageManager->addErrorMessage($error);
            return $resultRedirect;
        }

        $publish = isset($data['publish']) ? $data['publish'] : 0;
        $webform_id = isset($data['webform_id']) ? $data['webform_id'] : null;
        $webform_url = isset($data['webform_url']) ? $data['webform_url'] : null;
        $sidebar = isset($data['sidebar']) ? $data['sidebar'] : null;
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $webform = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Webform');

        $webform->load($storeId, 'id_shop')
            ->setIdShop($storeId)
            ->setActiveSubscription($publish)
            ->setUrl($webform_url)
            ->setWebformId($webform_id)
            ->setSidebar($sidebar)
            ->save();

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
    }
}