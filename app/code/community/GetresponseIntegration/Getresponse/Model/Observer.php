<?php

/**
 * Getresponse module observer
 *
 * @author Magento
 */
class GetresponseIntegration_Getresponse_Model_Observer
{
	/**
	 * @param Varien_Event_Observer $observer
	 *
	 * @return $this
	 */
	public function addTrackingCodeToHeader(Varien_Event_Observer $observer)
	{
        if ( !Mage::helper('getresponse')->isEnabled()) {
            return $this;
        }

        $shop_id = Mage::helper('getresponse')->getStoreId();

        $settings = Mage::getModel('getresponse/settings')->load($shop_id)->getData();
        if (empty($settings['api_key']) || 0 === (int) $settings['has_active_traffic_module']) {
            return $this;
        }

        $layout = Mage::app()->getLayout();
        /* @var $block Mage_Page_Block_Html_Head */
        $block = $observer->getEvent()->getBlock();

        if ("head" == $block->getNameInLayout()) {
            $myBlock = $layout->createBlock('core/text');
            $myBlock->setText($settings['tracking_code_snippet']);

            $block->append($myBlock);
        }

        if ("footer" == $block->getNameInLayout()) {

            if (Mage::getSingleton('customer/session')->isLoggedIn()) {

                $customer = Mage::getSingleton('customer/session')->getCustomer();

                if (strlen($customer->email) > 0) {

                    $myBlock = $layout->createBlock('core/text');

                    $myBlock->setText('<script type="text/javascript">gaSetUserId("'.$customer->email.'");</script>');

                    $block->append($myBlock);
                }
            }
        }

		return $this;
	}

	/**
	 * @param Varien_Event_Observer $observer
	 *
	 * @return $this
	 */
	public function addJQueryToHeader(Varien_Event_Observer $observer)
	{
	    if ( !Mage::helper('getresponse')->isEnabled()) {
			return $this;
		}

		/* @var $block Mage_Page_Block_Html_Head */
		$block = $observer->getEvent()->getBlock();

		if ("head" == $block->getNameInLayout()) {
			foreach (Mage::helper('getresponse')->getFiles() as $file) {
				$block->addJs(Mage::helper('getresponse')->getJQueryPath($file));
			}
		}

		return $this;
	}

	/**
	 * @param $observer
	 *
	 * @return $this
	 */
	public function set_block($observer)
	{
		if ( !Mage::helper('getresponse')->isEnabled()) {
			return $this;
		}

		$shop_id = Mage::helper('getresponse')->getStoreId();

		$settings = Mage::getModel('getresponse/settings')->load($shop_id)->getData();
		if (empty($settings['api_key'])) {
			return $this;
		}

		$webforms = Mage::getModel('getresponse/webforms')->load($shop_id)->getData();

		if ( !empty($webforms) && $webforms['active_subscription'] == 1 && !empty($webforms['url'])) {
			$action = $observer->getEvent()->getAction();
			//			$fullActionName = $action->getFullActionName();
			//			$sub_position = 'before="cart_sidebar"';
			$sub_position = ($webforms['block_position'] == 'before') ? 'before="-"' : 'after="-"';

			$myXml = '<reference name="' . $webforms['layout_position'] . '">';
			$myXml .= '<block type="core/text_list"
							name="' . $webforms['layout_position'] . '.content"
							as="getresponse_webform_' . $webforms['layout_position'] . '"
							translate="label" ' . $sub_position . '>';
			$myXml .= '<block type="core/template"
							name="getresponse_webform_' . $webforms['layout_position'] . '"
							template="getresponse/webform.phtml">';
			$myXml .= '<action method="setData">
							<name>getresponse_active_subscription</name>
							<value>' . $webforms['active_subscription'] . '</value></action>';
			$myXml .= '<action method="setData">
							<name>getresponse_webform_title</name>
							<value>' . $webforms['webform_title'] . '</value></action>';
			$myXml .= '<action method="setData">
							<name>getresponse_webform_url</name>
							<value>' . str_replace('&', '&amp;', $webforms['url']) . '</value></action>';
			$myXml .= '</block></block>';
			$myXml .= '</reference>';
			$layout = $observer->getEvent()->getLayout();
			//		if ($fullActionName == 'catalog_product_view') {
			$layout->getUpdate()->addUpdate($myXml);
			$layout->generateXml();
			//		}
		}

		return $this;
	}

