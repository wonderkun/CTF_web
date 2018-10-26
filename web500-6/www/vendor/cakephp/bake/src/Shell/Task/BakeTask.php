<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         0.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Shell\Task;

use Bake\Utility\CommonOptionsTrait;
use Cake\Cache\Cache;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Core\ConventionsTrait;
use Cake\Filesystem\File;

/**
 * Base class for Bake Tasks.
 *
 */
class BakeTask extends Shell
{
    use CommonOptionsTrait;
    use ConventionsTrait;

    /**
     * Table prefix
     *
     * @var string|null
     */
    public $tablePrefix = null;

    /**
     * The pathFragment appended to the plugin/app path.
     *
     * @var string
     */
    public $pathFragment;

    /**
     * Name of plugin
     *
     * @var string
     */
    public $plugin = null;

    /**
     * The db connection being used for baking
     *
     * @var string
     */
    public $connection = null;

    /**
     * Disable caching and enable debug for baking.
     * This forces the most current database schema to be used.
     *
     * @return void
     */
    public function startup()
    {
        Configure::write('debug', true);
        Cache::disable();
    }

    /**
     * Initialize hook.
     *
     * Populates the connection property, which is useful for tasks of tasks.
     *
     * @return void
     */
    public function initialize()
    {
        if (empty($this->connection) && !empty($this->params['connection'])) {
            $this->connection = $this->params['connection'];
        }
    }

    /**
     * Get the prefix name.
     *
     * Handles camelcasing each namespace in the prefix path.
     *
     * @return string The inflected prefix path.
     */
    protected function _getPrefix()
    {
        $prefix = $this->param('prefix');
        if (!$prefix) {
            return '';
        }
        $parts = explode('/', $prefix);

        return implode('/', array_map([$this, '_camelize'], $parts));
    }

    /**
     * Gets the path for output. Checks the plugin property
     * and returns the correct path.
     *
     * @return string Path to output.
     */
    public function getPath()
    {
        $path = APP . $this->pathFragment;
        if (isset($this->plugin)) {
            $path = $this->_pluginPath($this->plugin) . 'src/' . $this->pathFragment;
        }
        $prefix = $this->_getPrefix();
        if ($prefix) {
            $path .= $prefix . DS;
        }

        return str_replace('/', DS, $path);
    }

    /**
     * Base execute method parses some parameters and sets some properties on the bake tasks.
     * call when overriding execute()
     *
     * @return void
     */
    public function main()
    {
        if (isset($this->params['plugin'])) {
            $parts = explode('/', $this->params['plugin']);
            $this->plugin = implode('/', array_map([$this, '_camelize'], $parts));
            if (strpos($this->plugin, '\\')) {
                $this->abort('Invalid plugin namespace separator, please use / instead of \ for plugins.');

                return;
            }
        }
        if (isset($this->params['connection'])) {
            $this->connection = $this->params['connection'];
        }
    }

    /**
     * Executes an external shell command and pipes its output to the stdout
     *
     * @param string $command the command to execute
     * @return void
     * @throws \RuntimeException if any errors occurred during the execution
     */
    public function callProcess($command)
    {
        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];
        $this->_io->verbose('Running ' . $command);
        $process = proc_open(
            $command,
            $descriptorSpec,
            $pipes
        );
        if (!is_resource($process)) {
            $this->abort('Could not start subprocess.');

            return;
        }
        fclose($pipes[0]);

        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $error = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exit = proc_close($process);

        if ($exit !== 0) {
            throw new \RuntimeException($error);
        }

        $this->out($output);
    }

    /**
     * Handles splitting up the plugin prefix and classname.
     *
     * Sets the plugin parameter and plugin property.
     *
     * @param string $name The name to possibly split.
     * @return string The name without the plugin prefix.
     */
    protected function _getName($name)
    {
        if (strpos($name, '.')) {
            list($plugin, $name) = pluginSplit($name);
            $this->plugin = $this->params['plugin'] = $plugin;
        }

        return $name;
    }

    /**
     * Delete empty file in a given path
     *
     * @param string $path Path to folder which contains 'empty' file.
     * @return void
     */
    protected function _deleteEmptyFile($path)
    {
        $File = new File($path);
        if ($File->exists()) {
            $File->delete();
            $this->out(sprintf('<success>Deleted</success> `%s`', $path), 1, Shell::QUIET);
        }
    }

    /**
     * Get the option parser for this task.
     *
     * This base class method sets up some commonly used options.
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        return $this->_setCommonOptions(parent::getOptionParser());
    }
}
