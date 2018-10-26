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
namespace Bake\Shell;

use Bake\Utility\CommonOptionsTrait;
use Cake\Cache\Cache;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Core\ConventionsTrait;
use Cake\Core\Plugin;
use Cake\Datasource\ConnectionManager;
use Cake\Utility\Inflector;

/**
 * Command-line code generation utility to automate programmer chores.
 *
 * Bake is CakePHP's code generation script, which can help you kickstart
 * application development by writing fully functional skeleton controllers,
 * models, and templates. Going further, Bake can also write Unit Tests for you.
 *
 * @link https://book.cakephp.org/3.0/en/bake/usage.html
 *
 * @property \Bake\Shell\Task\ModelTask $Model
 */
class BakeShell extends Shell
{
    use CommonOptionsTrait;
    use ConventionsTrait;

    /**
     * The connection being used.
     *
     * @var string
     */
    public $connection = 'default';

    /**
     * Assign $this->connection to the active task if a connection param is set.
     *
     * @return void
     */
    public function startup()
    {
        parent::startup();
        Configure::write('debug', true);
        Cache::disable();
        if (!Plugin::loaded('WyriHaximus/TwigView')) {
            Plugin::load('WyriHaximus/TwigView', ['bootstrap' => true]);
        }

        $task = $this->_camelize($this->command);

        if (isset($this->{$task}) && !in_array($task, ['Project'])) {
            if (isset($this->params['connection'])) {
                $this->{$task}->connection = $this->params['connection'];
            }
            if (isset($this->params['tablePrefix'])) {
                $this->{$task}->tablePrefix = $this->params['tablePrefix'];
            }
        }
        if (isset($this->params['connection'])) {
            $this->connection = $this->params['connection'];
        }

        if ($this->params['quiet']) {
            $this->interactive = false;
            if (isset($this->{$task}) && !in_array($task, ['Project'])) {
                $this->{$task}->interactive = false;
            }
        }
    }

    /**
     * Override main() to handle action
     *
     * @return bool
     */
    public function main()
    {
        if ($this->args && $this->args[0] === 'view') {
            $this->out('<error>The view command has been renamed.</error>');
            $this->out('To create template files, please use the template command:', 2);
            $args = $this->args;
            array_shift($args);
            $args = implode($args, ' ');
            $this->out(sprintf('    <info>`bin/cake bake template %s`</info>', $args), 2);

            return false;
        }

        $connections = ConnectionManager::configured();
        if (empty($connections)) {
            $this->out('Your database configuration was not found.');
            $this->out('Add your database connection information to config/app.php.');

            return false;
        }
        $this->out('The following commands can be used to generate skeleton code for your application.', 2);
        $this->out('<info>Available bake commands:</info>', 2);
        $this->out('- all');
        $names = [];
        foreach ($this->tasks as $task) {
            list(, $name) = pluginSplit($task);
            $names[] = Inflector::underscore($name);
        }
        sort($names);
        foreach ($names as $name) {
            $this->out('- ' . $name);
        }
        $this->out('');
        $this->out('By using <info>`cake bake [name]`</info> you can invoke a specific bake task.');

        return false;
    }

    /**
     * Locate the tasks bake will use.
     *
     * Scans the following paths for tasks that are subclasses of
     * Cake\Shell\Task\BakeTask:
     *
     * - Cake/Shell/Task/
     * - Shell/Task for each loaded plugin
     * - App/Shell/Task/
     *
     * @return void
     */
    public function loadTasks()
    {
        $tasks = [];

        foreach (Plugin::loaded() as $plugin) {
            $tasks = $this->_findTasks(
                $tasks,
                Plugin::classPath($plugin),
                str_replace('/', '\\', $plugin),
                $plugin
            );
        }
        $tasks = $this->_findTasks($tasks, APP, Configure::read('App.namespace'));

        $this->tasks = array_values($tasks);
        parent::loadTasks();
    }

