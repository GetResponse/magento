<?php

/**
 * Class GetresponseIntegration_Getresponse_IndexController
 */
class GetresponseIntegration_Getresponse_IndexController extends Mage_Adminhtml_Controller_Action
{
	public $grapi;
	public $current_shop_id;
	public $settings;
	public $active_tab;
	public $disconnected = false;

	public $layout_positions = array(
			'top.menu' => 'Navigation Bar',
			'after_body_start' => 'Page Top',
			'left' => 'Left Column',
			'right' => 'Right Column',
			'content' => 'Content',
			'before_body_end' => 'Page Bottom',
			'footer' => 'Footer'
	);

	public $block_positions = array(
			'after' => 'Bottom',
			'before' => 'Top',
	);

	public $actions = array(
			'move' => 'Moved',
			'copy' => 'Copied'
	);

	public $automation_statuses = array(
			'1' => 'Enabled',
			'0' => 'Disabled'
	);

	/**
	 * construct
	 */
	protected function _construct()
	{
		$this->current_shop_id = Mage::helper('getresponse')->getStoreId();
		$this->settings = new stdClass();
	}

    protected function _isAllowed()
    {
        return true;
    }

	/**
	 * Getresponse API instance
	 */
	public static function grapi()
	{
		return GetresponseIntegration_Getresponse_Helper_Api::instance();
	}

	/**
	 * Main init action, et layout and template
	 *
	 * @return $this
	 */
	protected function _initAction()
	{
		$this->settingsHandler();

		$this->loadLayout()->_setActiveMenu('getresponse_menu/settings_page');

		if ($this->active_tab != 'index' && empty($this->settings->api['api_key'])) {
			Mage::getSingleton('core/session')
					->addError('Access denied - module is not connected to GetResponse Account.');
			$this->_redirect('getresponse/index/index');
		}

		return $this;
	}

	/**
	 * Main extenction settings
	 */
	protected function settingsHandler()
	{
		$this->settings->main['api_url_360_com'] = 'https://api3.getresponse360.com/v3';
		$this->settings->main['api_url_360_pl'] = 'https://api3.getresponse360.pl/v3';

		$this->settings->api = Mage::getModel('getresponse/settings')->load($this->current_shop_id)->getData();
		Mage::helper('getresponse/api')->setApiDetails(
				$this->settings->api['api_key'],
				$this->settings->api['api_url'],
				$this->settings->api['api_domain']
		);

		if (!empty($this->settings->api['api_key'])) {
		    $this->settings->api['encrypted_api_key'] =  str_repeat("*", strlen($this->settings->api['api_key']) - 6) . substr($this->settings->api['api_key'], -6);
        }

		$this->settings->account = Mage::getModel('getresponse/account')->load($this->current_shop_id)->getData();
		$this->settings->customs = Mage::getModel('getresponse/customs')->getCustoms($this->current_shop_id);
		$this->settings->webforms_settings =
				Mage::getModel('getresponse/webforms')->load($this->current_shop_id)->getData();
		$this->settings->campaigns = Mage::helper('getresponse/api')->getGrCampaigns();
	}

    /**
     * GET getresponse/index/webtraffic
     */
    public function webtrafficAction()
    {
        $this->settings->api = Mage::getModel('getresponse/settings')->load($this->current_shop_id)->getData();

        $this->_title($this->__('Web Traffic Tracking'))
            ->_title($this->__('GetResponse'));

        $this->active_tab = 'webtraffic';

        $this->_initAction();

        $this->_addContent($this->getLayout()
            ->createBlock('Mage_Core_Block_Template', 'getresponse_content')
            ->setTemplate('getresponse/webtraffic.phtml')
            ->assign('settings', $this->settings)
        );

        $this->renderLayout();
    }

    /**
     * POST getresponse/index/activate_webtraffic
     */
    public function activate_webtrafficAction()
    {
        $this->_initAction();
        $params = $this->getRequest()->getParams();
        $has_active_traffic_module = (int)$this->getRequest()->getParam('has_active_traffic_module', 0);

        Mage::getModel('getresponse/settings')->updateSettings(
            array('has_active_traffic_module' => $has_active_traffic_module),
            $this->current_shop_id
        );

        $message = $has_active_traffic_module == 0 ? 'Web event traffic tracking disabled' : 'Web event traffic tracking enabled';

        Mage::getSingleton('core/session')->addSuccess($message);
        $this->_redirect('getresponse/index/webtraffic');
    }

