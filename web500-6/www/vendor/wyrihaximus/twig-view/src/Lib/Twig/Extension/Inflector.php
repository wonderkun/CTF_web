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
 * Class Inflector
 * @package WyriHaximus\TwigView\Lib\Twig\Extension
 */
class Inflector extends \Twig_Extension
{

    /**
     * Get filters for this extension.
     *
     * @return \Twig_SimpleFunction[]
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('pluralize', 'Cake\Utility\Inflector::pluralize'),
            new \Twig_SimpleFilter('singularize', 'Cake\Utility\Inflector::singularize'),
            new \Twig_SimpleFilter('camelize', 'Cake\Utility\Inflector::camelize'),
            new \Twig_SimpleFilter('underscore', 'Cake\Utility\Inflector::underscore'),
            new \Twig_SimpleFilter('humanize', 'Cake\Utility\Inflector::humanize'),
            new \Twig_SimpleFilter('tableize', 'Cake\Utility\Inflector::tableize'),
            new \Twig_SimpleFilter('classify', 'Cake\Utility\Inflector::classify'),
            new \Twig_SimpleFilter('variable', 'Cake\Utility\Inflector::variable'),
            new \Twig_SimpleFilter('slug', 'Cake\Utility\Text::slug'),
        ];
    }

    /**
     * Get extension name.
     *
     * @return string
     */
    public function getName()
    {
        return 'inflector';
    }
}
