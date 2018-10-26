<?php

/**
 * This file is part of TwigView.
 *
 ** (c) 2014 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace WyriHaximus\TwigView\Lib\Twig;

use Cake\Core\App;
use Cake\Core\Plugin;
use Twig_Error_Loader;
use Twig_Source;
use WyriHaximus\TwigView\View\TwigView;

/**
 * Class Loader
 * @package WyriHaximus\TwigView\Lib\Twig
 */
class Loader implements \Twig_LoaderInterface, \Twig_ExistsLoaderInterface, \Twig_SourceContextLoaderInterface
{

    /**
     * Get the file contents of a template.
     *
     * @param string $name Template.
     *
     * @return string
     */
    public function getSource($name)
    {
        $name = $this->resolveFileName($name);
        return file_get_contents($name);
    }

    /**
     * Get cache key for template.
     *
     * @param string $name Template.
     *
     * @return string
     */
    public function getCacheKey($name)
    {
        return $this->resolveFileName($name);
    }

    /**
     * Check if template is still fresh.
     *
     * @param string  $name Template.
     * @param integer $time Timestamp.
     *
     * @return boolean
     */
    public function isFresh($name, $time)
    {
        $name = $this->resolveFileName($name);
        return filemtime($name) < $time;
    }

    /**
     * Resolve template name to filename.
     *
     * @param string $name Template.
     *
     * @return string
     *
     * @throws \Twig_Error_Loader Thrown when template file isn't found.
     */
    // @codingStandardsIgnoreStart
    protected function resolveFileName($name)
    {
        // @codingStandardsIgnoreEnd
        if (file_exists($name)) {
            return $name;
        }

        list($plugin, $file) = pluginSplit($name);
        foreach ([
            null,
            $plugin,
        ] as $scope) {
            $paths = $this->getPaths($scope);
            foreach ($paths as $path) {
                $filePath = $path . $file;
                if (file_exists($filePath)) {
                    return $filePath;
                }

                $filePath = $path . $file . TwigView::EXT;
                if (file_exists($filePath)) {
                    return $filePath;
                }
            }
        }

        throw new \Twig_Error_Loader(sprintf('Template "%s" is not defined.', $name));
    }

    /**
     * Check if $plugin is active and return it's template paths or return the aps template paths.
     *
     * @param string|null $plugin The plugin in question.
     *
     * @return array
     */
    protected function getPaths($plugin)
    {
        if ($plugin === null || !Plugin::loaded($plugin)) {
            return App::path('Template');
        }

        return App::path('Template', $plugin);
    }

    public function exists($name)
    {
        $name = $this->resolveFileName($name);

        return file_exists($name);
    }

    public function getSourceContext($name)
    {
        $path = $this->resolveFileName($name);

        return new Twig_Source(file_get_contents($path), $name, $path);
    }
}