    /**
     * GET getresponse/index/add_contact_list_rule
     */
    public function add_contact_list_ruleAction()
    {
        $this->_title($this->__('New Rule'))
            ->_title($this->__('GetResponse'));

        $this->_initAction();
        $this->disableIntegrationIfApiNotActive();

        $this->settings->categories_tree = $this->getTreeCategoriesHTML(1, false);
        $this->settings->actions = $this->actions;
        $this->settings->automation_statuses = $this->automation_statuses;
        $this->settings->categories = $this->getCategories();
        $this->settings->campaign_days = Mage::helper('getresponse/api')->getCampaignDays();
        $this->settings->automations =
            Mage::getModel('getresponse/automations')->getAutomations($this->current_shop_id);

        $this->_addContent($this->getLayout()
            ->createBlock('Mage_Core_Block_Template', 'getresponse_content')
            ->setTemplate('getresponse/add_contact_list_rule.phtml')
            ->assign('settings', $this->settings)
        );

        $this->renderLayout();
    }

    /**
     * GET getresponse/index/edit_contact_list_rule/id/{id}
     */
    public function edit_contact_list_ruleAction()
    {
        $id = $this->getRequest()->getParam('id');

        if (!isset($id) || empty($id)) {
            Mage::getSingleton('core/session')->addError('Invalid rule');
            $this->_redirect('getresponse/index/automation');
            return;
        }

        $this->_title($this->__('Edit Rule'))
            ->_title($this->__('GetResponse'));

        $this->_initAction();
        $this->disableIntegrationIfApiNotActive();

        $automation = Mage::getModel('getresponse/automations')->getAutomation($id);
        $automation = reset($automation);

        $this->settings->categories_tree = $this->getTreeCategoriesHTML(1, false, '', $automation['category_id']);
        $this->settings->actions = $this->actions;
        $this->settings->automation_statuses = $this->automation_statuses;
        $this->settings->categories = $this->getCategories();
        $this->settings->campaign_days = Mage::helper('getresponse/api')->getCampaignDays();

        $this->_addContent($this->getLayout()
            ->createBlock('Mage_Core_Block_Template', 'getresponse_content')
            ->setTemplate('getresponse/edit_contact_list_rule.phtml')
            ->assign('settings', $this->settings)
            ->assign('automation', $automation)
        );

        $this->renderLayout();
    }

    /**
     * POST getresponse/index/create_contact_list_rule
     */
    public function create_contact_list_ruleAction()
    {
        $this->_initAction();
        Mage::getSingleton('core/session')->setExportPost(filter_input_array(INPUT_POST));

        $params = $this->getRequest()->getParams();

        if (empty($params['category_id']) || empty($params['action']) || empty($params['campaign_id'])) {
            Mage::getSingleton('core/session')->addError('The list name you entered already exists. Please enter a different name.');
            $this->_redirect('getresponse/index/add_contact_list_rule');
            return;
        }

        $cycle_day = $this->getRequest()->getParam('cycle_day', '');

        $add = Mage::getModel('getresponse/automations')->createAutomation(array(
            'id_shop' => $this->current_shop_id,
            'category_id' => $params['category_id'],
            'campaign_id' => $params['campaign_id'],
            'cycle_day' => $cycle_day,
            'action' => $params['action']
        ));

        if ($add) {
            Mage::getSingleton('core/session')->addSuccess('Rule added');
            $this->_redirect('getresponse/index/automation');
            return;
        }
        else {
            Mage::getSingleton('core/session')->addError('Rule has not been created. Rule already exist');
            $this->_redirect('getresponse/index/add_contact_list_rule');
            return;
        }
    }

    /**
     * POST getresponse/index/update_contact_list_rule
     */
    public function update_contact_list_ruleAction()
    {
        $this->_initAction();
        Mage::getSingleton('core/session')->setExportPost(filter_input_array(INPUT_POST));

        $id = $this->getRequest()->getParam('id');

        if (empty($id)) {
            Mage::getSingleton('core/session')->addError('Invalid rule');
            $this->_redirect('getresponse/index/automation');
            return;
        }

        $params = $this->getRequest()->getParams();

        $params['active'] = isset($params['gr_autoresponder']) && 1 === (int) $params['gr_autoresponder'] ? 1 : 0;

        if (empty($params['category_id']) || empty($params['action']) || empty($params['campaign_id'])) {
            Mage::getSingleton('core/session')->addError('The campaign name you entered already exists. Please enter a different name.');
            $this->_redirect('getresponse/index/update_contact_list_rule/id/' . $id);
            return;
        }

        $cycle_day = $this->getRequest()->getParam('cycle_day', '');

        $add = Mage::getModel('getresponse/automations')->updateAutomation($id, array(
            'id_shop' => $this->current_shop_id,
            'category_id' => $params['category_id'],
            'campaign_id' => $params['campaign_id'],
            'cycle_day' => $cycle_day,
            'action' => $params['action'],
            'active' => $params['active']
        ));

        if ($add) {
            Mage::getSingleton('core/session')->addSuccess('Rule saved');
            $this->_redirect('getresponse/index/automation');
            return;
        }
        else {
            Mage::getSingleton('core/session')->addError('Rule not saved');
            $this->_redirect('getresponse/index/edit_contact_list_rule/id/' . $id);
            return;
        }
    }

