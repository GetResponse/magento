<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

use GetResponse\GetResponseIntegration\Helper\Config;
use GetResponse\GetResponseIntegration\Helper\GetResponseAPI3;
use Magento\Framework\App\CacheInterface;

/**
 * Class Repository
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class Repository
{
    /** @var  GetResponseAPI3 */
    private $resource;

    /** @var CacheInterface */
    private $cache;

    /**
     * @param GetResponseAPI3 $resource
     * @param CacheInterface $cache
     */
    public function __construct(
        GetResponseAPI3 $resource,
        CacheInterface $cache
    ) {
        $this->resource = $resource;
        $this->cache = $cache;
    }

    /**
     * @param string $name
     * @param string $lang
     * @param string $currency
     *
     * @return array
     */
    public function createShop($name, $lang, $currency)
    {
        return $this->resource->createShop($name, $lang, $currency);
    }

    /**
     * @return array
     */
    public function getShops()
    {
        return $this->resource->getShops();
    }

    /**
     * @param string $id
     * @return array
     */
    public function deleteShop($id)
    {
        return $this->resource->deleteShop($id);
    }

    /**
     * @param array $params
     * @return array
     */
    public function addContact($params)
    {
        return $this->resource->addContact($params);
    }

    /**
     * @param string $id
     * @param array $params
     *
     * @return array
     */
    public function updateContact($id, $params)
    {
        return $this->resource->updateContact($id, $params);
    }

    /**
     * @param string $id
     */
    public function deleteContact($id)
    {
        $this->resource->deleteContact($id);
    }

    /**
     * @param array $params
     * @return array
     */
    public function getContacts($params)
    {
        return $this->resource->getContacts($params);
    }

    /**
     * @param string $id
     *
     * @return array
     */
    public function getContact($id)
    {
        return $this->resource->getContact($id);
    }

    /**
     * @param string $email
     * @param string $campaign
     *
     * @return array
     */
    public function getContactByEmail($email, $campaign)
    {
        $result = $this->resource->getContacts(['query' => ['email' => $email, 'campaignId' => $campaign]]);
        return array_pop($result);
    }

    /**
     * @return array
     */
    public function getAccountDetails()
    {
        return $this->resource->ping();
    }

    /**
     * @param string $name
     * @return array
     */
    public function getCustomFieldByName($name)
    {
        $cacheKey = md5('getCustomFieldByName::' . $name);

        $cachedData = $this->cache->load($cacheKey);

        if (false !== $cachedData) {
            return unserialize($cachedData);
        }

        $result = $this->resource->getCustomFieldByName($name);

        $this->cache->save(serialize($result), $cacheKey, [Config::CACHE_KEY], Config::CACHE_TIME);

        return $result;
    }

    /**
     * @param array $params
     * @return array
     */
    public function addCustomField($params)
    {
        return $this->resource->addCustomField($params);
    }

    /**
     * @param Campaign $campaign
     * @return array
     */
    public function createCampaign(Campaign $campaign)
    {
        return $this->resource->createCampaign($campaign->forApiRequest());
    }

    /**
     * @return array
     */
    public function getFeatures()
    {
        return $this->resource->getFeatures();
    }

    /**
     * @return array
     */
    public function getTrackingCode()
    {
        return $this->resource->getTrackingCode();
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function getCampaigns($params)
    {
        return $this->resource->getCampaigns($params);
    }

    /**
     * @param string $id
     *
     * @return array
     */
    public function getCampaign($id)
    {
        return $this->resource->getCampaign($id);
    }

    /**
     * @return array
     */
    public function getAccountFromFields()
    {
        return $this->resource->getAccountFromFields();
    }

    /**
     * @param string $lang
     * @return array
     */
    public function getSubscriptionConfirmationsSubject($lang)
    {
        return $this->resource->getSubscriptionConfirmationsSubject($lang);
    }

    /**
     * @param string $lang
     * @return array
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
     * @return array
     */
    public function getForms($params)
    {
        return $this->resource->getForms($params);
    }

    /**
     * @param array $params
     * @return array
     */
    public function getWebForms($params = [])
    {
        return $this->resource->getWebForms($params);
    }

    /**
     * @return array
     */
    public function ping()
    {
        return $this->resource->ping();
    }
}
