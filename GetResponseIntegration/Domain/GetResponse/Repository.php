<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

use GetResponse\GetResponseIntegration\Helper\GetResponseAPI3;
use Magento\Framework\ObjectManagerInterface;

class Repository
{
    /** @var GetResponseAPI3 */
    private $resource;

    /** @var ObjectManagerInterface */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
        $this->resource = $this->getClient();
    }

    public function createShop($name, $lang, $currency)
    {
        return $this->resource->createShop($name, $lang, $currency);
    }

    public function deleteShop($id)
    {
        return $this->resource->deleteShop($id);
    }

    public function addContact($params)
    {
        return $this->resource->addContact($params);
    }

    public function updateContact($id, $params)
    {
        return $this->resource->updateContact($id, $params);
    }

    public function getContactByEmail($email, $campaign)
    {
        $result = (array) $this->resource->getContacts([
            'query' => [
                'email'      => $email,
                'campaignId' => $campaign
            ]
        ]);

        return array_pop($result);
    }

    public function getCustomFieldByName($name)
    {
        return $this->resource->getCustomFieldByName($name);
    }

    public function addCustomField($params)
    {
        return $this->resource->addCustomField($params);
    }

    public function createCampaign($params)
    {
        return $this->resource->createCampaign($params);
    }


    private function getClient()
    {
        $storeId = $this->objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $settings = $this->objectManager->create('GetResponse\GetResponseIntegration\Model\Settings');
        $data = $settings->load($storeId, 'id_shop')->getData();

        $moduleInfo = $this->objectManager->get('Magento\Framework\Module\ModuleList')->getOne('GetResponse_GetResponseIntegration');

        $version = isset($moduleInfo['setup_version']) ? $moduleInfo['setup_version'] : '';

        return new GetResponseAPI3($data['api_key'], $data['api_url'], $data['api_domain'], $version);
    }
}