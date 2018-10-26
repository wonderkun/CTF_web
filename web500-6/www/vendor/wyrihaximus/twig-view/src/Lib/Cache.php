<?php

namespace WyriHaximus\TwigView\Lib;

use Asm89\Twig\CacheExtension\CacheProviderInterface;
use Cake\Cache\Cache as CakeCache;

class Cache implements CacheProviderInterface
{
    const CACHE_PREFIX = 'twig-view-in-template-item-';

    /**
     * Retrieve data from the cache.
     *
     * @param string $identifier Identifier for this bit of data to read.
     *
     * @return mixed
     */
    public function fetch($identifier)
    {
        list($config, $key) = $this->configSplit($identifier);

        return CakeCache::read(static::CACHE_PREFIX . $key, $config);
    }

    /**
     * Extract $configName and $key from $name and $config.
     *
     * @param string $name   Name.
     * @param string $config Cache configuration name to used.
     *
     * @return array
     */
    protected function configSplit($name, $config = 'default')
    {
        if (strpos($name, ':') !== false) {
            $parts = explode(':', $name, 2);
            return $parts;
        }

        return [$config, $name];
    }

    /**
     * Save data to the cache.
     *
     * @param string  $identifier Identifier for this bit of data to write.
     * @param string  $data       Data to cache.
     * @param integer $lifeTime   Time to life inside the cache.
     *
     * @return boolean
     */
    public function save($identifier, $data, $lifeTime = 0)
    {
        list($config, $key) = $this->configSplit($identifier);

        return CakeCache::write(static::CACHE_PREFIX . $key, $data, $config);
    }
}
