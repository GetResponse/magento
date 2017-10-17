<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AccessValidator;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\Config;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Request\Http;

/**
 * Class RegistrationPost
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings
 */
class RegistrationPost extends Action
{

    const INVALID_CUSTOM_FIELD_MESSAGE = 'There is a problem with one of your custom field name! Field name must be composed using up to 32 characters, only a-z (lower case), numbers and "_".';

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
    )
    {
        parent::__construct($context);

        if (false === $accessValidator->checkAccess()) {
            $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        }

        $this->resultPageFactory = $resultPageFactory;
        $this->request = $this->getRequest();
        $this->repository = $repository;
    }

    /**
     * @return Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend('Add Contacts During Registrations');

        $data = $this->request->getPostValue();

        if (empty($data)) {
            return $resultPage;
        }

        $updateCustomFields = (isset($data['gr_sync_order_data'])) ? $data['gr_sync_order_data'] : 0;
        $cycleDay = (isset($data['gr_autoresponder']) && $data['gr_autoresponder'] == 1) ? $data['cycle_day'] : '';
        $isEnabled = isset($data['gr_enabled']) && 1 == $data['gr_enabled'] ? true : false;

        if (!$isEnabled) {
            $this->repository->updateSettings(null, 0, 0, 0);
        } else {

            $campaignId = $data['campaign_id'];

            if (empty($campaignId)) {
                $this->messageManager->addErrorMessage('You need to select contact list');
                return $resultPage;
            }

            if ($updateCustomFields) {
                $customs = CustomsFactory::buildFromFormPayload($data);

                foreach ($customs as $field => $name) {
                    if (false == preg_match('/^[_a-zA-Z0-9]{2,32}$/m', $name)) {
                        $this->messageManager->addErrorMessage(self::INVALID_CUSTOM_FIELD_MESSAGE);
                        return $resultPage;
                    }
                }
                $this->updateCustoms($customs);
            }

            $this->repository->updateSettings(
                $campaignId,
                $isEnabled,
                $updateCustomFields,
                $cycleDay
            );
        }

        $this->messageManager->addSuccessMessage('Settings saved');
        return $resultPage;
    }

    /**
     * @param $customs
     */
    public function updateCustoms($customs)
    {
        $allCustoms = $this->repository->getCustomFields();

        foreach ($allCustoms as $custom) {
            if (isset($customs[$custom['custom_field']])) {
                $this->repository->updateCustomField($custom['id'], $custom['custom_name'], 1);
            } else {
                $this->repository->updateCustomField($custom['id'], $custom['custom_name'], 0);
            }
        }
    }
}