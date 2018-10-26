<?php

namespace WyriHaximus\TwigView\Lib;

use Cake\Core\App;
use Cake\Core\Plugin;
use WyriHaximus\TwigView\View\TwigView;

/**
 * Class Scanner
 * @package WyriHaximus\TwigView\Lib
 */
class Scanner
{
    /**
     * Return all sections (app & plugins) with an Template directory.
     *
     * @return array
     */
    // @codingStandardsIgnoreStart
    public static function all()
    {
        // @codingStandardsIgnoreEnd
        $sections = [];

        foreach (App::path('Template') as $path) {
            if (is_dir($path)) {
                $sections['APP'] = isset($sections['APP']) ? $sections['APP'] : [];
                $sections['APP'] = array_merge($sections['APP'], static::iteratePath($path));
            }
        }

        foreach (static::pluginsWithTemplates() as $plugin) {
            foreach (App::path('Template', $plugin) as $path) {
                if (is_dir($path)) {
                    $sections[$plugin] = isset($sections[$plugin]) ? $sections[$plugin] : [];
                    $sections[$plugin] = array_merge($sections[$plugin], static::iteratePath($path));
                }
            }
        }

        return static::clearEmptySections($sections);
    }

    /**
     * Check sections a remove the ones without anything in them.
     *
     * @param array $sections Sections to check.
     *
     * @return array
     */
    protected static function clearEmptySections(array $sections)
    {
        array_walk($sections, function ($templates, $index) use (&$sections) {
            if (count($templates) == 0) {
                unset($sections[$index]);
            }
        });

        return $sections;
    }

    /**
     * Finds all plugins with a Template directory.
     *
     * @return array
     */
    protected static function pluginsWithTemplates()
    {
        $plugins = Plugin::loaded();

        array_walk($plugins, function ($plugin, $index) use (&$plugins) {
            $paths = App::path('Template', $plugin);

            array_walk($paths, function ($path, $index) use (&$paths) {
                if (!is_dir($path)) {
                    unset($paths[$index]);
                }
            });
        });

        return $plugins;
    }

    /**
     * Return all templates for a given plugin.
     *
     * @param string $plugin The plugin to find all templates for.
     *
     * @return mixed
     */
    public static function plugin($plugin)
    {
        $templates = [];

        foreach (App::path('Template', $plugin) as $path) {
            $templates = array_merge($templates, static::iteratePath($path));
        }

        return $templates;
    }

    /**
     * Iterage over the given path and return all matching .tpl files in it.
     *
     * @param string $path Path to iterate over.
     *
     * @return array
     */
    protected static function iteratePath($path)
    {
        return static::walkIterator(static::setupIterator($path));
    }

    /**
     * Setup iterator for given path.
     *
     * @param string $path Path to setup iterator for.
     *
     * @return \Iterator
     */
    protected static function setupIterator($path)
    {
        return new \RegexIterator(new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $path,
                \FilesystemIterator::KEY_AS_PATHNAME |
                \FilesystemIterator::CURRENT_AS_FILEINFO |
                \FilesystemIterator::SKIP_DOTS
            ),
            \RecursiveIteratorIterator::CHILD_FIRST,
            \RecursiveIteratorIterator::CATCH_GET_CHILD
        ), '/.*?' . TwigView::EXT . '$/', \RegexIterator::GET_MATCH);
    }

    /**
     * Walk over the iterator and compile all templates.
     *
     * @param \Iterator $iterator Iterator to walk.
     *
     * @return array
     */
    // @codingStandardsIgnoreStart
    protected static function walkIterator(\Iterator $iterator)
    {
        $items = [];

        $array = iterator_to_array($iterator);
        uasort($array, function ($a, $b) {
            if ($a == $b) {
                return 0;
            }

            return ($a < $b) ? -1 : 1;
        });

        foreach ($array as $paths) {
            foreach ($paths as $path) {
                $items[] = $path;
            }
        }

        return $items;
    }
    // @codingStandardsIgnoreEnd
}
