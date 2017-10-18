<?php

/**
 * Class GetresponseIntegration_Getresponse_Model_Automations
 */
class GetresponseIntegration_Getresponse_Model_Automations extends Mage_Core_Model_Abstract
{
	const ACTIVE = 1;
	const INACTIVE = 0;

	protected function _construct()
	{
		parent::_construct();
		$this->_init('getresponse/automations');
	}

	/**
	 * @param $shopId
	 *
	 * @return mixed
	 */
	public function getAutomations($shopId)
	{
		return $this->getCollection()->addFieldToFilter('id_shop', $shopId)->getData();
	}

    /**
     * @param $id
     *
     * @return mixed
     */
    public function getAutomation($id)
    {
        return $this->getCollection()->addFieldToFilter('id', $id)->getData();
    }

	/**
	 * @param $id
	 * @param $data
	 *
	 * @return bool
	 */
	public function updateAutomation($id, $data)
	{
		$model = $this->load($id)->addData($data);

		try {
			$model->save();
		} catch (Exception $e) {
			return false;
		}

		return true;
	}

	/**
	 * @param $data
	 *
	 * @return bool
	 */
	public function createAutomation($data)
	{
		$model = $this->setData($data);

		try {
			$insertId = $model->save()->getId();
		} catch (Exception $e) {
			return false;
		}

		return $insertId;
	}

	/**
	 * @param $id
	 *
	 * @return bool
	 */
	public function deleteAutomation($id)
	{
		try {
			$this->setId($id)->delete();
		} catch (Exception $e) {
			return false;
		}

		return true;
	}

	/**
	 * @param $categories
	 * @param $shopId
	 *
	 * @return array
	 */
	public function getActiveAutomationsByCategoriesAndShopId($categories, $shopId)
	{
		$automations = array();
		if (empty($categories)) {
			return $automations;
		}

		foreach ($categories as $categoryId) {
			$automation =
				Mage::getModel('getresponse/automations')
					->getCollection()
					->addFieldToFilter('active', self::ACTIVE)
					->addFieldToFilter('id_shop', $shopId)
					->addFieldToFilter('category_id', $categoryId)
					->getData();

			if ( !empty($automation)) {
				$automations[] = $automation[0];
			}
		}

		return $automations;
	}

    /**
     * @param string $shopId
     */
	public function disconnect($shopId)
	{
		$automations = $this->getAutomations($shopId);
		if ( !empty($automations)) {
			foreach ($automations as $automation) {
				$this->deleteAutomation($automation['id']);
			}
		}
	}

}