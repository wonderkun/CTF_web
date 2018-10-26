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
 * TranslatorInterface
 *
 * @package Aura.Intl
 *
 */
interface TranslatorInterface
{
    /**
     *
     * translate
     *
     * @param string $key
     *
     * @param array $tokens_values
     *
     */
    public function translate($key, array $tokens_values = []);
}
