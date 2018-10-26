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

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

/**
 * Task class for creating and updating controller files.
 *
 * @property \Bake\Shell\Task\ModelTask $Model
 * @property \Bake\Shell\Task\BakeTemplateTask $BakeTemplate
 * @property \Bake\Shell\Task\TestTask $Test
 */
class ControllerTask extends BakeTask
{
    /**
     * Tasks to be loaded by this Task
     *
     * @var array
     */
    public $tasks = [
        'Bake.Model',
        'Bake.BakeTemplate',
        'Bake.Test'
    ];

    /**
     * Path fragment for generated code.
     *
     * @var string
     */
    public $pathFragment = 'Controller/';

    /**
     * Execution method always used for tasks
     *
     * @param string|null $name The name of the controller to bake.
     * @return null|bool
     */
    public function main($name = null)
    {
        parent::main();
        $name = $this->_getName($name);

        if (empty($name)) {
            $this->out('Possible controllers based on your current database:');
            foreach ($this->listAll() as $table) {
                $this->out('- ' . $this->_camelize($table));
            }

            return true;
        }

        $controller = $this->_camelize($name);
        $this->bake($controller);
    }

    /**
     * Bake All the controllers at once. Will only bake controllers for models that exist.
     *
     * @return void
     */
    public function all()
    {
        $tables = $this->listAll();
        foreach ($tables as $table) {
            TableRegistry::clear();
            $this->main($table);
        }
    }

    /**
     * Assembles and writes a Controller file
     *
     * @param string $controllerName Controller name already pluralized and correctly cased.
     * @return string Baked controller
     */
    public function bake($controllerName)
    {
        $this->out("\n" . sprintf('Baking controller class for %s...', $controllerName), 1, Shell::QUIET);

        $actions = [];
        if (!$this->param('no-actions') && !$this->param('actions')) {
            $actions = ['index', 'view', 'add', 'edit', 'delete'];
        }
        if ($this->param('actions')) {
            $actions = array_map('trim', explode(',', $this->param('actions')));
            $actions = array_filter($actions);
        }

        $helpers = $this->getHelpers();
        $components = $this->getComponents();

        $prefix = $this->_getPrefix();
        if ($prefix) {
            $prefix = '\\' . str_replace('/', '\\', $prefix);
        }

        $namespace = Configure::read('App.namespace');
        if ($this->plugin) {
            $namespace = $this->_pluginNamespace($this->plugin);
        }

        $currentModelName = $controllerName;
        $plugin = $this->plugin;
        if ($plugin) {
            $plugin .= '.';
        }

        if (TableRegistry::exists($plugin . $currentModelName)) {
            $modelObj = TableRegistry::get($plugin . $currentModelName);
        } else {
            $modelObj = TableRegistry::get($plugin . $currentModelName, [
                'connectionName' => $this->connection
            ]);
        }

        $pluralName = $this->_variableName($currentModelName);
        $singularName = $this->_singularName($currentModelName);
        $singularHumanName = $this->_singularHumanName($controllerName);
        $pluralHumanName = $this->_variableName($controllerName);

        $defaultModel = sprintf('%s\Model\Table\%sTable', $namespace, $controllerName);
        if (!class_exists($defaultModel)) {
            $defaultModel = null;
        }
        $entityClassName = $this->_entityName($modelObj->getAlias());

        $data = compact(
            'actions',
            'admin',
            'components',
            'currentModelName',
            'defaultModel',
            'entityClassName',
            'helpers',
            'modelObj',
            'namespace',
            'plugin',
            'pluralHumanName',
            'pluralName',
            'prefix',
            'singularHumanName',
            'singularName'
        );
        $data['name'] = $controllerName;

        $out = $this->bakeController($controllerName, $data);
        $this->bakeTest($controllerName);

        return $out;
    }

    /**
     * Generate the controller code
     *
     * @param string $controllerName The name of the controller.
     * @param array $data The data to turn into code.
     * @return string The generated controller file.
     */
    public function bakeController($controllerName, array $data)
    {
        $data += [
            'name' => null,
            'namespace' => null,
            'prefix' => null,
            'actions' => null,
            'helpers' => null,
            'components' => null,
            'plugin' => null,
            'pluginPath' => null,
        ];

        $this->BakeTemplate->set($data);

        $contents = $this->BakeTemplate->generate('Controller/controller');

        $path = $this->getPath();
        $filename = $path . $controllerName . 'Controller.php';
        $this->createFile($filename, $contents);

        return $contents;
    }

    /**
     * Assembles and writes a unit test file
     *
     * @param string $className Controller class name
     * @return string|null Baked test
     */
    public function bakeTest($className)
    {
        if (!empty($this->params['no-test'])) {
            return null;
        }
        $this->Test->plugin = $this->plugin;
        $this->Test->connection = $this->connection;
        $this->Test->interactive = $this->interactive;

        return $this->Test->bake('Controller', $className);
    }

    /**
     * Get the list of components for the controller.
     *
     * @return array
     */
    public function getComponents()
    {
        $components = [];
        if (!empty($this->params['components'])) {
            $components = explode(',', $this->params['components']);
            $components = array_values(array_filter(array_map('trim', $components)));
        }

        return $components;
    }

    /**
     * Get the list of helpers for the controller.
     *
     * @return array
     */
    public function getHelpers()
    {
        $helpers = [];
        if (!empty($this->params['helpers'])) {
            $helpers = explode(',', $this->params['helpers']);
            $helpers = array_values(array_filter(array_map('trim', $helpers)));
        }

        return $helpers;
    }

    /**
     * Outputs and gets the list of possible controllers from database
     *
     * @return array Set of controllers
     */
    public function listAll()
    {
        $this->Model->connection = $this->connection;

        return $this->Model->listUnskipped();
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
            'Bake a controller skeleton.'
        )->addArgument('name', [
            'help' => 'Name of the controller to bake (without the `Controller` suffix). ' .
                'You can use Plugin.name to bake controllers into plugins.'
        ])->addOption('components', [
            'help' => 'The comma separated list of components to use.'
        ])->addOption('helpers', [
            'help' => 'The comma separated list of helpers to use.'
        ])->addOption('prefix', [
            'help' => 'The namespace/routing prefix to use.'
        ])->addOption('actions', [
            'help' => 'The comma separated list of actions to generate. ' .
                      'You can include custom methods provided by your template set here.'
        ])->addOption('no-test', [
            'boolean' => true,
            'help' => 'Do not generate a test skeleton.'
        ])->addOption('no-actions', [
            'boolean' => true,
            'help' => 'Do not generate basic CRUD action methods.'
        ])->addSubcommand('all', [
            'help' => 'Bake all controllers with CRUD methods.'
        ]);

        return $parser;
    }
}
