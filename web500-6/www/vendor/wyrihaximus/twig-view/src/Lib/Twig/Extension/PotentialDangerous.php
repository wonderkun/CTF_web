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
class PotentialDangerous extends \Twig_Extension
{

    /**
     * Get declared filters.
     *
     * @return \Twig_SimpleFilter[]
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('env', 'env'),
        ];
    }

    /**
     * Get declared functions.
     *
     * @return \Twig_SimpleFunction[]
     */
    public function getFunctions()
    {
        return [
        new \Twig_SimpleFunction('config', 'Cake\Core\Configure::read'),
        ];
    }

    /**
     * Get extension name.
     *
     * @return string
     */
    public function getName()
    {
        return 'potential_dangerous';
    }
}
