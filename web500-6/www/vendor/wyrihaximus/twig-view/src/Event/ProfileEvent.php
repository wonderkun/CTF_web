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

class ProfileEvent extends Event
{
    const EVENT = 'TwigView.TwigView.profile';

    /**
     * @param \Twig_Profiler_Profile $profile
     * @return static
     */
    public static function create(\Twig_Profiler_Profile $profile)
    {
        return new static(static::EVENT, $profile);
    }

    /**
     * @return \Twig_Profiler_Profile
     */
    public function getLoader()
    {
        return $this->getSubject();
    }
}
