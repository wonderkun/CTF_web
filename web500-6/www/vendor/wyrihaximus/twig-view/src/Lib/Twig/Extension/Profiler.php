<?php

/**
 * This file is part of TwigView.
 *
 ** (c) 2014 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace WyriHaximus\TwigView\Lib\Twig\Extension;

use DebugKit\DebugTimer;

/**
 * Class Basic
 * @package WyriHaximus\TwigView\Lib\Twig\Extension
 */
class Profiler extends \Twig_Extension_Profiler
{
    /**
     * Enter $profile.
     *
     * @param \Twig_Profiler_Profile $profile Profile.
     *
     * @return void
     */
    public function enter(\Twig_Profiler_Profile $profile)
    {
        $name = 'Twig Template: ' . substr($profile->getName(), strlen(ROOT) + 1);
        DebugTimer::start($name, __d('twig_view', $name));

        parent::enter($profile);
    }

    /**
     * Leave $profile.
     *
     * @param \Twig_Profiler_Profile $profile Profile.
     *
     * @return void
     */
    public function leave(\Twig_Profiler_Profile $profile)
    {
        parent::leave($profile);

        $name = 'Twig Template: ' . substr($profile->getName(), strlen(ROOT) + 1);
        DebugTimer::stop($name);
    }
}
