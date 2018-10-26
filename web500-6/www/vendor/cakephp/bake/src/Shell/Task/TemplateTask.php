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

use Bake\Utility\Model\AssociationFilter;
use Cake\Console\Shell;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;

/**
 * Task class for creating and updating view template files.
 *
 * @property \Bake\Shell\Task\ModelTask $Model
 * @property \Bake\Shell\Task\BakeTemplateTask $BakeTemplate
 */
class TemplateTask extends BakeTask
{
    /**
     * Tasks to be loaded by this Task
     *
     * @var array
     */
    public $tasks = [
        'Bake.Model',
        'Bake.BakeTemplate'
    ];

    /**
     * path to Template directory
     *
     * @var array
     */
    public $pathFragment = 'Template/';

    /**
     * Name of the controller being used
     *
     * @var string
     */
    public $controllerName = null;

    /**
     * Classname of the controller being used
     *
     * @var string
     */
    public $controllerClass = null;

    /**
     * Name with plugin of the model being used
     *
     * @var string
     */
    public $modelName = null;

    /**
     * Actions to use for scaffolding
     *
     * @var array
     */
    public $scaffoldActions = ['index', 'view', 'add', 'edit'];

    /**
     * AssociationFilter utility
     *
     * @var AssociationFilter
     */
    protected $_associationFilter = null;

    /**
     * Template path.
     *
     * @var string
     */
    public $path;

    /**
     * Override initialize
     *
     * @return void
     */
    public function initialize()
    {
        $this->path = current(App::path('Template'));
    }

    /**
     * Execution method always used for tasks
     *
     * @param string|null $name The name of the controller to bake view templates for.
     * @param string|null $template The template to bake with.
     * @param string|null $action The output action name. Defaults to $template.
     * @return mixed
     */
    public function main($name = null, $template = null, $action = null)
    {
        parent::main();

        if (empty($name)) {
            $this->out('Possible tables to bake view templates for based on your current database:');
            $this->Model->connection = $this->connection;
            foreach ($this->Model->listUnskipped() as $table) {
                $this->out('- ' . $this->_camelize($table));
            }

            return true;
        }
        $name = $this->_getName($name);

        $controller = null;
        if (!empty($this->params['controller'])) {
            $controller = $this->params['controller'];
        }
        $this->controller($name, $controller);
        $this->model($name);

        if ($template && $action === null) {
            $action = $template;
        }
        if ($template) {
            $this->bake($template, true, $action);

            return true;
        }

        $vars = $this->_loadController();
        $methods = $this->_methodsToBake();

        foreach ($methods as $method) {
            $content = $this->getContent($method, $vars);
            if ($content) {
                $this->bake($method, $content);
            }
        }
    }

    /**
     * Set the model class for the table.
     *
     * @param string $table The table/model that is being baked.
     * @return void
     */
    public function model($table)
    {
        $tableName = $this->_camelize($table);
        $plugin = null;
        if (!empty($this->params['plugin'])) {
            $plugin = $this->params['plugin'] . '.';
        }
        $this->modelName = $plugin . $tableName;
    }

    /**
     * Set the controller related properties.
     *
     * @param string $table The table/model that is being baked.
     * @param string|null $controller The controller name if specified.
     * @return void
     */
    public function controller($table, $controller = null)
    {
        $tableName = $this->_camelize($table);
        if (empty($controller)) {
            $controller = $tableName;
        }
        $this->controllerName = $controller;

        $plugin = $this->param('plugin');
        if ($plugin) {
            $plugin .= '.';
        }
        $prefix = $this->_getPrefix();
        if ($prefix) {
            $prefix .= '/';
        }
        $this->controllerClass = App::className($plugin . $prefix . $controller, 'Controller', 'Controller');
    }

    /**
     * Get the path base for view templates.
     *
     * @return string
     */
    public function getPath()
    {
        $path = parent::getPath();
        $path .= $this->controllerName . DS;

        return $path;
    }

    /**
     * Get a list of actions that can / should have view templates baked for them.
     *
     * @return array Array of action names that should be baked
     */
    protected function _methodsToBake()
    {
        $base = Configure::read('App.namespace');

        $methods = [];
        if (class_exists($this->controllerClass)) {
            $methods = array_diff(
                array_map(
                    'Cake\Utility\Inflector::underscore',
                    get_class_methods($this->controllerClass)
                ),
                array_map(
                    'Cake\Utility\Inflector::underscore',
                    get_class_methods($base . '\Controller\AppController')
                )
            );
        }
        if (empty($methods)) {
            $methods = $this->scaffoldActions;
        }
        foreach ($methods as $i => $method) {
            if ($method[0] === '_') {
                unset($methods[$i]);
            }
        }

        return $methods;
    }

    /**
     * Bake All view templates for all controller actions.
     *
     * @return void
     */
    public function all()
    {
        $this->Model->connection = $this->connection;
        $tables = $this->Model->listUnskipped();

        foreach ($tables as $table) {
            $this->main($table);
        }
    }

