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
 * Class Arrays
 * @package WyriHaximus\TwigView\Lib\Twig\Extension
 */
class Arrays extends \Twig_Extension
{

    /**
     * Get declared functions.
     *
     * @return \Twig_SimpleFunction[]
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('in_array', 'in_array'),
            new \Twig_SimpleFunction('explode', 'explode'),
            new \Twig_SimpleFunction('array', function ($array) {
                return (array)$array;
            }),
            new \Twig_SimpleFunction('array_push', 'push'),
            new \Twig_SimpleFunction('array_add', 'add'),
            new \Twig_SimpleFunction('array_prev', 'prev'),
            new \Twig_SimpleFunction('array_next', 'next'),
            new \Twig_SimpleFunction('array_current', 'current'),
            new \Twig_SimpleFunction('array_each', 'each'),
        ];
    }

    /**
     * Get extension name.
     *
     * @return string
     */
    public function getName()
    {
        return 'array';
    }
}
