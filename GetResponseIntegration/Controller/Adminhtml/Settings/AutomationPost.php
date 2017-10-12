<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings;

use GetResponse\GetResponseIntegration\Model\Automation as ModelAutomation;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class AutomationPost
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings
 */
class AutomationPost extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var int
     */
    private $storeId;

    /**
     * @var ModelAutomation
     */
    private $automation;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $this->automation = $this->_objectManager->get('GetResponse\GetResponseIntegration\Model\Automation');
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        if (empty($data)) {
            echo json_encode(['success' => 'false', 'msg' => 'Oops something went wrong!']);
            die;
        }

        // Toggling status
        if (isset($data['toggle_status'])) {
            $automation_id = (empty($data['automation_id'])) ? '' : $data['automation_id'];
            $status = ($data['toggle_status'] == 'true') ? 1 : 0;
            $this->automation->load($automation_id)
                ->setActive($status)
                ->save();

            $automation_status = $this->automation->load($automation_id)->getActive();

            if ($automation_status == $status) {
                echo json_encode(['success' => 'true', 'msg' => 'Status successfully changed!']);
            } else {
                echo json_encode(['success' => 'false', 'msg' => 'Something went wrong!']);
            }
            die;
        }

        // Deleting automation
        if (isset($data['delete_automation']) && 'true' == $data['delete_automation']) {
            $automation_id = $data['automation_id'];
            $collection = $this->automation->getCollection()->addFieldToFilter('id_shop', $this->storeId);

            $this->automation->load($automation_id)->delete();
            echo json_encode(['success' => 'true', 'msg' => 'Automation successfully deleted!', 'total' => count($collection->load()->getItems())]);
            die;
        }

        $campaign_id = (empty($data['campaign_id'])) ? '' : $data['campaign_id'];
        $category_id = (empty($data['category'])) ? '' : $data['category'];
        $action = (empty($data['action'])) ? '' : $data['action'];
        $cycle_day = (isset($data['gr_autoresponder']) && 'true' == $data['gr_autoresponder'] && isset($data['cycle_day']) && $data['cycle_day'] != '') ? (int)$data['cycle_day'] : '';

        //editing
        if (isset($data['edit_automation']) && 'true' == $data['edit_automation']) {
            $automation_id = (empty($data['automation_id'])) ? '' : $data['automation_id'];

            $this->automation->load($automation_id)
                ->setCategoryId($category_id)
                ->setCampaignId($campaign_id)
                ->setCycleDay($cycle_day)
                ->setAction($action)
                ->save();

            $data['id'] = $automation_id;
            $data['cycle_day'] = !empty($cycle_day) ? $cycle_day : 'Not set';

            echo json_encode(['success' => 'true', 'msg' => 'Campaign rules have been changed.', 'data' => $data]);
            die;
        }

        if (empty($campaign_id) || empty($category_id)) {
            echo json_encode(['success' => 'false', 'msg' => 'You need to choose a campaign and category!']);
            die;
        }

        $automations_count = $this->automation->getCollection()
            ->addFieldToFilter('id_shop', $this->storeId)
            ->addFieldToFilter('category_id', $category_id);

        if (count($automations_count) > 0) {
            echo json_encode(['success' => 'false', 'msg' => 'Automation has not been created. Rule for chosen category already exist.']);
            die;
        }

        $this->automation->setIdShop($this->storeId)
            ->setCategoryId($category_id)
            ->setCampaignId($campaign_id)
            ->setActive(1)
            ->setCycleDay($cycle_day)
            ->setAction($action)
            ->save();

        $data['id'] = $this->automation->getId();
        $data['cycle_day'] = !empty($cycle_day) ? $cycle_day : 'Not set';

        echo json_encode(['success' => 'true', 'msg' => 'New automation rule has been created!', 'data' => $data]);
        die;
    }
}