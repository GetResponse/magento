<?php

class GetresponseIntegration_Getresponse_Helper_Api extends Mage_Core_Helper_Abstract
{
	const CONTACT_ERROR = 1;
	const CONTACT_UPDATED = 2;
	const CONTACT_CREATED = 3;
    const ORIGIN_NAME = 'magento';

	public static $status = array(
		self::CONTACT_CREATED => 'Created',
		self::CONTACT_UPDATED => 'Updated',
		self::CONTACT_ERROR => 'Not added'
	);

	/** @var array */
    private $cachedCustoms = [];

	/**
	 * Getresponse API instance
	 */
	public static function grapi()
	{
		return self::instance();
	}

	/**
	 * @return GetresponseIntegration_Getresponse_Helper_GrApi
	 */
	public static function instance()
	{
		static $instance;

		if (null === $instance) {
			Mage::getResourceHelper('getresponse/grapi');
			$instance = new GetresponseIntegration_Getresponse_Helper_GrApi();
		}

		return $instance;
	}

	/**
	 * @param $email
	 * @param $campaign
	 *
	 * @return mixed
	 */
	public function getContact($email, $campaign)
	{
		$results = (array)$this->grapi()->get_contacts(
			array('query' =>
				array(
					'email' => $email,
					'campaignId' => $campaign
				)
			)
		);

		return array_pop($results);
	}

	/**
	 * @param        $campaign
	 * @param        $name
	 * @param        $email
	 * @param string $cycle_day
	 * @param array  $user_customs
	 *
	 * @return int
	 */
	public function addContact($campaign, $name, $email, $cycle_day = '', $user_customs = array())
	{
		$params = array(
			'email' => $email,
			'campaign' => array('campaignId' => $campaign),
			'ipAddress' => $_SERVER['REMOTE_ADDR'],
		);

		if (!empty(trim($name))) {
            $params['name'] = trim($name);
        }

		if (is_numeric($cycle_day) && $cycle_day >= 0) {
			$params['dayOfCycle'] = $cycle_day;
		}

		$contact = $this->getContact($email, $campaign);

		// If contact already exists in gr account.
		if ( !empty($contact) && isset($contact->contactId)) {

			$results = $this->grapi()->get_contact($contact->contactId);
			if ( !empty($results->customFieldValues) || !empty($user_customs)) {
				$params['customFieldValues'] = $this->mergeUserCustoms($results->customFieldValues, $user_customs);
			}

			$response = $this->grapi()->update_contact($contact->contactId, $params);

			if (isset($response->codeDescription)) {
				return self::CONTACT_ERROR;
			}

			return self::CONTACT_UPDATED;

		}
		else {
            $user_customs['origin'] = self::ORIGIN_NAME;
			$params['customFieldValues'] = $this->set_customs($user_customs);
			$response = $this->grapi()->add_contact($params);

			if (isset($response->codeDescription)) {
				return self::CONTACT_ERROR;
			}

			return self::CONTACT_CREATED;
		}

	}

	/**
	 * Get all custom fields.
	 *
	 * @return array
	 */
	public function get_custom_fields()
	{
		$all_customs = array();
		$results = $this->grapi()->get_custom_fields(array('perPage' => 1000));

		if (empty($results)) {
			return $all_customs;
		}

		foreach ($results as $ac) {
			if (isset($ac->name) && isset($ac->customFieldId)) {
				$all_customs[$ac->name] = $ac->customFieldId;
			}
		}

		return $all_customs;
	}

	/**
	 * Set customs.
	 *
	 * * @param $user_customs
	 *
	 * @return array
	 */
	public function set_customs($user_customs)
	{
		$custom_fields = array();

		if (empty($user_customs)) {
			return $custom_fields;
		}

		foreach ($user_customs as $name => $value) {

            if (!isset($this->cachedCustoms[$name])) {

                $customs = (array)$this->grapi()->get_custom_fields(['query[name]' => $name]);
                $custom = reset($customs);

                // custom field not found - create new
                if (empty($custom) || empty($custom->customFieldId)) {
                    $custom = $this->grapi()->add_custom_field([
                        'name' => $name,
                        'type' => is_array($value) ? "checkbox" : "text",
                        'hidden' => "false",
                        'values' => is_array($value) ? $value : [$value],
                    ]);
                    // Custom adding failed
                    if (!isset($custom->customFieldId)) {
                        continue;
                    }
                }

                $this->cachedCustoms[$name] = $custom;

            } else {
                $custom = $this->cachedCustoms[$name];
            }

            $custom_fields[] = array(
                'customFieldId' => $custom->customFieldId,
                'value' => is_array($value) ? $value : array($value)
            );
		}

		return $custom_fields;
	}

	/**
	 * Merge account custom fields.
	 *
	 * @param array $results      results.
	 * @param array $user_customs user customs.
	 *
	 * @return array
	 */
	public function mergeUserCustoms($results, $user_customs)
	{
		$custom_fields = array();

		if (is_array($results)) {
			foreach ($results as $customs) {
				$value = $customs->value;
				if (in_array($customs->name, array_keys($user_customs))) {
					$user_custom_value = $user_customs[$customs->name];
					$value = is_array($user_custom_value) ? $user_custom_value : array($user_custom_value);
					unset($user_customs[$customs->name]);
				}

				$custom_fields[] = array('customFieldId' => $customs->customFieldId,
										 'value' => $value,
				);
			}
		}

		return array_merge($custom_fields, $this->set_customs($user_customs));
	}

