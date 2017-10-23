<?php
namespace GetResponse\GetResponseIntegration\Domain\Magento;

/**
 * Class ConnectionSettings
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class ConnectionSettings
{
    /** @var string */
    private $apiKey;

    /** @var string */
    private $url;

    /** @var string */
    private $domain;

    /**
     * @param string $apiKey
     * @param string $url
     * @param string $domain
     */
    public function __construct($apiKey, $url, $domain)
    {
        $this->apiKey = $apiKey;
        $this->url = $url;
        $this->domain = $domain;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'apiKey' => $this->apiKey,
            'url' => $this->url,
            'domain' => $this->domain
        ];
    }
}
