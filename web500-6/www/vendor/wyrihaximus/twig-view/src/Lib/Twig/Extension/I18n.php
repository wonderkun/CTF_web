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
 * Class I18n
 * @package WyriHaximus\TwigView\Lib\Twig\Extension
 */
class I18n extends \Twig_Extension
{

    /**
     * Get declared functions.
     *
     * @return \Twig_SimpleFunction[]
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('__', '__'),
            new \Twig_SimpleFunction('__d', '__d'),
            new \Twig_SimpleFunction('__n', '__n'),
            new \Twig_SimpleFunction('__x', '__x'),
            new \Twig_SimpleFunction('__dn', '__dn'),
        ];
    }

    /**
     * Get extension name.
     *
     * @return string
     */
    public function getName()
    {
        return 'i18n';
    }
}