    /**
     * GET getresponse/index/newsletter
     */
    public function newsletterAction()
    {
        $this->settings->api = Mage::getModel('getresponse/settings')->load($this->current_shop_id)->getData();

        $this->_title($this->__('Subscription via newsletter'))
            ->_title($this->__('GetResponse'));

        $this->active_tab = 'newsletter';

        $this->_initAction();
        $this->disableIntegrationIfApiNotActive();

        $this->settings->campaign_days = Mage::helper('getresponse/api')->getCampaignDays();
        $this->setNewCampaignSettings();

        $this->_addContent($this->getLayout()
            ->createBlock('Mage_Core_Block_Template', 'getresponse_content')
            ->setTemplate('getresponse/newsletter.phtml')
            ->assign('settings', $this->settings)
        );

        $this->renderLayout();
    }

    /**
     * POST getresponse/index/activate_newsletter
     */
    public function activate_newsletterAction()
    {
        $this->_initAction();

        $newsletter_subscription = (int)$this->getRequest()->getParam('newsletter_subscription', 0);
        $newsletter_campaign_id = $this->getRequest()->getParam('newsletter_campaign_id', '');
        $newsletter_cycle_day = (int)$this->getRequest()->getParam('newsletter_cycle_day', 0);
        $newsletter_autoresponder = (int)$this->getRequest()->getParam('newsletter_autoresponder', 0);

        if (0 === $newsletter_subscription) {
            $newsletter_campaign_id = '';
            $newsletter_cycle_day = 0;
        } else {
            if (0 === $newsletter_autoresponder) {
                $newsletter_cycle_day = 0;
            }
        }

        Mage::getModel('getresponse/settings')->updateSettings(
            array(
                'newsletter_subscription' => $newsletter_subscription,
                'newsletter_campaign_id' => $newsletter_campaign_id,
                'newsletter_cycle_day' => $newsletter_cycle_day,
            ),
            $this->current_shop_id
        );

        Mage::getSingleton('core/session')->addSuccess('Settings saved');
        $this->_redirect('getresponse/index/newsletter');
    }

	/**
	 * GET getresponse/index/index
	 */
	public function indexAction()
	{
		$this->_title($this->__('API Key settings'))
				->_title($this->__('GetResponse'));

		$this->active_tab = 'index';

		$this->_initAction();
		$site = ( !empty($this->settings->api['api_key']) && $this->disconnected === false) ? 'account' : 'apikey';

		$this->_addContent($this->getLayout()
				->createBlock('Mage_Core_Block_Template', 'getresponse_content')
				->setTemplate('getresponse/' . $site . '.phtml')
				->assign('settings', $this->settings)
		);

		$this->renderLayout();
	}

