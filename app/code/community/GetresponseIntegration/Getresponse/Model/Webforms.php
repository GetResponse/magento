<?php

/**
 * Class GetresponseIntegration_Getresponse_Model_Webforms
 */
class GetresponseIntegration_Getresponse_Model_Webforms extends Mage_Core_Model_Abstract
{
	protected function _construct()
	{
		parent::_construct();
		$this->_init('getresponse/webforms');
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
	 * @param array $data
	 * @param int $shopId
	 * @return bool
	 */
	public function updateWebforms($data, $shopId)
	{
		$model = $this->load($shopId)->addData($data);

		try {
			$model->save();
		} catch (Exception $e) {
			return false;
		}

		return true;
	}

    /**
     * @param int $shopId
     */
	public function disconnect($shopId)
	{
		$this->updateWebforms(
            array(
                'webform_id' => '',
                'active_subscription' => '0',
                'url' => '',
                'webform_title' => '',
                'layout_position' => '',
                'block_position' => '',
            )
            , $shopId
        );
	}
}