	/**
	 * Get getresponse campaigns from user account
	 */
	public function getGrCampaigns()
	{
		$campaigns = array();
		$results = $this->grapi()->get_campaigns(array('sort' => array('name' => 'asc')));
		if ( !empty($results) && !isset($results->codeDescription)) {
			foreach ($results as $result) {
				$campaigns[$result->campaignId] = $result->name;
			}
		}

		return $campaigns;
	}

	/**
	 * Get getresponse campaigns from user account
	 */
	public function getForms()
	{
		$results = $this->grapi()->get_forms();
		if ( !empty($results) && !isset($results->codeDescription)) {
			return $results;
		}

		return false;
	}

	/**
	 * Get getresponse campaigns from user account
	 */
	public function getWebForms()
	{
		$results = $this->grapi()->get_web_forms();
		if ( !empty($results) && !isset($results->codeDescription)) {
			return $results;
		}

		return false;
	}

	/**
	 * @return array|bool
	 */
	public function getCampaignDays()
	{
		$autoresponders = $this->grapi()->get_autoresponders();
		$campaign_days = array();

		if ( !empty($autoresponders->codeDescription)) {
			return false;
		}

		if ( !empty($autoresponders) && is_object($autoresponders)) {
			foreach ($autoresponders as $autoresponder) {
				if ($autoresponder->triggerSettings->dayOfCycle == null) {
					continue;
				}

				$campaign_days[$autoresponder->triggerSettings->subscribedCampaign->campaignId][$autoresponder->triggerSettings->dayOfCycle] =
					array('day' => $autoresponder->triggerSettings->dayOfCycle,
						  'name' => $autoresponder->subject,
						  'status' => $autoresponder->status,
					);
			}
		}

		return $campaign_days;
	}

	/**
	 * Add camapaign to GetResponse via API
	 *
	 * @param $campaign_name
	 * @param $from_field
	 * @param $reply_to_field
	 * @param $confirmation_subject
	 * @param $confirmation_body
	 *
	 * @return string
	 */
	public function addCampaignToGR($campaign_name, $from_field, $reply_to_field, $confirmation_subject, $confirmation_body)
	{
		$locale = Mage::app()->getLocale()->getLocaleCode();
		$code = strtoupper(substr($locale, 0, 2));

		try {
			$params = array(
				'name'          => $campaign_name,
				'confirmation'  => array(
					'fromField' => array('fromFieldId'  => $from_field),
					'replyTo'   => array('fromFieldId'  => $reply_to_field),
					'subscriptionConfirmationBodyId'    => $confirmation_body,
					'subscriptionConfirmationSubjectId' => $confirmation_subject
				),
				'languageCode'  => $code
			);

			$result = $this->grapi()->create_campaign($params);

			return $result;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * @param $contact_id
	 *
	 * @return bool|mixed
	 */
	public function deleteContact($contact_id)
	{
		$results = $this->grapi()->delete_contact($contact_id);
		if ( !empty($results) && !isset($results->codeDescription)) {
			return $results;
		}

		return false;
	}

	/**
	 * Set details for gr api
	 *
	 * @param $api_key
	 * @param $api_url
	 * @param $api_domain
	 */
	public function setApiDetails($api_key, $api_url, $api_domain)
	{
		if ( !empty($api_key)) {
			$this->grapi()->api_key = $api_key;
		}

		if ( !empty($api_url)) {
			$this->grapi()->api_url = $api_url;
		}

		if ( !empty($api_domain)) {
			$this->grapi()->domain = $api_domain;
		}
	}

    /**
     *
     * @return object
     */
    public function getShops()
    {
        return $this->grapi()->get_shops();
	}

    /**
     * Create new shop.
     *
     * @param string $name
     * @return object
     */
    public function addShop($name)
    {
        $locale = substr(Mage::app()->getLocale()->getDefaultLocale(), 0, 2);
        $currency = Mage::app()->getStore()->getCurrentCurrencyCode();

        return $this->grapi()->add_shop($name, $locale, $currency);
    }

    public function addCart($shopId, $params)
    {
        return $this->grapi()->add_new_cart($shopId, $params);
    }

    public function updateCart($shopId, $cartId, $params)
    {
        return $this->grapi()->update_cart($shopId, $cartId, $params);
    }

    public function deleteCart($shopId, $cartId)
    {
        return $this->grapi()->delete_cart($shopId, $cartId);
    }

    public function getProductByMagentoId($shopId, $magentoProductId)
    {
        $filter = [
            'query' => [
                'metaFieldNames' => 'externalId',
                'metaFieldValues' => $magentoProductId
            ]
        ];

        return $this->grapi()->get_products($shopId, $filter);
    }

    public function addProduct($shopId, $params)
    {
        return $this->grapi()->add_product($shopId, $params);
    }

    public function createOrder($shopId, $params)
    {
        return $this->grapi()->create_order($shopId, $params);
    }

    public function updateOrder($shopId, $orderId, $params)
    {
        return $this->grapi()->update_order($shopId, $orderId, $params);
    }

    /**
     * @param string $shopId
     * @return bool
     */
    public function deleteShop($shopId)
    {
        $response = $this->grapi()->delete_shop($shopId);
        if (false === $response || !empty($response->codeDescription)) {
            return false;
        } else {
            return true;
        }
    }

}