	/**
	 * POST getresponse/index/apikey
	 */
	public function apikeyAction()
	{
		$api_key = $this->getRequest()->getParam('api_key');

		if ( !$api_key) {
			Mage::getSingleton('core/session')->addError('You need to enter API key. This field can\'t be empty');
			$this->_forward('index');

			return;
		}

		$getresponse_360_account = $this->getRequest()->getParam('getresponse_360_account');
		$api_url = ($getresponse_360_account) ? $this->getRequest()->getParam('api_url') : null;
		$api_domain = ($getresponse_360_account) ? $this->getRequest()->getParam('api_domain') : null;

		$this->grapi()->api_key = $api_key;
		$status = $this->grapi()->check_api($api_url, $api_domain);

		$status_array = (array)$status;
		if (empty($status_array) && !empty($api_domain)) {
			Mage::getSingleton('core/session')->addError('Invalid domain.');
			$this->_forward('index');

			return;
		}
		elseif ( !empty($status->codeDescription)) {
			Mage::getSingleton('core/session')->addError('The API key seems incorrect. Please check if you typed or pasted it correctly. If you recently generated a new key, please make sure you’re using the right one.');
			$this->_forward('index');

			return;
		}
		elseif ( !empty($status->accountId)) {
			if (false === Mage::getModel('getresponse/account')->updateAccount($status, $this->current_shop_id)) {
				Mage::getSingleton('core/session')->addError('Error during account details save.');
			}
		}
		else {
			Mage::getSingleton('core/session')->addError('Error - please try again.');
			$this->_forward('index');

			return;
		}

		Mage::register('api_key', $api_key);
        Mage::getModel('getresponse/customs')->connectCustoms($this->current_shop_id);
		Mage::getSingleton('core/session')->addSuccess('GetResponse account connected');

        $featureTracking = 0;
        $features = $this->grapi()->get_features();

        if ($features instanceof stdClass && $features->feature_tracking == 1) {
            $featureTracking = 1;
        }

		$data = array(
		    'id_shop' => $this->current_shop_id,
            'api_key' => $api_key,
            'api_url' => $api_url,
            'api_domain' => $api_domain,
            'has_gr_traffic_feature_enabled' => $featureTracking
		);

        // getting tracking code
        $trackingCode = (array) $this->grapi()->get_tracking_code();

        if (!empty($trackingCode) && is_object($trackingCode[0]) && 0 < strlen($trackingCode[0]->snippet)) {
            $data['tracking_code_snippet'] = $trackingCode[0]->snippet;
        }

		if (false === Mage::getModel('getresponse/settings')->updateSettings($data, $this->current_shop_id)) {
			Mage::getSingleton('core/session')->addError('Error during settings details save.');
		}

		$this->_redirect('getresponse/index/index');
	}

	/**
	 * GET getresponse/index/export
	 */
	public function exportAction()
	{
		$postData = Mage::getSingleton('core/session')->getExportPost();
		$postData = is_array($postData) ? $postData : [];
		Mage::getSingleton('core/session')->unsExportPost();

		$inputValues['gr_sync_order_data'] = false;

		if (false === empty($postData)) {
			if (isset($postData['gr_sync_order_data'])) {
				$inputValues = $postData['gr_sync_order_data'];
			}
		}

		$this->settings->inputValues = $inputValues;

		$this->_title($this->__('Export customers'))
				->_title($this->__('GetResponse'));

		$this->active_tab = 'export';

		$this->_initAction();
		$this->disableIntegrationIfApiNotActive();

		$this->settings->campaign_days = Mage::helper('getresponse/api')->getCampaignDays();

		$this->_prepare_customs_for_mapping();

		$this->_addContent($this->getLayout()
				->createBlock('Mage_Core_Block_Template', 'getresponse_content')
				->setTemplate('getresponse/export.phtml')
				->assign('settings', $this->settings)
		);

		$this->renderLayout();
	}

    /**
     * POST getresponse/index/exported
     */
    public function exportedAction()
    {
        $this->_initAction();

        Mage::getSingleton('core/session')->setExportPost(filter_input_array(INPUT_POST));

        $campaign_id = $this->getRequest()->getParam('campaign_id');
        if (empty($campaign_id)) {
            Mage::getSingleton('core/session')->addError('Campaign Id can\'t be empty.');
            $this->_redirect('getresponse/index/export');

            return;
        }

        $params = $this->getRequest()->getParams();

        $this->exportCustomers($campaign_id, $params);

        $this->_redirect('getresponse/index/export');
    }

	protected function _prepare_customs_for_mapping()
    {
        $gr_customs = $gr_custom_values = [];

        if (!empty($this->settings->customs)) {
            foreach ($this->settings->customs as $custom) {

                if (in_array($custom['custom_field'], ['firstname', 'lastname', 'email'])) {
                    continue;
                }

                $gr_custom_values[] = $custom['custom_field'];
                $gr_customs[] = $custom;
            }
        }

        $this->settings->custom_values_json = json_encode($gr_custom_values);
        $this->settings->customs_json = json_encode($gr_customs);
    }

    /**
     * GET getresponse/index/create_list
     */
    public function create_listAction()
    {
        $this->_title($this->__('New List'))
            ->_title($this->__('GetResponse'));

        $this->_initAction();
        $this->disableIntegrationIfApiNotActive();
        $this->setNewCampaignSettings();

        $this->_addContent($this->getLayout()
            ->createBlock('Mage_Core_Block_Template', 'getresponse_content')
            ->setTemplate('getresponse/create_list.phtml')
            ->assign('settings', $this->settings)
            ->assign('back_url', base64_decode($this->getRequest()->getParam('back_url')))
        );

        $this->renderLayout();
	}

