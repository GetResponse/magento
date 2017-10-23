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
     * @param int $id
     * @param string|null $field
     * @return Mage_Core_Model_Abstract
     */
    public function load($id, $field = null)
    {
        if (is_null($field)) {
            $field = 'id_shop';
        }

        return parent::load($id, $field);
    }

    /**
	 * @param $shopId
	 * @return bool
	 */
	public function disconnect($shopId)
	{
		$model = $this->load($shopId)->addData(array(
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
	 * @param $shopId
	 *
	 * @return bool
	 */
	public function updateSettings($data, $shopId)
	{
		$model = $this->load($shopId)->addData($data);

		try {
			$model->save();
		} catch (Exception $e) {
			return false;
		}

		return true;
	}
}