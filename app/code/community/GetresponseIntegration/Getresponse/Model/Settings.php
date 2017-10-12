<?php

/**
 * Class GetresponseIntegration_Getresponse_Model_Settings
 */
class GetresponseIntegration_Getresponse_Model_Settings extends Mage_Core_Model_Abstract
{
	protected function _construct()
	{
		parent::_construct();
		$this->_init('getresponse/settings');
	}

    /**
     * Override for automaticly choosing integration by shop id
     *
     * @param $id
     * @param null $field
     *
     * @return mixed
     */
    public function load($id, $field = null)
    {
        if (is_null($field)) {
            $field = 'id_shop';
        }

        return parent::load($id, $field);
    }

    /**
	 * @param $shop_id
	 *
	 * @return bool
	 */
	public function disconnectSettings($shop_id)
	{
		$model = $this->load($shop_id)->addData(array(
			'api_key' => '',
			'api_url' => '',
			'api_domain' => '',
			'active_subscription' => '0',
			'update_address' => '0',
			'campaign_id' => '',
			'cycle_day' => '0',
            'has_gr_traffic_feature_enabled' => '0',
            'has_active_traffic_module' => '0',
            'tracking_code_snippet' => '',
            'subscription_on_checkout' => '0',
            'newsletter_subscription' => 0,
            'newsletter_campaign_id' => '',
            'newsletter_cycle_day' => 0
		));

		try {
			$model->save();
		} catch (Exception $e) {
			return false;
		}

		return true;
	}

	/**
	 * @param $data
	 * @param $shop_id
	 *
	 * @return bool
	 */
	public function updateSettings($data, $shop_id)
	{
		$model = $this->load($shop_id)->addData($data);

		try {
			$model->save();
		} catch (Exception $e) {
			return false;
		}

		return true;
	}
}