    /**
     * POST getresponse/index/create_new_list
     */
    public function create_new_listAction()
    {
        $this->_initAction();
        Mage::getSingleton('core/session')->setExportPost(filter_input_array(INPUT_POST));

        $params = $this->getRequest()->getParams();

        $error = $this->validateNewListParameters($params);

        if (strlen($error > 0)) {
            Mage::getSingleton('core/session')->addError($error);
            $this->_redirect($params['back_url']);
            return;
        }

        $campaign_name = strtolower($params['campaign_name']);

        $add = Mage::helper('getresponse/api')->addCampaignToGR(
            $campaign_name,
            $params['from'],
            $params['reply_to'],
            $params['confirmation_subject'],
            $params['confirmation_body']
        );

        if (is_object($add) && isset($add->campaignId)) {
            Mage::getSingleton('core/session')->addSuccess('List created');
            $this->_redirect($params['back_url']);
        } elseif (is_object($add) && $add->code == 1008) {
            Mage::getSingleton('core/session')->addError('List name you entered already exists. Please enter a different name.');
            $this->_redirect('getresponse/index/create_list');
        } else {
            Mage::getSingleton('core/session')->addError('List "' . $campaign_name . '" has not been added' . ' - ' . $add->message . '.');
            $this->_redirect('getresponse/index/create_list');
        }

    }

    /**
     * @param array $params
     *
     * @return string
     */
    private function validateNewListParameters($params)
    {
        if (!isset($params['campaign_name']) || strlen($params['campaign_name']) < 3) {
            return 'You need to enter a name that\'s at least 3 characters long';
        }

        if (!isset($params['from']) || strlen($params['from']) === 0) {
            return 'You need to select a sender email address';
        }

        if (!isset($params['reply_to']) || strlen($params['reply_to']) === 0) {
            return 'You need to select reply to';
        }

        if (!isset($params['confirmation_subject']) || strlen($params['confirmation_subject']) === 0) {
            return 'You need to select a subject line for the subscription confirmation message';
        }

        if (!isset($params['confirmation_body']) || strlen($params['confirmation_body']) === 0) {
            return 'You need to select confirmation message body template';
        }

        return '';
    }

	/**
	 * GET getresponse/index/viapage
	 */
	public function viapageAction()
	{
		$this->_title($this->__('Subscription via registration page'))
				->_title($this->__('GetResponse'));

		$this->active_tab = 'viapage';

		$this->_initAction();
		$this->disableIntegrationIfApiNotActive();

		$this->settings->campaign_days = Mage::helper('getresponse/api')->getCampaignDays();
		$this->setNewCampaignSettings();

        $this->_prepare_customs_for_mapping();

		$this->_addContent($this->getLayout()
				->createBlock('Mage_Core_Block_Template', 'getresponse_content')
				->setTemplate('getresponse/viapage.phtml')
				->assign('settings', $this->settings)
		);

		$this->renderLayout();
	}

