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
 * Translator to translate the message
 *
 * @package Aura.Intl
 *
 */
class Translator implements TranslatorInterface
{
    /**
     *
     * A fallback translator.
     *
     * @var TranslatorInterface
     *
     */
    protected $fallback;

    /**
     *
     * The formatter to use when translating messages.
     *
     * @var FormatterInterface
     *
     */
    protected $formatter;

    /**
     *
     * The locale being used for translations.
     *
     * @var string
     *
     */
    protected $locale;

    /**
     * The Package containing keys and translations.
     *
     * @var Package
     *
     */
    protected $package;

    /**
     *
     * Constructor
     *
     * @param string $locale The locale being used.
     *
     * @param Package $package The Package containing keys and translations.
     *
     * @param FormatterInterface $formatter A message formatter.
     *
     * @param TranslatorInterface $fallback A fallback translator.
     *
     */
    public function __construct(
        $locale,
        Package $package,
        FormatterInterface $formatter,
        TranslatorInterface $fallback = null
    ) {
        $this->locale    = $locale;
        $this->package   = $package;
        $this->formatter = $formatter;
        $this->fallback  = $fallback;
    }

    /**
     *
     * Gets the message translation by its key.
     *
     * @param string $key The message key.
     *
     * @return mixed The message translation string, or false if not found.
     *
     */
    protected function getMessage($key)
    {
        $message = $this->package->getMessage($key);
        if ($message) {
            return $message;
        }

        if ($this->fallback) {
            // get the message from the fallback translator
            $message = $this->fallback->getMessage($key);
            if ($message) {
                // speed optimization: retain locally
                $this->package->addMessage($key, $message);
                // done!
                return $message;
            }
        }

        // no local message, no fallback
        return false;
    }

    /**
     *
     * Translates the message indicated by they key, replacing token values
     * along the way.
     *
     * @param string $key The message key.
     *
     * @param array $tokens_values Token values to interpolate into the
     * message.
     *
     * @return string The translated message with tokens replaced.
     *
     */
    public function translate($key, array $tokens_values = [])
    {
        // get the message string
        $message = $this->getMessage($key);

        // do we have a message string?
        if (! $message) {
            // no, return the message key as-is
            $message = $key;
        }

        // are there token replacement values?
        if (empty($tokens_values)) {
            // no, return the message string as-is
            return $message;
        }

        // run message string through formatter to replace tokens with values
        return $this->formatter->format($this->locale, $message, $tokens_values);
    }

    /**
     *
     * An object of type Package
     *
     * @return Package
     *
     */
    public function getPackage()
    {
        return $this->package;
    }
}