	/**
	 * @param Varien_Event_Observer $observer
	 *
	 * @return $this
	 */
	public function addCssToHeader(Varien_Event_Observer $observer)
	{
		if ( !Mage::helper('getresponse')->isEnabled()) {
			return $this;
		}

		/* @var $block Mage_Page_Block_Html_Head */
		$block = $observer->getEvent()->getBlock();

		if ("head" == $block->getNameInLayout()) {
			$block->addCss('css/getresponse.css');
			$block->addCss('css/getresponse-custom-field.css');
			$block->addCss('css/jquery-ui.min.css');
			$block->addCss('css/jquery.switchButton.css');
		}

		return $this;
	}

	/**
	 * @param $observer
	 *
	 * @return bool
	 */
	public function createAccount($observer)
	{
		if ( !Mage::helper('getresponse')->isEnabled()) {
			return $this;
		}

		$event = $observer->getEvent();
		$customer = $event->getCustomer();
		$shop_id = Mage::helper('getresponse')->getStoreId();
		$settings = Mage::getModel('getresponse/settings')->load($shop_id)->getData();

		if (empty($settings['api_key']) || $settings['active_subscription'] != '1' || empty($settings['campaign_id'])) {
			return $this;
		}

		$subscriberModel = Mage::getModel('newsletter/subscriber')->loadByEmail($customer->getEmail());
		if (false === $subscriberModel->isSubscribed()) {
			return $this;
		}

		Mage::helper('getresponse/api')->setApiDetails(
			$settings['api_key'],
			$settings['api_url'],
			$settings['api_domain']
		);

		Mage::helper('getresponse/api')->addContact(
			$settings['campaign_id'],
			$customer->getName(),
			$customer->getEmail(),
			$settings['cycle_day'],
			array()
		);

		return $this;
	}

	/**
	 * @param Varien_Event_Observer $observer
	 * @return bool
	 */
	public function createAccountOrder(Varien_Event_Observer $observer)
	{
		if ( !Mage::helper('getresponse')->isEnabled()) {
			return $this;
		}

		$order = $observer->getEvent()->getOrder();

		$categories = Mage::helper('getresponse')->getCategoriesByOrder($order);
		$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
		$shop_id = Mage::helper('getresponse')->getStoreId();

		$settings = Mage::getModel('getresponse/settings')->load($shop_id)->getData();
		if (empty($settings['api_key']) || $settings['active_subscription'] != '1' || empty($settings['campaign_id'])) {
			return $this;
		}

		$subscriberModel = Mage::getModel('newsletter/subscriber')->loadByEmail($customer->getEmail());
		if (false === $subscriberModel->isSubscribed()) {
			return $this;
		}

		$customs = Mage::getModel('getresponse/customs')->getCustoms($shop_id);

        $billing = $customer->getPrimaryBillingAddress();

        $user_customs = [];

        if (is_object($billing)) {
            $user_customs = $billing->getData();
            $user_customs['country'] = $user_customs['country_id'];
        }

		Mage::helper('getresponse/api')->setApiDetails(
			$settings['api_key'],
			$settings['api_url'],
			$settings['api_domain']
		);

		Mage::helper('getresponse/api')->addContact(
			$settings['campaign_id'],
			$customer->getName(),
			$customer->getEmail(),
			$settings['cycle_day'],
			Mage::getModel('getresponse/customs')->mapCustoms($user_customs, $customs)
		);

		$this->automationHandler($categories, $shop_id, $customer, $user_customs, $customs, $settings);

		return $this;
	}

	/**
	 * @param $categories
	 * @param $shop_id
	 * @param $customer
	 * @param $user_customs
	 * @param $customs
	 * @param $settings
	 *
	 * @return bool
	 */
	public function automationHandler($categories, $shop_id, $customer, $user_customs, $customs, $settings)
	{
 		$automations = Mage::getModel('getresponse/automations')
			->getActiveAutomationsByCategoriesAndShopId($categories, $shop_id);

		if (empty($automations)) {
			return false;
		}

		$delete_contact = false;

		foreach ($automations as $automation) {

			Mage::helper('getresponse/api')->addContact(
				$automation['campaign_id'],
				$customer->getName(),
				$customer->getEmail(),
				$automation['cycle_day'],
				Mage::getModel('getresponse/customs')->mapCustoms($user_customs, $customs)
			);

			if ($automation['action'] == 'move') {
				$delete_contact = true;
			}
		}

		if ($delete_contact === true) {
			$contact = Mage::helper('getresponse/api')->getContact($customer->getEmail(), $settings['campaign_id']);
			if ($contact->contactId) {
				Mage::helper('getresponse/api')->deleteContact($contact->contactId);
			}
		}

		return true;
	}