	/**
	 * POST getresponse/index/subscribtion
	 */
	public function subpageAction()
	{
		$this->_initAction();

		$campaign_id = $this->getRequest()->getParam('campaign_id');
        $active_subscription = $this->getRequest()->getParam('active_subscription', 0);
        $gr_sync_order_data = $this->getRequest()->getParam('gr_sync_order_data', 0);
        $subscription_on_checkout = $this->getRequest()->getParam('subscription_on_checkout', 0);
        $gr_autoresponder = $this->getRequest()->getParam('gr_autoresponder', 0);
        $cycle_day = $this->getRequest()->getParam('cycle_day', 0);

		$params = $this->getRequest()->getParams();

		if (empty($campaign_id)) {
            Mage::getSingleton('core/session')->addError('You need to select list');
            $this->_redirect('getresponse/index/viapage');
            return;
        }

		if (!empty($params['gr_custom_field'])) {
			foreach ($params['gr_custom_field'] as $field_key => $field_value) {
				if (false == preg_match('/^[_a-zA-Z0-9]{2,32}$/m', ($field_value))) {
					Mage::getSingleton('core/session')->addError('Incorrect field name: ' . $field_value . '.');
					$this->_redirect('getresponse/index/viapage');
					return;
				}
			}
		}

		if (1 != $gr_autoresponder) {
            $cycle_day = 0;
		}

        Mage::getModel('getresponse/settings')->updateSettings(
            array(
                'campaign_id' => $campaign_id,
                'active_subscription' => $active_subscription,
                'update_address' => $gr_sync_order_data,
                'cycle_day' => $cycle_day,
                'subscription_on_checkout' => $subscription_on_checkout
            ),
            $this->current_shop_id
		);



		if (!empty($params['gr_sync_order_data']) && isset($params['gr_custom_field'])) {

            $custom_map = array();

            foreach ($params['gr_custom_field'] as $key => $name) {
                $custom_map[$name] = $params['custom_field'][$key];
            }
            //echo "<pre>"; print_r($custom_map); die;
			foreach ($this->settings->customs as $cf) {
				if (isset($custom_map[$cf['custom_field']])) {

					Mage::getModel('getresponse/customs')->updateCustom(
					    $cf['id_custom'],
                        array(
                            'custom_value' => $custom_map[$cf['custom_field']],
                            'active_custom' => GetresponseIntegration_Getresponse_Model_Customs::ACTIVE
                        )
                    );
				}
				else {
					Mage::getModel('getresponse/customs')->updateCustom(
					    $cf['id_custom'],
                        array('active_custom' => GetresponseIntegration_Getresponse_Model_Customs::INACTIVE)
                    );
				}
			}
		}

		Mage::getSingleton('core/session')->addSuccess('Settings saved');

		$this->_redirect('getresponse/index/viapage');
	}

	/**
	 * GET getresponse/index/viawebform
	 */
	public function viawebformAction()
	{
		$this->_title($this->__('Subscription via a form'))
				->_title($this->__('GetResponse'));

		$this->active_tab = 'viawebform';

		$this->_initAction();
		$this->disableIntegrationIfApiNotActive();

		$this->settings->forms = array();
		$forms = Mage::helper('getresponse/api')->getForms();
		if (!empty($forms)) {
			foreach ($forms as $form) {
				if (isset($form->status) && $form->status == 'published') {
					$this->settings->forms[] = $form;
				}
			}
		}

		$this->settings->webforms = array();
		$webforms = Mage::helper('getresponse/api')->getWebForms();
		if (!empty($webforms)) {
			foreach ($webforms as $webform) {
				if (isset($webform->status) && $webform->status == 'enabled') {
					$this->settings->webforms[] = $webform;
				}
			}
		}

		$this->settings->layout_positions = $this->layout_positions;
		$this->settings->block_positions = $this->block_positions;

		$this->_addContent($this->getLayout()
				->createBlock('Mage_Core_Block_Template', 'getresponse_content')
				->setTemplate('getresponse/viawebform.phtml')
				->assign('settings', $this->settings)
		);

		$this->renderLayout();
	}

	/**
	 * POST getresponse/index/subform
	 */
	public function subformAction()
	{
		$this->_initAction();

        $active = $this->getRequest()->getParam('active_subscription', 0);
		$params = $this->getRequest()->getParams();

		if (0 == $active) {
            Mage::getModel('getresponse/webforms')->updateWebforms(
                array('webform_id' => $params['webform_id'],
                    'id_shop' => $this->current_shop_id,
                    'active_subscription' => $active,
                    'layout_position' => '',
                    'block_position' => '',
                    'webform_title' => '',
                    'url' => ''
                ),
                $this->current_shop_id
            );
            Mage::getSingleton('core/session')->addSuccess('Form unpublished');
            $this->_redirect('getresponse/index/viawebform');
            return;
        }

		if (empty($params['webform_id'])) {
			Mage::getSingleton('core/session')->addError('Webform Id can\'t be empty');
			$this->_redirect('getresponse/index/viawebform');

			return;
		}

		if (isset($params['webform_title'])) {
			if ($params['webform_title'] == '') {
				Mage::getSingleton('core/session')->addError('Block Title can\'t be empty');
				$this->_redirect('getresponse/index/viawebform');

				return;
			}
			elseif (strlen($params['webform_title']) > 255) {
				Mage::getSingleton('core/session')->addError('Title is too long. Max: 255 characters');
				$this->_redirect('getresponse/index/viawebform');

				return;
			}
		}

		if ( !empty($params['gr_webform_type']) && $params['gr_webform_type'] == 'old') {
			$webforms = self::grapi()->get_web_form($params['webform_id']);
		}
		else {
			$webforms = self::grapi()->get_form($params['webform_id']);
		}

		if (empty($webforms->codeDescription)) {
			Mage::getModel('getresponse/webforms')->updateWebforms(
					array('webform_id' => $params['webform_id'],
                          'id_shop' => $this->current_shop_id,
							'active_subscription' => $active,
							'layout_position' => $params['layout_position'],
							'block_position' => $params['block_position'],
							'webform_title' => trim($params['webform_title']),
							'url' => $webforms->scriptUrl
					),
					$this->current_shop_id
			);

			Mage::getSingleton('core/session')->addSuccess('Form published');
		}
		else {
			Mage::getSingleton('core/session')->addError('Error - please try again');
		}

		$this->_redirect('getresponse/index/viawebform');
	}

