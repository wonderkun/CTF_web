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
 * Creates new package instances.
 *
 * @package Aura.Intl
 *
 */
class PackageFactory
{
    /**
     *
     * Returns a new Package instance.
     *
     * @param array $info An array of package information with keys
     * 'formatter', 'fallback', and 'messages'. Typically read from an
     * `intl/xx_XX.php` file.
     *
     * @return Package
     *
     */
    public function newInstance(array $info)
    {
        $package = new Package;
        if (isset($info['fallback'])) {
            $package->setFallback($info['fallback']);
        }
        if (isset($info['formatter'])) {
            $package->setFormatter($info['formatter']);
        }
        if (isset($info['messages'])) {
            $package->setMessages($info['messages']);
        }
        return $package;
    }
}
