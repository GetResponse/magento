<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

use GetResponse\GetResponseIntegration\Helper\GetResponseAPI3;

/**
 * Class Repository
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class Repository
{
    /** @var  GetResponseAPI3 */
    private $resource;

    /**
     * @param GetResponseAPI3 $resource
     */
    public function __construct(GetResponseAPI3 $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @param string $name
     * @param string $lang
     * @param string $currency
     *
     * @return mixed
     */
    public function createShop($name, $lang, $currency)
    {
        return $this->resource->createShop($name, $lang, $currency);
    }

    /**
     * @return mixed
     */
    public function getShops()
    {
        return $this->resource->getShops();
    }

    /**
     * @param string $id
     *
     * @return mixed
     */
    public function deleteShop($id)
    {
        return $this->resource->deleteShop($id);
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function addContact($params)
    {
        return $this->resource->addContact($params);
    }

    /**
     * @param string $id
     * @param array $params
     *
     * @return mixed
     */
    public function updateContact($id, $params)
    {
        return $this->resource->updateContact($id, $params);
    }

    /**
     * @param string $id
     *
     * @return mixed
     */
    public function deleteContact($id)
    {
        return $this->resource->deleteContact($id);
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function getContacts($params)
    {
        return $this->resource->getContacts($params);
    }

    /**
     * @param string $id
     *
     * @return mixed
     */
    public function getContact($id)
    {
        return $this->resource->getContact($id);
    }

    /**
     * @param string $email
     * @param string $campaign
     *
     * @return mixed
     */
    public function getContactByEmail($email, $campaign)
    {
        $result = (array)$this->resource->getContacts([
            'query' => [
                'email' => $email,
                'campaignId' => $campaign
            ]
        ]);

        return array_pop($result);
    }

    /**
     * @return array
     */
    public function getAccountDetails()
    {
        return (array)$this->resource->ping();
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getCustomFieldByName($name)
    {
        return $this->resource->getCustomFieldByName($name);
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function addCustomField($params)
    {
        return $this->resource->addCustomField($params);
    }

    /**
     * @param Campaign $campaign
     *
     * @return mixed
     */
    public function createCampaign(Campaign $campaign)
    {
        return $this->resource->createCampaign($campaign->forApiRequest());
    }

    /**
     * @return mixed
     */
    public function getFeatures()
    {
        return $this->resource->getFeatures();
    }

    /**
     * @return mixed
     */
    public function getTrackingCode()
    {
        return $this->resource->getTrackingCode();
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function getCampaigns($params)
    {
        return $this->resource->getCampaigns($params);
    }

    /**
     * @param string $id
     *
     * @return mixed
     */
    public function getCampaign($id)
    {
        return $this->resource->getCampaign($id);
    }

    /**
     * @return mixed
     */
    public function getAccountFromFields()
    {
        return $this->resource->getAccountFromFields();
    }

    /**
     * @param string $lang
     *
     * @return mixed
     */
    public function getSubscriptionConfirmationsSubject($lang)
    {
        return $this->resource->getSubscriptionConfirmationsSubject($lang);
    }

    /**
     * @param string $lang
     *
     * @return mixed
     */
    public function getSubscriptionConfirmationsBody($lang)
    {
        return $this->resource->getSubscriptionConfirmationsBody($lang);
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function getAutoresponders($params)
    {
        return $this->resource->getAutoresponders($params);
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function getForms($params)
    {
        return $this->resource->getForms($params);
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function getWebForms($params = [])
    {
        return $this->resource->getWebForms($params);
    }

    /**
     * @param string $shopId
     * @param array $params
     *
     * @return mixed
     */
    public function addProduct($shopId, $params)
    {
        return $this->resource->addProduct($shopId, $params);
    }

    /**
     * @param string $shopId
     * @param string $cartId
     *
     * @return mixed
     */
    public function deleteCart($shopId, $cartId)
    {
        return $this->resource->deleteCart($shopId, $cartId);
    }

    /**
     * @param string $shopId
     * @param array $params
     *
     * @return mixed
     */
    public function addCart($shopId, $params)
    {
        return $this->resource->addCart($shopId, $params);
    }

    /**
     * @param string $shopId
     * @param string $cartId
     * @param array $params
     *
     * @return mixed
     */
    public function updateCart($shopId, $cartId, $params)
    {
        return $this->resource->updateCart($shopId, $cartId, $params);
    }

    /**
     * @param string $shopId
     * @param array $params
     *
     * @return mixed
     */
    public function createOrder($shopId, $params)
    {
        return $this->resource->createOrder($shopId, $params);
    }

    /**
     * @param string $shopId
     * @param string $orderId
     * @param array $params
     *
     * @return mixed
     */
    public function updateOrder($shopId, $orderId, $params)
    {
        return $this->resource->updateOrder($shopId, $orderId, $params);
    }

    /**
     * @param string $shopId
     * @param string $orderId
     * @param array $params
     *
     * @return mixed
     */
    public function getOrder($shopId, $orderId, $params = [])
    {
        return $this->resource->getOrder($shopId, $orderId, $params);
    }
}