    /**
     * Loads Controller and sets variables for the template
     * Available template variables:
     *
     * - 'modelObject'
     * - 'modelClass'
     * - 'entityClass'
     * - 'primaryKey'
     * - 'displayField'
     * - 'singularVar'
     * - 'pluralVar'
     * - 'singularHumanName'
     * - 'pluralHumanName'
     * - 'fields'
     * - 'keyFields'
     * - 'schema'
     *
     * @return array Returns variables to be made available to a view template
     */
    protected function _loadController()
    {
        if (TableRegistry::exists($this->modelName)) {
            $modelObject = TableRegistry::get($this->modelName);
        } else {
            $modelObject = TableRegistry::get($this->modelName, [
                'connectionName' => $this->connection
            ]);
        }

        $namespace = Configure::read('App.namespace');

        $primaryKey = (array)$modelObject->getPrimaryKey();
        $displayField = $modelObject->getDisplayField();
        $singularVar = $this->_singularName($this->controllerName);
        $singularHumanName = $this->_singularHumanName($this->controllerName);
        $schema = $modelObject->getSchema();
        $fields = $schema->columns();
        $modelClass = $this->modelName;

        list(, $entityClass) = namespaceSplit($this->_entityName($this->modelName));
        $entityClass = sprintf('%s\Model\Entity\%s', $namespace, $entityClass);
        if (!class_exists($entityClass)) {
            $entityClass = EntityInterface::class;
        }
        $associations = $this->_filteredAssociations($modelObject);
        $keyFields = [];
        if (!empty($associations['BelongsTo'])) {
            foreach ($associations['BelongsTo'] as $assoc) {
                $keyFields[$assoc['foreignKey']] = $assoc['variable'];
            }
        }

        $pluralVar = Inflector::variable($this->controllerName);
        $pluralHumanName = $this->_pluralHumanName($this->controllerName);

        return compact(
            'modelObject',
            'modelClass',
            'entityClass',
            'schema',
            'primaryKey',
            'displayField',
            'singularVar',
            'pluralVar',
            'singularHumanName',
            'pluralHumanName',
            'fields',
            'associations',
            'keyFields',
            'namespace'
        );
    }

    /**
     * Assembles and writes bakes the view file.
     *
     * @param string $template Template file to use.
     * @param string $content Content to write.
     * @param string $outputFile The output file to create. If null will use `$template`
     * @return string|false Generated file content.
     */
    public function bake($template, $content = '', $outputFile = null)
    {
        if ($outputFile === null) {
            $outputFile = $template;
        }
        if ($content === true) {
            $content = $this->getContent($template);
        }
        if (empty($content)) {
            $this->err("<warning>No generated content for '{$template}.ctp', not generating template.</warning>");

            return false;
        }
        $this->out("\n" . sprintf('Baking `%s` view template file...', $outputFile), 1, Shell::QUIET);
        $path = $this->getPath();
        $filename = $path . Inflector::underscore($outputFile) . '.ctp';
        $this->createFile($filename, $content);

        return $content;
    }

    /**
     * Builds content from template and variables
     *
     * @param string $action name to generate content to
     * @param array|null $vars passed for use in templates
     * @return string|false Content from template
     */
    public function getContent($action, $vars = null)
    {
        if (!$vars) {
            $vars = $this->_loadController();
        }

        if (empty($vars['primaryKey'])) {
            $this->error('Cannot generate views for models with no primary key');

            return false;
        }

        if ($action === "index" && !empty($this->params['index-columns'])) {
            $this->BakeTemplate->set('indexColumns', $this->params['index-columns']);
        }

        $this->BakeTemplate->set('action', $action);
        $this->BakeTemplate->set('plugin', $this->plugin);
        $this->BakeTemplate->set($vars);

        return $this->BakeTemplate->generate("Template/$action");
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
            'Bake views for a controller, using built-in or custom templates. '
        )->addArgument('controller', [
            'help' => 'Name of the controller views to bake. You can use Plugin.name as a shortcut for plugin baking.'
        ])->addArgument('action', [
            'help' => "Will bake a single action's file. core templates are (index, add, edit, view)"
        ])->addArgument('alias', [
            'help' => 'Will bake the template in <action> but create the filename after <alias>.'
        ])->addOption('controller', [
            'help' => 'The controller name if you have a controller that does not follow conventions.'
        ])->addOption('prefix', [
            'help' => 'The routing prefix to generate views for.',
        ])->addOption('index-columns', [
            'help' => 'Limit for the number of index columns',
            'default' => 0
        ])->addSubcommand('all', [
            'help' => '[optional] Bake all CRUD action views for all controllers. Requires models and controllers to exist.'
        ]);

        return $parser;
    }

    /**
     * Get filtered associations
     * To be mocked...
     *
     * @param \Cake\ORM\Table $model Table
     * @return array associations
     */
    protected function _filteredAssociations(Table $model)
    {
        if (is_null($this->_associationFilter)) {
            $this->_associationFilter = new AssociationFilter();
        }

        return $this->_associationFilter->filterAssociations($model);
    }
}
