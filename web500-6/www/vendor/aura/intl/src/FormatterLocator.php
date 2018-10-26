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
 * A ServiceLocator implementation for loading and retaining formatter objects.
 *
 * @package Aura.Intl
 *
 */
class FormatterLocator
{
    /**
     *
     * A registry to retain formatter objects.
     *
     * @var array
     *
     */
    protected $registry;

    /**
     *
     * Tracks whether or not a registry entry has been converted from a
     * callable to a formatter object.
     *
     * @var array
     *
     */
    protected $converted = [];

    /**
     *
     * Constructor.
     *
     * @param array $registry An array of key-value pairs where the key is the
     * formatter name the value is a callable that returns a formatter object.
     *
     */
    public function __construct(array $registry = [])
    {
        foreach ($registry as $name => $spec) {
            $this->set($name, $spec);
        }
    }

    /**
     *
     * Sets a formatter into the registry by name.
     *
     * @param string $name The formatter name.
     *
     * @param callable $spec A callable that returns a formatter object.
     *
     * @return void
     *
     */
    public function set($name, $spec)
    {
        $this->registry[$name] = $spec;
        $this->converted[$name] = false;
    }

    /**
     *
     * Gets a formatter from the registry by name.
     *
     * @param string $name The formatter to retrieve.
     *
     * @return FormatterInterface A formatter object.
     * 
     * @throws Exception\FormatterNotMapped
     *
     */
    public function get($name)
    {
        if (! isset($this->registry[$name])) {
            throw new Exception\FormatterNotMapped($name);
        }

        if (! $this->converted[$name]) {
            $func = $this->registry[$name];
            $this->registry[$name] = $func();
            $this->converted[$name] = true;
        }

        return $this->registry[$name];
    }
}