	/**
	 * GET getresponse/index/automation
	 */
	public function automationAction()
	{
		$this->_title($this->__('Campaign rules'))
				->_title($this->__('GetResponse'));

		$this->active_tab = 'automation';

		$this->_initAction();
		$this->disableIntegrationIfApiNotActive();

		$this->settings->actions = $this->actions;
		$this->settings->automation_statuses = $this->automation_statuses;
		$this->settings->categories = $this->getCategories();
		$this->settings->campaign_days = Mage::helper('getresponse/api')->getCampaignDays();
		$this->settings->automations =
				Mage::getModel('getresponse/automations')->getAutomations($this->current_shop_id);

		if (!empty($this->settings->automations)) {
		    foreach ($this->settings->automations as &$automation) {
		        if ('copy' === $automation['action']) {
		            $automation['action'] = 'copied';
                } else if ('move' === $automation['action']) {
                    $automation['action'] = 'moved';
                }
            }
        }

		$this->settings->categories_tree = $this->getTreeCategoriesHTML(1, false);

		$this->_addContent($this->getLayout()
				->createBlock('Mage_Core_Block_Template', 'getresponse_content')
				->setTemplate('getresponse/automation.phtml')
				->assign('settings', $this->settings)
		);

		$this->renderLayout();
	}

    /**
     * POST getresponse/index/deleteautomation
     */
    public function delete_automationAction()
    {
        $id = $this->getRequest()->getParam('id');
        if (empty($id)) {
            Mage::getSingleton('core/session')->addError('Rule not found');
            $this->_redirect('getresponse/index/automation');
            return;
        }
        Mage::getModel('getresponse/automations')->deleteAutomation($id);
        Mage::getSingleton('core/session')->addSuccess('Rule deleted');
        $this->_redirect('getresponse/index/automation');
        return;
    }

	/**
	 * disconnect account
	 */
	public function disconnectAction()
	{
		$this->_initAction();

		Mage::helper('getresponse')->disconnectIntegration($this->current_shop_id);
		Mage::getSingleton('core/session')->addSuccess('GetResponse account disconnected');

		$this->_redirect('getresponse/index/index');
	}

	/**
	 * @param $campaign_id
	 * @param $params
	 *
	 * @return bool
	 */
	public function exportCustomers($campaign_id, $params)
	{
		$subscribers = Mage::helper('getresponse')->getNewsletterSubscribersCollection();

		$cycle_day = '';
		if (isset($params['gr_autoresponder']) && 1 == $params['gr_autoresponder']) {
			$cycle_day = (int)$params['cycle_day'];
		}

		$custom_fields = $this->prepareCustomFields(
            isset($params['gr_custom_field']) ? $params['gr_custom_field'] : array(),
            isset($params['custom_field']) ? $params['custom_field'] : array()
        );

		if ( !empty($params['gr_custom_field'])) {
			foreach ($params['gr_custom_field'] as $field_key => $field_value) {
				if (false == preg_match('/^[_a-zA-Z0-9]{2,32}$/m', ($field_value))) {
					Mage::getSingleton('core/session')->addError('Incorrect field name: ' . $field_key . '.');

					return false;
				}
			}
		}

		$reports = [
			'created' => 0,
			'updated' => 0,
			'error' => 0,
		];

		if ( !empty($subscribers)) {
			foreach ($subscribers as $subscriber) {
				$customer = Mage::getResourceModel('customer/customer_collection')
                    ->addAttributeToSelect('email')
                    ->addAttributeToSelect('firstname')
                    ->addAttributeToSelect('lastname')
                    ->joinAttribute('street', 'customer_address/street', 'default_billing', null, 'left')
                    ->joinAttribute('postcode', 'customer_address/city', 'default_billing', null, 'left')
                    ->joinAttribute('city', 'customer_address/postcode', 'default_billing', null, 'left')
                    ->joinAttribute('telephone', 'customer_address/telephone', 'default_billing', null, 'left')
                    ->joinAttribute('country', 'customer_address/country_id', 'default_billing', null, 'left')
                    ->joinAttribute('company', 'customer_address/company', 'default_billing', null, 'left')
                    ->joinAttribute('birthday', 'customer/dob', 'entity_id', null, 'left')
                    ->addFieldToFilter(array(
                        array('attribute'=>'email','eq'=>$subscriber->getEmail())
                    ))->getFirstItem();

                if (!empty($customer)) {
                    $name = $customer->getName();
                } else {
                    $name = null;
                }
                $result = Mage::helper('getresponse/api')->addContact(
                        $campaign_id,
                        $name,
                        $subscriber->getEmail(),
                        $cycle_day,
                        Mage::getModel('getresponse/customs')->mapExportCustoms($custom_fields, $customer)
                );

                if (GetresponseIntegration_Getresponse_Helper_Api::CONTACT_CREATED === $result) {
                    $reports['created'] ++;
                } elseif(GetresponseIntegration_Getresponse_Helper_Api::CONTACT_UPDATED == $result) {
                    $reports['updated'] ++;
                } else {
                    $reports['error'] ++;
                }
			}
		}

		//$flashMessage = 'Contact export process has completed. (';
		//$flashMessage .= 'created:'.$reports['created']. ', ';
		//$flashMessage .= 'updated:'.$reports['updated']. ', ';
		//$flashMessage .= 'not added:'.$reports['error'] . ').';

		$flashMessage = 'Customer data exported';

		Mage::getSingleton('core/session')->addSuccess($flashMessage);

		return true;
	}

