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
 * Factory to create Translator objects.
 *
 * @package Aura.Intl
 *
 */
class TranslatorFactory
{
    /**
     *
     * The class to use for new instances.
     *
     * @var string
     *
     */
    protected $class = Translator::class;

    /**
     *
     * Returns a new Translator.
     *
     * @param string $locale The locale code for the translator.
     *
     * @param Package $package The localized package for the translator.
     *
     * @param FormatterInterface $formatter The formatter to use for
     * interpolating token values.
     *
     * @param TranslatorInterface $fallback A fallback translator to use, if
     * any.
     *
     * @return Translator
     *
     */
    public function newInstance(
        $locale,
        Package $package,
        FormatterInterface $formatter,
        TranslatorInterface $fallback = null
    ) {
        $class = $this->class;
        return new $class($locale, $package, $formatter, $fallback);
    }
}