    /**
     * This action is running before all pages loading.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function initBeforeEventAction($observer)
    {
        if (!Mage::helper('getresponse')->isEnabled()) {
            return $this;
        }

        $shop_id = Mage::helper('getresponse')->getStoreId();
        $settings = Mage::getModel('getresponse/settings')->load($shop_id)->getData();

        if (empty($settings['api_key'])) {
            return $this;
        }

        Mage::register('_subscription_on_checkout', (bool) $settings['subscription_on_checkout']);
    }

    /**
     * Get billing data if 'is_subscribed' parameter is ticked.
     *
     * @param  Varien_Event_Observer$observer
     * @return $this
     */
    public function checkoutSaveAddress($observer)
    {
        if (!Mage::helper('getresponse')->isEnabled()) {
            return $this;
        }

        $post = Mage::app()->getRequest()->getPost();

        if (empty($post) || empty($post['billing']) || (isset($post['is_subscribed']) && $post['is_subscribed'] != 1)) {
            return $this;
        }

        /** @var Mage_Core_Model_Session $session */
        $session = Mage::getSingleton('core/session');

        $session->setData('_is_subscribed', true);
        $session->setData('_subscriber_data', $post['billing']);

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function checkoutAllAfterFormSubmitted($observer)
    {
        if ( !Mage::helper('getresponse')->isEnabled()) {
            return $this;
        }

        /** @var Varien_Event $event */
        $event = $observer->getEvent();

        /** @var Mage_Sales_Model_Quote $Quote */
        $Quote =$event->getQuote();

        if (false === in_array($Quote->getCheckoutMethod(true), array('register', 'guest'))) {
            return $this;
        }

        /** @var Mage_Core_Model_Session $session */
        $session = Mage::getSingleton('core/session');

        $isSubscribed = (bool) $session->getData('_is_subscribed');

        if (0 === $isSubscribed) {
            return $this;
        }

        $details = (array) $session->getData('_subscriber_data');

        // clear session
        $session->setData('_is_subscribed', null);
        $session->setData('_subscriber_data', null);

        if (empty($details['email'])) {
            return $this;
        }

        Mage::getModel('newsletter/subscriber')->subscribe($details['email']);

        $shop_id = Mage::helper('getresponse')->getStoreId();

        $settings = Mage::getModel('getresponse/settings')->load($shop_id)->getData();

        if (empty($settings['api_key'])) {
            return $this;
        }

        Mage::helper('getresponse/api')->setApiDetails(
            $settings['api_key'],
            $settings['api_url'],
            $settings['api_domain']
        );

        $customs = (array) Mage::getModel('getresponse/customs')->getCustoms($shop_id);

        $details['street'] = join(' ', (array) $details['street']);
        $details['country'] = $details['country_id'];

        Mage::helper('getresponse/api')->addContact(
            $settings['campaign_id'],
            $details['firstname'] . ' ' . $details['lastname'],
            $details['email'],
            $settings['cycle_day'],
            Mage::getModel('getresponse/customs')->mapCustoms($details, $customs)
        );

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function initBeforeAddToNewsletterAction(Varien_Event_Observer $observer)
    {
        if ( !Mage::helper('getresponse')->isEnabled()) {
            return $this;
        }

        $shop_id = Mage::helper('getresponse')->getStoreId();
        $settings = Mage::getModel('getresponse/settings')->load($shop_id)->getData();

        if (empty($settings['api_key']) || '1' !== $settings['newsletter_subscription'] || empty($settings['newsletter_campaign_id'])) {
            return $this;
        }

        $name = $email = null;
        $post = Mage::app()->getRequest()->getPost();

        /** @var Mage_Customer_Model_Session $customerSession */
        $customerSession = Mage::getSingleton('customer/session');
        $customer = $customerSession->getCustomer();

        // only, if customer is logged in.
        if (!$customer->isEmpty() && strlen($customer->email) > 0 && isset($post['is_subscribed']) && $post['is_subscribed'] === 1) {
            $name = $customer->firstname . ' ' . $customer->lastname;
            $email = $customer->email;
        } else if (isset($post['email']) && !empty($post['email'])) {
            $name = 'Friend';
            $email = $post['email'];
        }

        if (empty($email)) {
            return $this;
        }

        $subscriberModel = Mage::getModel('newsletter/subscriber')->loadByEmail($email);

        if (false === $subscriberModel->isSubscribed()) {
            return $this;
        }

        Mage::helper('getresponse/api')->setApiDetails(
            $settings['api_key'],
            $settings['api_url'],
            $settings['api_domain']
        );

        Mage::helper('getresponse/api')->addContact(
            $settings['newsletter_campaign_id'],
            $name,
            $email,
            $settings['cycle_day'],
            []
        );

        return $this;
    }
}
