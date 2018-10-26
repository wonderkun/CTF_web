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

/**
 * Class Basic
 * @package WyriHaximus\TwigView\Lib\Twig\Extension
 */
class Basic extends \Twig_Extension
{

    /**
     * Get declared filters.
     *
     * @return \Twig_SimpleFilter[]
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('debug', 'debug'),
            new \Twig_SimpleFilter('pr', 'pr'),
            new \Twig_SimpleFilter('low', 'low'),
            new \Twig_SimpleFilter('up', 'up'),
            new \Twig_SimpleFilter('count', 'count'),
            new \Twig_SimpleFilter('h', 'h'),
            new \Twig_SimpleFilter('null', function () {
                return '';
            }),
        ];
    }

    /**
     * Get extension name.
     *
     * @return string
     */
    public function getName()
    {
        return 'basic';
    }
}
