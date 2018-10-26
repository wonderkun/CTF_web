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

use MessageFormatter;
use Aura\Intl\Exception;

/**
 *
 * Uses php intl extension to format messages
 *
 * @package Aura.Intl
 *
 */
class IntlFormatter implements FormatterInterface
{
    /**
     *
     * Constructor.
     *
     * @param string $icu_version The current ICU version; mostly used for
     * testing.
     *
     * @throws Exception\IcuVersionTooLow when the Version of ICU installed
     * is too low for Aura.Intl to work properly.
     *
     */
    public function __construct($icu_version = INTL_ICU_VERSION)
    {
        if (version_compare($icu_version, '4.8') < 0) {
            throw new Exception\IcuVersionTooLow('ICU Version 4.8 or higher required.');
        }
    }

    /**
     *
     * Format the message with the help of php intl extension
     *
     * @param string $locale
     * @param string $string
     * @param array $tokens_values
     * @return string
     * @throws Exception
     */
    public function format($locale, $string, array $tokens_values)
    {
        $values = [];
        foreach ($tokens_values as $token => $value) {
            // convert an array to a CSV string
            if (is_array($value)) {
                $value = '"' . implode('", "', $value) . '"';
            }

            $values[$token] = $value;
        }

        try {
            $formatter = new MessageFormatter($locale, $string);
            if (! $formatter) {
                $this->throwCannotInstantiateFormatter();
            }
        } catch (\Exception $e) {
            $this->throwCannotInstantiateFormatter();
        }

        $result = $formatter->format($values);
        if ($result === false) {
            throw new Exception\CannotFormat(
                $formatter->getErrorMessage(),
                $formatter->getErrorCode()
            );
        }

        return $result;
    }

    /**
     *
     * Throws exception
     *
     * @throws Exception\CannotInstantiateFormatter
     */
    protected function throwCannotInstantiateFormatter()
    {
        throw new Exception\CannotInstantiateFormatter(
            intl_get_error_message(),
            intl_get_error_code()
        );
    }
}
