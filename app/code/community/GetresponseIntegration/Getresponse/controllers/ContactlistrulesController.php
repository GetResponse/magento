<?php
use GetresponseIntegration_Getresponse_Domain_AutomationRuleFactory as AutomationRuleFactory;
use GetresponseIntegration_Getresponse_Domain_AutomationRulesCollectionRepository as AutomationRulesCollectionRepository;
use GetresponseIntegration_Getresponse_Domain_AutomationRulesCollectionFactory as AutomationRulesCollectionFactory;

require_once Mage::getModuleDir('controllers',
        'GetresponseIntegration_Getresponse') . DIRECTORY_SEPARATOR . 'BaseController.php';

class GetresponseIntegration_Getresponse_ContactlistrulesController extends GetresponseIntegration_Getresponse_BaseController
{

    protected $actions = [
        'move' => 'Moved',
        'copy' => 'Copied'
    ];

    /**
     * GET getresponse/contactlistrules/index
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->_title($this->__('Campaign rules'))->_title($this->__('GetResponse'));

        $this->settings->campaign_days = Mage::helper('getresponse/api')->getCampaignDays();

        $ruleRepository = new AutomationRulesCollectionRepository($this->currentShopId);
        $ruleCollectionDb = $ruleRepository->getCollection();

        $this->_addContent($this->getLayout()
            ->createBlock('Mage_Core_Block_Template', 'getresponse_content')
            ->setTemplate('getresponse/automation.phtml')
            ->assign('settings', $this->settings)
            ->assign('rules', $ruleCollectionDb)
            ->assign('categories', $this->getCategories())
            ->assign('campaign_days', Mage::helper('getresponse/api')->getCampaignDays())
            ->assign('campaigns', $this->api->getGrCampaigns())
        );

        $this->renderLayout();
    }

    /**
     * GET getresponse/contactlistrules/add
     */
    public function addAction()
    {
        $this->_initAction();
        $this->_title($this->__('New Rule'))->_title($this->__('GetResponse'));

        /** @var Mage_Core_Block_Abstract $autoresponderBlock */
        $autoresponderBlock = $this->getLayout()->createBlock(
            'GetresponseIntegration_Getresponse_Block_Adminhtml_Autoresponder',
            'autoresponder',
            array(
                'campaign_days' => $this->api->getCampaignDays(),
                'selected_day' => $this->settings->api['cycle_day']
            )
        );

        $this->_addContent($this->getLayout()
            ->createBlock('Mage_Core_Block_Template', 'getresponse_content')
            ->setTemplate('getresponse/add_contact_list_rule.phtml')
            ->assign('settings', $this->settings)
            ->assign('categories_tree', $this->getTreeCategoriesHTML(1, false))
            ->assign('actions', $this->actions)
            ->assign('campaigns', $this->api->getGrCampaigns())
            ->assign('autoresponder_block', $autoresponderBlock->toHtml())
        );

        $this->renderLayout();
    }

    /**
     * GET getresponse/contactlistrules/edit/id/{id}
     */
    public function editAction()
    {
        $this->_initAction();
        $this->_title($this->__('New Rule'))->_title($this->__('GetResponse'));

        /** @var Mage_Core_Block_Abstract $autoresponderBlock */
        $autoresponderBlock = $this->getLayout()->createBlock(
            'GetresponseIntegration_Getresponse_Block_Adminhtml_Autoresponder',
            'autoresponder',
            array(
                'campaign_days' => $this->api->getCampaignDays(),
                'selected_day' => $this->settings->api['cycle_day']
            )
        );

        $id = $this->getRequest()->getParam('id');

        if (!isset($id) || empty($id)) {
            $this->_getSession()->addError('Invalid rule');
            $this->_redirect('*/*/index');
            return;
        }

        $ruleRepository = new AutomationRulesCollectionRepository($this->currentShopId);
        $ruleCollectionDb = $ruleRepository->getCollection();
        $automation = [];

        foreach ($ruleCollectionDb as $rule) {
            if ($rule['id'] === $id)
                $automation = $rule;
        }

        $this->_addContent($this->getLayout()
            ->createBlock('Mage_Core_Block_Template', 'getresponse_content')
            ->setTemplate('getresponse/edit_contact_list_rule.phtml')
            ->assign('settings', $this->settings)
            ->assign('categories_tree', $this->getTreeCategoriesHTML(1, false))
            ->assign('automation', $automation)
            ->assign('actions', $this->actions)
            ->assign('campaigns', $this->api->getGrCampaigns())
            ->assign('autoresponder_block', $autoresponderBlock->toHtml())
        );

        $this->renderLayout();
    }

