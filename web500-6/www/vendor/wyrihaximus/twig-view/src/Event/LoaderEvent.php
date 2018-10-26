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

class LoaderEvent extends Event
{
    const EVENT = 'TwigView.TwigView.loader';

    /**
     * @param \Twig_LoaderInterface $loader
     * @return LoaderEvent
     */
    public static function create(\Twig_LoaderInterface $loader)
    {
        return new static(static::EVENT, $loader, [
            'loader' => $loader,
        ]);
    }

    /**
     * @return \Twig_LoaderInterface
     */
    public function getLoader()
    {
        return $this->getSubject();
    }

    /**
     * @return string|Twig_LoaderInterface
     */
    public function getResultLoader()
    {
        if ($this->result instanceof \Twig_LoaderInterface) {
            return $this->result;
        }

        if (is_array($this->result) && $this->result['loader'] instanceof \Twig_LoaderInterface) {
            return $this->result['loader'];
        }

        return $this->getLoader();
    }
}
