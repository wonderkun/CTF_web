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
 * Class Utils
 * @package WyriHaximus\TwigView\Lib\Twig\Extension
 */
class Utils extends \Twig_Extension
{

    /**
     * Get declared filters.
     *
     * @return \Twig_SimpleFilter[]
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('serialize', 'serialize'),
            new \Twig_SimpleFilter('unserialize', 'unserialize'),
            new \Twig_SimpleFilter('md5', 'md5'),
            new \Twig_SimpleFilter('base64_encode', 'base64_encode'),
            new \Twig_SimpleFilter('base64_decode', 'base64_decode'),
            new \Twig_SimpleFilter('nl2br', 'nl2br'),
            new \Twig_SimpleFilter('string', function ($str) {
                return (string)$str;
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
        return 'utils';
    }
}