    /**
     * Append matching tasks in $path to the $tasks array.
     *
     * @param array $tasks The task list to modify and return.
     * @param string $path The base path to look in.
     * @param string $namespace The base namespace.
     * @param string|null $prefix The prefix to append.
     * @return array Updated tasks.
     */
    protected function _findTasks($tasks, $path, $namespace, $prefix = null)
    {
        $path .= 'Shell/Task';
        if (!is_dir($path)) {
            return $tasks;
        }
        $candidates = $this->_findClassFiles($path, $namespace);
        $classes = $this->_findTaskClasses($candidates);
        foreach ($classes as $class) {
            list(, $name) = namespaceSplit($class);
            $name = substr($name, 0, -4);
            $fullName = ($prefix ? $prefix . '.' : '') . $name;
            $tasks[$name] = $fullName;
        }

        return $tasks;
    }

    /**
     * Find task classes in a given path.
     *
     * @param string $path The path to scan.
     * @param string $namespace Namespace.
     * @return array An array of files that may contain bake tasks.
     */
    protected function _findClassFiles($path, $namespace)
    {
        $iterator = new \DirectoryIterator($path);
        $candidates = [];
        foreach ($iterator as $item) {
            if ($item->isDot() || $item->isDir()) {
                continue;
            }
            $name = $item->getBasename('.php');
            $candidates[] = $namespace . '\Shell\Task\\' . $name;
        }

        return $candidates;
    }

    /**
     * Find bake tasks in a given set of files.
     *
     * @param array $files The array of files.
     * @return array An array of matching classes.
     */
    protected function _findTaskClasses($files)
    {
        $classes = [];
        foreach ($files as $className) {
            if (!class_exists($className)) {
                continue;
            }
            $reflect = new \ReflectionClass($className);
            if (!$reflect->isInstantiable()) {
                continue;
            }
            if (!$reflect->isSubclassOf('Bake\Shell\Task\BakeTask')) {
                continue;
            }
            $classes[] = $className;
        }

        return $classes;
    }

    /**
     * Quickly bake the MVC
     *
     * @param string|null $name Name.
     * @return bool
     */
    public function all($name = null)
    {
        $this->out('Bake All');
        $this->hr();

        if (!empty($this->params['connection'])) {
            $this->connection = $this->params['connection'];
        }

        if (empty($name) && !$this->param('everything')) {
            $this->Model->connection = $this->connection;
            $this->out('Possible model names based on your database:');
            foreach ($this->Model->listUnskipped() as $table) {
                $this->out('- ' . $table);
            }
            $this->out('Run <info>`cake bake all [name]`</info> to generate skeleton files.');

            return false;
        }

        $allTables = collection([$name]);
        $filteredTables = $allTables;

        if ($this->param('everything')) {
            $this->Model->connection = $this->connection;
            $filteredTables = collection($this->Model->listUnskipped());
        }

        foreach (['Model', 'Controller', 'Template'] as $task) {
            $filteredTables->each(function ($tableName) use ($task) {
                $tableName = $this->_camelize($tableName);
                $this->{$task}->connection = $this->connection;
                $this->{$task}->interactive = $this->interactive;
                $this->{$task}->main($tableName);
            });
        }

        $this->out('<success>Bake All complete.</success>', 1, Shell::QUIET);

        return true;
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();

        $parser->setDescription(
            'The Bake script generates controllers, models and template files for your application.' .
            ' If run with no command line arguments, Bake guides the user through the class creation process.' .
            ' You can customize the generation process by telling Bake where different parts of your application' .
            ' are using command line arguments.'
        )->addSubcommand('all', [
            'help' => 'Bake a complete MVC skeleton.',
        ])->addOption('everything', [
            'help' => 'Bake a complete MVC skeleton, using all the available tables. ' .
                'Usage: "bake all --everything"',
            'default' => false,
            'boolean' => true,
        ])->addOption('prefix', [
            'help' => 'Prefix to bake controllers and templates into.'
        ])->addOption('tablePrefix', [
            'help' => 'Table prefix to be used in models.',
            'default' => null
        ]);

        $parser = $this->_setCommonOptions($parser);

        foreach ($this->_taskMap as $task => $config) {
            $taskParser = $this->{$task}->getOptionParser();
            $this->{$task}->interactive = $this->interactive;
            $parser->addSubcommand(Inflector::underscore($task), [
                'help' => $taskParser->getDescription(),
                'parser' => $taskParser
            ]);
        }

        return $parser;
    }
}
