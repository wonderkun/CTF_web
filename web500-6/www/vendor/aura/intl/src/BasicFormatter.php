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
 * BasicFormatter
 *
 * @package Aura.Intl
 *
 */
class BasicFormatter implements FormatterInterface
{
    /**
     *
     * Format message
     *
     * @param string $locale
     *
     * @param string $string
     *
     * @param array $tokens_values
     *
     * @return string A string replaced with the token values
     *
     */
    public function format($locale, $string, array $tokens_values)
    {
        $replace = [];
        foreach ($tokens_values as $token => $value) {
            // convert an array to a CSV string
            if (is_array($value)) {
                $value = '"' . implode('", "', $value) . '"';
            }
            $replace['{' . $token . '}'] = $value;
        }
        return strtr($string, $replace);
    }
}
