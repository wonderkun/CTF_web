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
use WyriHaximus\TwigView\View\TwigView;

class ConstructEvent extends Event
{
    const EVENT = 'TwigView.TwigView.construct';

    /**
     * @param TwigView $twigView
     * @param \Twig_Environment $twig
     * @return static
     */
    public static function create(TwigView $twigView, \Twig_Environment $twig)
    {
        return new static(static::EVENT, $twigView, [
            'twigView' => $twigView,
            'twig' => $twig,
        ]);
    }

    /**
     * @return TwigView
     */
    public function getTwigView()
    {
        return $this->getData()['twigView'];
    }

    /**
     * @return \Twig_Environment
     */
    public function getTwig()
    {
        return $this->getData()['twig'];
    }
}
