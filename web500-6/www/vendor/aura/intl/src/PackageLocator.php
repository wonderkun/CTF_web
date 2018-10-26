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
 * A ServiceLocator implementation for loading and retaining translator objects.
 *
 * @package Aura.Intl
 *
 */
class PackageLocator implements PackageLocatorInterface
{
    /**
     *
     * A registry of packages.
     *
     * Unlike many other registries, this one is two layers deep. The first
     * key is a package name, the second key is a locale code, and the value
     * is a callable that returns a Package object for that name and locale.
     *
     * @var array
     *
     */
    protected $registry = [];

    /**
     *
     * Tracks whether or not a registry entry has been converted from a
     * callable to a Package object.
     *
     * @var array
     *
     */
    protected $converted = [];

    /**
     *
     * Constructor.
     *
     * @param array $registry A registry of packages.
     *
     * @see $registry
     *
     */
    public function __construct(array $registry = [])
    {
        foreach ($registry as $name => $locales) {
            foreach ($locales as $locale => $spec) {
                $this->set($name, $locale, $spec);
            }
        }
    }

    /**
     *
     * Sets a Package object.
     *
     * @param string $name The package name.
     *
     * @param string $locale The locale for the package.
     *
     * @param callable $spec A callable that returns a package.
     *
     * @return void
     *
     */
    public function set($name, $locale, callable $spec)
    {
        $this->registry[$name][$locale] = $spec;
        $this->converted[$name][$locale] = false;
    }

    /**
     *
     * Gets a Package object.
     *
     * @param string $name The package name.
     *
     * @param string $locale The locale for the package.
     *
     * @return Package
     *
     */
    public function get($name, $locale)
    {
        if (! isset($this->registry[$name][$locale])) {
            throw new Exception("Package '$name' with locale '$locale' is not registered.");
        }

        if (! $this->converted[$name][$locale]) {
            $func = $this->registry[$name][$locale];
            $this->registry[$name][$locale] = $func();
            $this->converted[$name][$locale] = true;
        }

        return $this->registry[$name][$locale];
    }
}
