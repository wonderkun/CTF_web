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

use Cake\Utility\Text;

/**
 * Class Strings
 * @package WyriHaximus\TwigView\Lib\Twig\Extension
 */
class Strings extends \Twig_Extension
{

    /**
     * Get declared filters.
     *
     * @return \Twig_SimpleFilter[]
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('substr', 'substr'),
            new \Twig_SimpleFilter('tokenize', 'Cake\Utility\Text::tokenize'),
            new \Twig_SimpleFilter('insert', 'Cake\Utility\Text::insert'),
            new \Twig_SimpleFilter('cleanInsert', 'Cake\Utility\Text::cleanInsert'),
            new \Twig_SimpleFilter('wrap', 'Cake\Utility\Text::wrap'),
            new \Twig_SimpleFilter('wrapBlock', 'Cake\Utility\Text::wrapBlock'),
            new \Twig_SimpleFilter('wordWrap', 'Cake\Utility\Text::wordWrap'),
            new \Twig_SimpleFilter('highlight', 'Cake\Utility\Text::highlight'),
            new \Twig_SimpleFilter('tail', 'Cake\Utility\Text::tail'),
            new \Twig_SimpleFilter('truncate', 'Cake\Utility\Text::truncate'),
            new \Twig_SimpleFilter('excerpt', 'Cake\Utility\Text::excerpt'),
            new \Twig_SimpleFilter('toList', 'Cake\Utility\Text::toList'),
            new \Twig_SimpleFilter('stripLinks', function ($string) {
                $previousrErrorHandler = set_error_handler(function () {
                });
                $strippedString = Text::stripLinks($string);
                set_error_handler($previousrErrorHandler);
                return $strippedString;
            }),
            new \Twig_SimpleFilter('isMultibyte', 'Cake\Utility\Text::isMultibyte'),
            new \Twig_SimpleFilter('utf8', 'Cake\Utility\Text::utf8'),
            new \Twig_SimpleFilter('ascii', 'Cake\Utility\Text::ascii'),
            new \Twig_SimpleFilter('parseFileSize', 'Cake\Utility\Text::parseFileSize'),
            new \Twig_SimpleFilter('none', function ($string) {
                return;
            }),
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
            new \Twig_SimpleFunction('uuid', 'Cake\Utility\Text::uuid'),
        ];
    }

    /**
     * Get extension name.
     *
     * @return string
     */
    public function getName()
    {
        return 'string';
    }
}
