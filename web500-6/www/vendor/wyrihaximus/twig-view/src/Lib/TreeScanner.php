<?php

namespace WyriHaximus\TwigView\Lib;

/**
 * Class TreeScanner
 * @package WyriHaximus\TwigView\Lib
 */
class TreeScanner
{
    /**
     * Return all sections (app & plugins) with an Template directory.
     *
     * @return array
     */
    public static function all()
    {
        return static::deepen(RelativeScanner::all());
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
        return static::deepen([
            $plugin => RelativeScanner::plugin($plugin),
        ])[$plugin];
    }

    /**
     * Strip the absolute path of template's paths for all given sections.
     *
     * @param string $sections Sections to iterate over.
     *
     * @return array
     */
    protected static function deepen($sections)
    {
        foreach ($sections as $section => $paths) {
            $sections[$section] = static::convertToTree($paths);
        }
        return $sections;
    }

    /**
     * Turn a set of paths into a tree.
     *
     * @param array $paths Paths to turn into a tree.
     *
     * @return array
     */
    protected static function convertToTree(array $paths)
    {
        foreach ($paths as $index => $path) {
            static::convertPathToTree($paths, $index, $path);
        }

        return $paths;
    }

    /**
     * Convert a path into a tree when it contains a directory separator.
     *
     * @param array  $paths The paths to work on.
     * @param mixed  $index Index of $path.
     * @param string $path  Path to breakup and turn into a tree.
     *
     * @return void
     */
    protected static function convertPathToTree(array &$paths, $index, $path)
    {
        if (strpos($path, DIRECTORY_SEPARATOR) !== false) {
            $chunks = explode(DIRECTORY_SEPARATOR, $path);
            $paths = static::branch($paths, $chunks);
            unset($paths[$index]);
        }
    }

    /**
     * Create a branch for the current level and push a twig on it.
     *
     * @param array $paths    Paths to append.
     * @param array $branches Branches to use untill only one left.
     *
     * @return array
     */
    protected static function branch(array $paths, array $branches)
    {
        $twig = array_shift($branches);
        if (count($branches) == 0) {
            $paths[] = $twig;
            return $paths;
        }

        if (!isset($paths[$twig])) {
            $paths[$twig] = [];
        }

        $paths[$twig] = static::branch($paths[$twig], $branches);

        return $paths;
    }
}