    /**
     * POST getresponse/contactlistrules/save
     */
    public function saveAction()
    {
        $this->_initAction();

        $params = $this->getRequest()->getParams();

        $isAutoresponderOn = $this->getRequest()->getParam('gr_autoresponder', 0);
        $cycleDay = $this->getRequest()->getParam('cycle_day', null);
        if (null === $isAutoresponderOn) {
            $cycleDay = null;
        }

        $data = [
            'id' => substr(md5(time()), 0, 5),
            'categoryId' => $params['category_id'],
            'campaignId' => $params['campaign_id'],
            'cycleDay' => $cycleDay,
            'action' => $params['action'],
            'active' => 1
        ];

        $ruleRepository = new AutomationRulesCollectionRepository($this->currentShopId);
        $rule = AutomationRuleFactory::createFromArray($data);
        $ruleCollectionDb = $ruleRepository->getCollection();
        $ruleCollectionDb = AutomationRulesCollectionFactory::createFromArray($ruleCollectionDb);
        $status = $ruleCollectionDb->add($rule);

        if ($status) {
            $ruleRepository->create($ruleCollectionDb);
            $this->_getSession()->addSuccess('Rule added');
            $this->_redirect('*/*/index');

        } else {
            $this->_getSession()->addError('Rule has not been created. Rule already exist');
            $this->_redirectReferer();
        }
    }

    /**
     * POST getresponse/contactlistrules/update/id/{id}
     */
    public function updateAction()
    {
        $this->_initAction();

        $id = $this->getRequest()->getParam('id');

        if (empty($id)) {
            $this->_getSession()->addError('Invalid rule');
            $this->_redirect('*/*/index');
            return;
        }

        $params = $this->getRequest()->getParams();

        $isAutoresponderOn = $this->getRequest()->getParam('gr_autoresponder', 0);
        $cycleDay = $this->getRequest()->getParam('cycle_day', null);
        if (null === $isAutoresponderOn) {
            $cycleDay = null;
        }

        $data = [
            'id' => $id,
            'categoryId' => $params['category_id'],
            'campaignId' => $params['campaign_id'],
            'cycleDay' => $cycleDay,
            'action' => $params['action'],
            'active' => 1
        ];

        $ruleRepository = new AutomationRulesCollectionRepository($this->currentShopId);
        $editedRule = AutomationRuleFactory::createFromArray($data);
        $ruleCollectionDb = $ruleRepository->getCollection();

        foreach ($ruleCollectionDb as $key => $rule) {
            if ($rule['id'] === $id) {
                unset($ruleCollectionDb[$key]);
            }
        }

        $ruleCollectionDb = AutomationRulesCollectionFactory::createFromArray($ruleCollectionDb);
        $status = $ruleCollectionDb->add($editedRule);

        if ($status) {
            $ruleRepository->create($ruleCollectionDb);
            $this->_getSession()->addSuccess('Rule saved');
            $this->_redirect('*/*/index');
        } else {
            $this->_getSession()->addError('Rule not saved');
            $this->_redirectReferer();
        }
    }

    /**
     * POST getresponse/contactlistrules/delete/id/{id}
     */
    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        if (empty($id)) {
            $this->_getSession()->addError('Rule not found');
            $this->_redirect('*/*/index');
            return;
        }

        $ruleRepository = new AutomationRulesCollectionRepository($this->currentShopId);
        $ruleCollectionDb = $ruleRepository->getCollection();

        foreach ($ruleCollectionDb as $key => $rule) {
            if ($rule['id'] === $id) {
                unset($ruleCollectionDb[$key]);
            }
        }

        $ruleCollectionDb = AutomationRulesCollectionFactory::createFromArray($ruleCollectionDb);
        $ruleRepository->create($ruleCollectionDb);
        $this->_getSession()->addSuccess('Rule deleted');
        $this->_redirect('*/*/index');
    }

    /**
     * @param int $parentId
     * @param int $isChild
     * @param string $prefix
     * @param null $defaultCategory
     * @return string
     */
    protected function getTreeCategoriesHTML($parentId, $isChild, $prefix = '', $defaultCategory = null)
    {
        $options = '';
        $allCats = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('is_active', '1')
            ->addAttributeToFilter('parent_id', ['eq' => $parentId]);

        foreach ($allCats as $category) {

            $markDefault = '';

            if ($category->getId() === $defaultCategory) {
                $markDefault = ' selected="selected" ';
            }

            $prefix = ($isChild) ? $prefix . '↳' : $prefix;
            $options .= '<option ' . $markDefault . ' value="' . $category->getId() . '">' . $prefix . ' ' . $category->getName() .
                '</option>';
            $subcats = $category->getChildren();
            if ($subcats != '') {
                $options .= $this->getTreeCategoriesHTML($category->getId(), true, $prefix, $defaultCategory);
            }
        }

        return $options;
    }

    /**
     * @return array
     */
    protected function getCategories()
    {
        $results = [];
        $categories = Mage::getModel('catalog/category')
            ->getCollection()
            ->setStoreId($this->currentShopId)
            ->addFieldToFilter('is_active', 1)
            ->addAttributeToSelect('*');

        foreach ($categories as $category) {
            $catid = $category->getId();
            $data = $category->getData();
            $results[$catid] = $data;
        }

        return $results;
    }

}