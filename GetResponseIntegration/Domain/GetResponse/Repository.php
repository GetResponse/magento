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

    public function createShop($name, $lang, $currency)
    {
        return $this->resource->createShop($name, $lang, $currency);
    }

    public function getShops()
    {
        return $this->resource->getShops();
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

    public function deleteContact($id)
    {
        return $this->resource->deleteContact($id);
    }

    public function getContacts($params)
    {
        return $this->resource->getContacts($params);
    }

    public function getContact($id)
    {
        return $this->resource->getContact($id);
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

    /**
     * @return array
     */
    public function getAccountDetails()
    {
        return (array) $this->resource->ping();
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

    public function getFeatures()
    {
        return $this->resource->getFeatures();
    }

    public function getTrackingCode()
    {
        return $this->resource->getTrackingCode();
    }

    public function getCampaigns($params)
    {
        return $this->resource->getCampaigns($params);
    }

    public function getCampaign($id)
    {
        return $this->resource->getCampaign($id);
    }

    public function getAccountFromFields()
    {
        return $this->resource->getAccountFromFields();
    }

    public function getSubscriptionConfirmationsSubject($lang)
    {
        return $this->resource->getSubscriptionConfirmationsSubject($lang);
    }

    public function getSubscriptionConfirmationsBody($lang)
    {
        return $this->resource->getSubscriptionConfirmationsBody($lang);
    }

    public function getAutoresponders($params)
    {
        return $this->resource->getAutoresponders($params);
    }

    public function getForms($params)
    {
        return $this->resource->getForms($params);
    }

    public function getWebForms($params = [])
    {
        return $this->resource->getWebForms($params);
    }

    public function addProduct($shopId, $params)
    {
        return $this->resource->addProduct($shopId, $params);
    }

    public function deleteCart($shopId, $cartId)
    {
        return $this->resource->deleteCart($shopId, $cartId);
    }

    public function addCart($shopId, $params)
    {
        return $this->resource->addCart($shopId, $params);
    }

    public function updateCart($shopId, $cartId, $params)
    {
        return $this->resource->updateCart($shopId, $cartId, $params);
    }

    public function createOrder($shopId, $params)
    {
        return $this->resource->createOrder($shopId, $params);
    }

    public function updateOrder($shopId, $orderId, $params)
    {
        return $this->resource->updateOrder($shopId, $orderId, $params);
    }

    public function getOrder($shopId, $orderId, $params = [])
    {
        return $this->resource->getOrder($shopId, $orderId, $params);
    }
}
