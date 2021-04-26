<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

use GrShareCode\Cache\CacheInterface as GrShareCodeCacheInterface;
use Magento\Framework\App\CacheInterface;

class ShareCodeCache implements GrShareCodeCacheInterface
{
    private $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param string $key
     * @param string $value
     * @param int $ttl
     */
    public function set($key, $value, $ttl)
    {
        $this->cache->save($value, $key, [], $ttl);
    }

    /**
     * @param string $key
     * @return string
     */
    public function get($key)
    {
        return $this->cache->load($key);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        $response = $this->cache->load($key);
        return (!empty($response));
    }
}