	/**
	 * disable integration if api is not active
	 */
	protected function disableIntegrationIfApiNotActive()
	{
		if ( !empty($this->settings->api['api_key'])) {
			$this->grapi()->api_key = $this->settings->api['api_key'];
			$status = $this->grapi()->check_api( $this->settings->api['api_url'], $this->settings->api['api_domain'] );
			if ( !empty($status->codeDescription)) {
				Mage::helper('getresponse')->disconnectIntegration($this->current_shop_id);
				$this->settings->api['api_key'] = null;
				$this->settings->api['api_domain'] = null;
				$this->settings->api['api_url'] = null;
				$this->disconnected = true;
				Mage::getSingleton('core/session')->addError('Invalid API Key. Account has been disconnected.');
				$this->_redirect('getresponse/index/index');
			}
		}
	}

	/**
	 * set from, confirmation subject, confirmation body
	 */
	protected function setNewCampaignSettings()
	{
		$locale = Mage::app()->getLocale()->getDefaultLocale();
		$code = strtoupper(substr($locale, 0, 2));

		$from = self::grapi()->get_account_from_fields();
		if (empty($from->codeDescription)) {
			$this->settings->from = $from;
		}
		$confirmation_subject = self::grapi()->get_subscription_confirmations_subject($code);
		if (empty($confirmation_subject->codeDescription)) {
			$this->settings->confirmation_subject = $confirmation_subject;
		}
		$confirmation_body = self::grapi()->get_subscription_confirmations_body($code);
		if (empty($confirmation_body->codeDescription)) {
			$this->settings->confirmation_body = $confirmation_body;
		}
	}

	/**
	 * @param        $parentId
	 * @param        $isChild
	 * @param string $prefix
	 *
	 * @return string
	 */
	public function getTreeCategoriesHTML($parentId, $isChild, $prefix = '', $defaultCategory = null)
	{
		$options = '';
		$allCats = Mage::getModel('catalog/category')
				->getCollection()
				->addAttributeToSelect('*')
				->addAttributeToFilter('is_active', '1')
				->addAttributeToFilter('parent_id', array('eq' => $parentId));

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
	 * Get categories
	 * @return array
	 */
	protected function getCategories()
	{
		$results = array();
		$categories = Mage::getModel('catalog/category')
				->getCollection()
				->setStoreId($this->current_shop_id)
				->addFieldToFilter('is_active', 1)
				->addAttributeToSelect('*');

		foreach ($categories as $category) {
			$catid = $category->getId();
			$data = $category->getData();
			$results[$catid] = $data;
		}

		return $results;
	}

	private function prepareCustomFields($grCustomFields, $customFields)
    {
        $fields = [];

        foreach ($grCustomFields as $id => $name) {
            $fields[$name] = isset($customFields[$id]) ? $customFields[$id] : null;
        }

        return $fields;
    }
}