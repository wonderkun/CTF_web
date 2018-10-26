<?php

/**
 * This file is part of TwigView.
 *
 ** (c) 2015 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace WyriHaximus\TwigView\Event;

use Cake\Event\Event;

class EnvironmentConfigEvent extends Event
{
    const EVENT = 'TwigView.TwigView.environment';

    /**
     * @var array
     */
    private $config = [];

    /**
     * @param array $config
     *
     * @return static
     */
    public static function create(array $config)
    {
        $event = new static(static::EVENT);
        $event->setConfig($config);
        return $event;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->config = array_replace_recursive($this->config, $config);
    }
}
