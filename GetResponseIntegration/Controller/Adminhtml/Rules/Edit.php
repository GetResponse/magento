<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Rules;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AccessValidator;
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
    const AUTOMATION_URL = 'getresponseintegration/settings/automation';
    const BACK_URL = 'getresponseintegration/rules/edit';

    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param AccessValidator $accessValidator
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        AccessValidator $accessValidator
    )
    {
        parent::__construct($context);

        if (false === $accessValidator->checkAccess()) {
            $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        }

        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(self::AUTOMATION_URL);

        $id = $this->getRequest()->getParam('id');

        if (empty($id)) {
            $this->messageManager->addErrorMessage('Incorrect rule');
            return $resultRedirect;
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend('Edit rule');

        /** @var Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();

        if (empty($data)) {
            return $resultPage;
        }

        $error = RuleValidator::validateForPostedParams($data);

        if (!empty($error)) {
            $this->messageManager->addErrorMessage($error);
            $resultRedirect->setPath(self::BACK_URL);
            return $resultRedirect;
        }

        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $automation = $this->_objectManager->get('GetResponse\GetResponseIntegration\Model\Automation');

        $cycle_day = (isset($data['gr_autoresponder']) && $data['gr_autoresponder'] == 1 && isset($data['cycle_day'])) ? $data['cycle_day'] : '';

        $automation->load($id, 'id')
            ->setIdShop($storeId)
            ->setCategoryId($data['category'])
            ->setCampaignId($data['campaign_id'])
            ->setActive(1)
            ->setCycleDay($cycle_day)
            ->setAction($data['action'])
            ->save();

        $this->messageManager->addSuccessMessage('Rule has been updated');
        return $resultRedirect;
    }
}
