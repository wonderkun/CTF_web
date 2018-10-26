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
 * Class Number
 * @package WyriHaximus\TwigView\Lib\Twig\Extension
 */
class Number extends \Twig_Extension
{

    /**
     * Get declared functions.
     *
     * @return \Twig_SimpleFilter[]
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('toReadableSize', 'Cake\I18n\Number::toReadableSize'),
            new \Twig_SimpleFilter('fromReadableSize', 'Cake\I18n\Number::fromReadableSize'),
            new \Twig_SimpleFilter('toPercentage', 'Cake\I18n\Number::toPercentage'),
            new \Twig_SimpleFilter('number_format', 'Cake\I18n\Number::format'),
            new \Twig_SimpleFilter('formatDelta', 'Cake\I18n\Number::formatDelta'),
            new \Twig_SimpleFilter('currency', 'Cake\I18n\Number::currency'),
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
            new \Twig_SimpleFunction('defaultCurrency', 'Cake\I18n\Number::defaultCurrency'),
            new \Twig_SimpleFunction('number_formatter', 'Cake\I18n\Number::formatter'),
        ];
    }

    /**
     * Get extension name.
     *
     * @return string
     */
    public function getName()
    {
        return 'number';
    }
}
