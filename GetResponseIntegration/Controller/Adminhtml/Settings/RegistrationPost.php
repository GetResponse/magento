<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomsFactory;
use GetResponse\GetResponseIntegration\Model\Customs as ModelCustoms;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class RegistrationPost
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings
 */
class RegistrationPost extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * RegistrationPost constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        if (!empty($data)) {

            $update = (isset($data['gr_sync_order_data'])) ? $data['gr_sync_order_data'] : 0;
            $cycle_day = (isset($data['gr_autoresponder']) && $data['gr_autoresponder'] == 1) ? $data['cycle_day'] : '';
            $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
            $settings = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Settings');

            if (!isset($data['gr_enabled']) || 1 != $data['gr_enabled']) {
                $settings->load($storeId, 'id_shop')
                    ->setCampaignId(null)
                    ->setActiveSubscription(0)
                    ->setUpdate(0)
                    ->setCycleDay(0)
                    ->save();
            } else {
                if (isset($data['gr_sync_order_data'])) {
                    $customs = CustomsFactory::buildFromFormPayload($data);

                    foreach ($customs as $field => $name) {
                        if (false == preg_match('/^[_a-zA-Z0-9]{2,32}$/m', $name)) {
                            $this->messageManager->addErrorMessage('There is a problem with one of your custom field name! Field name
                        must be composed using up to 32 characters, only a-z (lower case), numbers and "_".');
                            $resultPage = $this->resultPageFactory->create();
                            $resultPage->setActiveMenu('GetResponse_GetResponseIntegration::settings');
                            $resultPage->getConfig()->getTitle()->prepend('Add Contacts During Registrations');

                            return $resultPage;
                        }
                    }
                }
                $campaign_id = $data['campaign_id'];
                if (empty($campaign_id)) {
                    $this->messageManager->addErrorMessage('You need to select contact list');
                    $resultPage = $this->resultPageFactory->create();
                    $resultPage->setActiveMenu('GetResponse_GetResponseIntegration::settings');
                    $resultPage->getConfig()->getTitle()->prepend('Add Contacts During Registrations');

                    return $resultPage;
                }
                $settings->load($storeId, 'id_shop')
                    ->setCampaignId($campaign_id)
                    ->setActiveSubscription($data['gr_enabled'])
                    ->setUpdate($update)
                    ->setCycleDay($cycle_day)
                    ->save();

                if ($update == 1) {
                    $this->updateCustoms($customs);
                }
            }

            $this->messageManager->addSuccessMessage('Settings saved');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('GetResponse_GetResponseIntegration::settings');
        $resultPage->getConfig()->getTitle()->prepend('Add Contacts During Registrations');

        return $resultPage;
    }

    /**
     * @param $customs
     */
    public function updateCustoms($customs)
    {
        if (is_array($customs)) {
            /** @var ModelCustoms $model */
            $model = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Customs');
            $all_customs = $model->getCollection()->addFieldToFilter('default', false);
            foreach ($all_customs as $custom) {
                if (isset($customs[$custom->getCustomField()])) {
                    $custom->setCustomName($customs[$custom->getCustomField()])->setActiveCustom(1)->save();
                } else {
                    $custom->setActiveCustom('0')->save();
                }
            }
        }
    }
}