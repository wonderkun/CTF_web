<?php

/**
 * This file is part of TwigView.
 *
 ** (c) 2014 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace WyriHaximus\TwigView\View;

use Cake\Core\Configure;
use Cake\View\Exception\MissingLayoutException;
use Cake\View\Exception\MissingTemplateException;
use Cake\View\View;
use Exception;
use WyriHaximus\TwigView\Event\ConstructEvent;
use WyriHaximus\TwigView\Event\EnvironmentConfigEvent;
use WyriHaximus\TwigView\Event\LoaderEvent;
use WyriHaximus\TwigView\Lib\Twig\Loader;

/**
 * Class TwigView
 * @package WyriHaximus\TwigView\View
 */
// @codingStandardsIgnoreStart
class TwigView extends View
// @codingStandardsIgnoreEnd
{
    const EXT = '.twig';

    const ENV_CONFIG = 'WyriHaximus.TwigView.environment';

    /**
     * Extension to use.
     *
     * @var string
     */
    // @codingStandardsIgnoreStart
    protected $_ext = self::EXT;
    // @codingStandardsIgnoreEnd

    /**
     * @var array
     */
    protected $extensions = [
        self::EXT,
        '.tpl',
        '.ctp',
    ];

    /**
     * Twig instance.
     *
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * Initialize view.
     *
     * @return void
     */
    public function initialize()
    {
        $this->twig = new \Twig_Environment($this->getLoader(), $this->resolveConfig());

        $this->getEventManager()->dispatch(ConstructEvent::create($this, $this->twig));

        $this->_ext = self::EXT;

        parent::initialize();
    }

    /**
     * @return array
     */
    protected function resolveConfig()
    {
        $config = [
            'cache' => CACHE . 'twigView' . DS,
            'charset' => strtolower(Configure::read('App.encoding')),
            'auto_reload' => Configure::read('debug'),
            'debug' => Configure::read('debug'),
        ];

        $config = array_replace($config, $this->readConfig());

        $configEvent = EnvironmentConfigEvent::create($config);
        $this->getEventManager()->dispatch($configEvent);
        return $configEvent->getConfig();
    }

    /**
     * @return array
     */
    protected function readConfig()
    {
        if (!Configure::check(static::ENV_CONFIG)) {
            return [];
        }

        $config = Configure::read(static::ENV_CONFIG);
        if (!is_array($config)) {
            return [];
        }

        return $config;
    }

    /**
     * @param string $extension
     */
    public function unshiftExtension($extension)
    {
        array_unshift($this->extensions, $extension);
    }

    /**
     * Create the template loader.
     *
     * @return \Twig_LoaderInterface
     */
    protected function getLoader()
    {
        $event = LoaderEvent::create(new Loader());
        $this->getEventManager()->dispatch($event);
        return $event->getResultLoader();
    }

    /**
     * Get helper list.
     *
     * @return \Cake\View\Helper[]
     */
    protected function generateHelperList()
    {
        $registry = $this->helpers();
        $helperList = [];

        foreach ($registry->loaded() as $alias) {
            $helperList[$alias] = $registry->get($alias);
        }

        return $helperList;
    }
    /**
     * Render the template.
     *
     * @param string $viewFile Template file.
     * @param array  $data     Data that can be used by the template.
     *
     * @throws \Exception
     * @return string
     */
    // @codingStandardsIgnoreStart
    protected function _render($viewFile, $data = array())
    {
        // @codingStandardsIgnoreEnd
        if (empty($data)) {
            $data = $this->viewVars;
        }

        if (substr($viewFile, -3) === 'ctp') {
            $out = parent::_render($viewFile, $data);
            // @codingStandardsIgnoreStart
        } else {
            // @codingStandardsIgnoreEnd
            $data = array_merge(
                $data,
                $this->generateHelperList(),
                [
                    '_view' => $this,
                ]
            );

            // @codingStandardsIgnoreStart
            try {
                $out = $this->getTwig()->loadTemplate($viewFile)->render($data);
            } catch (Exception $e) {
                $previous = $e->getPrevious();

                if ($previous !== null && $previous instanceof Exception) {
                    throw $previous;
                } else {
                    throw $e;
                }
            }
            // @codingStandardsIgnoreEnd
        }

        return $out;
    }

    /**
     * @param string|null $name
     * @return string
     * @throws \Exception
     */
    // @codingStandardsIgnoreStart
    protected function _getViewFileName($name = null)
    {
        // @codingStandardsIgnoreEnd
        $templatePath = $subDir = '';

        if ($this->subDir !== null) {
            $subDir = $this->subDir . DIRECTORY_SEPARATOR;
        }
        if ($this->templatePath) {
            $templatePath = $this->templatePath . DIRECTORY_SEPARATOR;
        }

        if ($name === null) {
            $name = $this->template;
        }

        list($plugin, $name) = $this->pluginSplit($name);
        $name = str_replace('/', DIRECTORY_SEPARATOR, $name);

        if (strpos($name, DIRECTORY_SEPARATOR) === false && $name[0] !== '.') {
            $name = $templatePath . $subDir . $this->_inflectViewFileName($name);
        } elseif (strpos($name, DIRECTORY_SEPARATOR) !== false) {
            if ($name[0] === DIRECTORY_SEPARATOR || $name[1] === ':') {
                $name = trim($name, DIRECTORY_SEPARATOR);
            } elseif (!$plugin || $this->templatePath !== $this->name) {
                $name = $templatePath . $subDir . $name;
            } else {
                $name = DIRECTORY_SEPARATOR . $subDir . $name;
            }
        }

        foreach ($this->_paths($plugin) as $path) {
            foreach ($this->extensions as $extension) {
                if (file_exists($path . $name . $extension)) {
                    return $this->_checkFilePath($path . $name . $extension, $path);
                }
            }
        }

        throw new MissingTemplateException(['file' => $name . $this->_ext]);
    }

    /**
     * @param string|null $name
     * @return string
     * @throws \Exception
     */
    // @codingStandardsIgnoreStart
    protected function _getLayoutFileName($name = null)
    {
        // @codingStandardsIgnoreEnd
        if ($name === null) {
            $name = $this->layout;
        }
        $subDir = null;

        if ($this->layoutPath) {
            $subDir = $this->layoutPath . DIRECTORY_SEPARATOR;
        }
        list($plugin, $name) = $this->pluginSplit($name);

        $layoutPaths = $this->_getSubPaths('Layout' . DIRECTORY_SEPARATOR . $subDir);

        foreach ($this->_paths($plugin) as $path) {
            foreach ($layoutPaths as $layoutPath) {
                $currentPath = $path . $layoutPath;
                foreach ($this->extensions as $extension) {
                    if (file_exists($currentPath . $name . $extension)) {
                        return $this->_checkFilePath($currentPath . $name . $extension, $currentPath);
                    }
                }
            }
        }
        throw new MissingLayoutException([
            'file' => $layoutPaths[0] . $name . $this->_ext
        ]);
    }

    /**
     * @param string $name
     * @param bool $pluginCheck
     * @return string
     * @throws \Exception
     */
    // @codingStandardsIgnoreStart
    protected function _getElementFileName($name, $pluginCheck = true)
    {
        // @codingStandardsIgnoreEnd
        list($plugin, $name) = $this->pluginSplit($name, $pluginCheck);

        $paths = $this->_paths($plugin);
        $elementPaths = $this->_getSubPaths('Element');

        foreach ($paths as $path) {
            foreach ($elementPaths as $elementPath) {
                foreach ($this->extensions as $extension) {
                    if (file_exists($path . $elementPath . DIRECTORY_SEPARATOR . $name . $extension)) {
                        return $path . $elementPath . DIRECTORY_SEPARATOR . $name . $extension;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Get twig environment instance.
     *
     * @return \Twig_Environment
     */
    public function getTwig()
    {
        return $this->twig;
    }

    /**
     * Return empty string when View instance is cast to string.
     *
     * @return string
     */
    public function __toString()
    {
        return '';
    }
}
