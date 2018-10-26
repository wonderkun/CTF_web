<?php
/**
 *
 * This file is part of the Aura Project for PHP.
 *
 * @package Aura.Intl
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Aura\Intl;

/**
 *
 * Formatter Interface
 *
 * @package Aura.Intl
 *
 */
class TranslatorLocatorFactory
{
    /**
     *
     * Returns a new TranslatorLocator.
     *
     * @return TranslatorLocator
     *
     */
    public function newInstance()
    {
        return new TranslatorLocator(
            new PackageLocator,
            new FormatterLocator([
                'basic' => function () {
                    return new \Aura\Intl\BasicFormatter;
                },
                'intl'  => function () {
                    return new \Aura\Intl\IntlFormatter;
                },
            ]),
            new TranslatorFactory,
            'en_US'
        );
    }
}
