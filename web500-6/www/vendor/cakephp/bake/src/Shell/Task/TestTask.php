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
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Core\Plugin;
use Cake\Filesystem\Folder;
use Cake\Http\Response;
use Cake\Http\ServerRequest as Request;
use Cake\ORM\Association;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use ReflectionClass;

/**
 * Task class for creating and updating test files.
 *
 * @property \Bake\Shell\Task\BakeTemplateTask $BakeTemplate
 */
class TestTask extends BakeTask
{
    /**
     * Tasks used.
     *
     * @var array
     */
    public $tasks = ['Bake.BakeTemplate'];

    /**
     * class types that methods can be generated for
     *
     * @var array
     */
    public $classTypes = [
        'Entity' => 'Model\Entity',
        'Table' => 'Model\Table',
        'Controller' => 'Controller',
        'Component' => 'Controller\Component',
        'Behavior' => 'Model\Behavior',
        'Helper' => 'View\Helper',
        'Shell' => 'Shell',
        'Task' => 'Shell\Task',
        'Shell_helper' => 'Shell\Helper',
        'Cell' => 'View\Cell',
        'Form' => 'Form',
        'Mailer' => 'Mailer',
    ];

    /**
     * class types that methods can be generated for
     *
     * @var array
     */
    public $classSuffixes = [
        'entity' => '',
        'table' => 'Table',
        'controller' => 'Controller',
        'component' => 'Component',
        'behavior' => 'Behavior',
        'helper' => 'Helper',
        'shell' => 'Shell',
        'task' => 'Task',
        'shell_helper' => 'Helper',
        'cell' => 'Cell',
        'form' => 'Form',
        'mailer' => 'Mailer',
    ];

    /**
     * Internal list of fixtures that have been added so far.
     *
     * @var array
     */
    protected $_fixtures = [];

    /**
     * Execution method always used for tasks
     *
     * @param string|null $type Class type.
     * @param string|null $name Name.
     * @return array|null
     */
    public function main($type = null, $name = null)
    {
        parent::main();
        if (empty($type) && empty($name)) {
            $this->outputTypeChoices();

            return null;
        }

        if ($this->param('all')) {
            $this->_bakeAll($type);

            return null;
        }

        if (empty($name)) {
            return $this->outputClassChoices($type);
        }

        if ($this->bake($type, $name)) {
            $this->out('<success>Done</success>');
        }
    }

    /**
     * Output a list of class types you can bake a test for.
     *
     * @return void
     */
    public function outputTypeChoices()
    {
        $this->out(
            'You must provide a class type to bake a test for. The valid types are:',
            2
        );
        $i = 0;
        foreach ($this->classTypes as $option => $package) {
            $this->out(++$i . '. ' . $option);
        }
        $this->out('');
        $this->out('Re-run your command as `cake bake <type> <classname>`');
    }

    /**
     * Output a list of possible classnames you might want to generate a test for.
     *
     * @param string $typeName The typename to get classes for.
     * @return array
     */
    public function outputClassChoices($typeName)
    {
        $type = $this->mapType($typeName);
        $this->out(
            'You must provide a class to bake a test for. Some possible options are:',
            2
        );
        $options = $this->_getClassOptions($type);
        $i = 0;
        foreach ($options as $option) {
            $this->out(++$i . '. ' . $option);
        }
        $this->out('');
        $this->out('Re-run your command as `cake bake ' . $typeName . ' <classname>`');

        return $options;
    }

    /**
     * @param string $type The typename to get bake all classes for.
     * @return void
     */
    protected function _bakeAll($type)
    {
        $mappedType = $this->mapType($type);
        $classes = $this->_getClassOptions($mappedType);

        foreach ($classes as $class) {
            if ($this->bake($type, $class)) {
                $this->out('<success>Done - ' . $class . '</success>');
            } else {
                $this->out('<error>Failed - ' . $class . '</error>');
            }
        }

        $this->out('<info>Bake finished</info>');
    }

    /**
     * Get the possible classes for a given type.
     *
     * @param string $namespace The namespace fragment to look for classes in.
     * @return array
     */
    protected function _getClassOptions($namespace)
    {
        $classes = [];
        $base = APP;
        if ($this->plugin) {
            $base = Plugin::classPath($this->plugin);
        }
        $path = $base . str_replace('\\', DS, $namespace);
        $folder = new Folder($path);
        list(, $files) = $folder->read();
        foreach ($files as $file) {
            $classes[] = str_replace('.php', '', $file);
        }

        return $classes;
    }

    /**
     * Completes final steps for generating data to create test case.
     *
     * @param string $type Type of object to bake test case for ie. Model, Controller
     * @param string $className the 'cake name' for the class ie. Posts for the PostsController
     * @return string|bool
     */
    public function bake($type, $className)
    {
        if (!isset($this->classSuffixes[strtolower($type)]) || !isset($this->classTypes[ucfirst($type)])) {
            return false;
        }

        $fullClassName = $this->getRealClassName($type, $className);

        if (empty($this->params['no-fixture'])) {
            if (!empty($this->params['fixtures'])) {
                $fixtures = array_map('trim', explode(',', $this->params['fixtures']));
                $this->_fixtures = array_filter($fixtures);
            } elseif ($this->typeCanDetectFixtures($type) && class_exists($fullClassName)) {
                $this->out('Bake is detecting possible fixtures...');
                $testSubject = $this->buildTestSubject($type, $fullClassName);
                $this->generateFixtureList($testSubject);
            }
        }

        $methods = [];
        if (class_exists($fullClassName)) {
            $methods = $this->getTestableMethods($fullClassName);
        }
        $mock = $this->hasMockClass($type);
        list($preConstruct, $construction, $postConstruct) = $this->generateConstructor($type, $fullClassName);
        $uses = $this->generateUses($type, $fullClassName);

        $subject = $className;
        list($namespace, $className) = namespaceSplit($fullClassName);

        $baseNamespace = Configure::read('App.namespace');
        if ($this->plugin) {
            $baseNamespace = $this->_pluginNamespace($this->plugin);
        }
        $subNamespace = substr($namespace, strlen($baseNamespace) + 1);

        $properties = $this->generateProperties($type, $subject, $fullClassName);

        $this->out("\n" . sprintf('Baking test case for %s ...', $fullClassName), 1, Shell::QUIET);

        $this->BakeTemplate->set('fixtures', $this->_fixtures);
        $this->BakeTemplate->set('plugin', $this->plugin);
        $this->BakeTemplate->set(compact(
            'subject',
            'className',
            'properties',
            'methods',
            'type',
            'fullClassName',
            'mock',
            'type',
            'preConstruct',
            'postConstruct',
            'construction',
            'uses',
            'baseNamespace',
            'subNamespace',
            'namespace'
        ));
        $out = $this->BakeTemplate->generate('tests/test_case');

        $filename = $this->testCaseFileName($type, $fullClassName);
        $emptyFile = $this->getPath() . $this->getSubspacePath($type) . DS . 'empty';
        $this->_deleteEmptyFile($emptyFile);
        if ($this->createFile($filename, $out)) {
            return $out;
        }

        return false;
    }

    /**
     * Checks whether the chosen type can find its own fixtures.
     * Currently only model, and controller are supported
     *
     * @param string $type The Type of object you are generating tests for eg. controller
     * @return bool
     */
    public function typeCanDetectFixtures($type)
    {
        $type = strtolower($type);

        return in_array($type, ['controller', 'table']);
    }

    /**
     * Construct an instance of the class to be tested.
     * So that fixtures can be detected
     *
     * @param string $type The type of object you are generating tests for eg. controller
     * @param string $class The classname of the class the test is being generated for.
     * @return object And instance of the class that is going to be tested.
     */
    public function buildTestSubject($type, $class)
    {
        if (strtolower($type) === 'table') {
            list(, $name) = namespaceSplit($class);
            $name = str_replace('Table', '', $name);
            if ($this->plugin) {
                $name = $this->plugin . '.' . $name;
            }
            if (TableRegistry::exists($name)) {
                $instance = TableRegistry::get($name);
            } else {
                $instance = TableRegistry::get($name, [
                    'connectionName' => $this->connection
                ]);
            }
        } elseif (strtolower($type) === 'controller') {
            $instance = new $class(new Request(), new Response());
        } else {
            $instance = new $class();
        }

        return $instance;
    }

    /**
     * Gets the real class name from the cake short form. If the class name is already
     * suffixed with the type, the type will not be duplicated.
     *
     * @param string $type The Type of object you are generating tests for eg. controller.
     * @param string $class the Classname of the class the test is being generated for.
     * @return string Real class name
     */
    public function getRealClassName($type, $class)
    {
        $namespace = Configure::read('App.namespace');
        if ($this->plugin) {
            $namespace = str_replace('/', '\\', $this->plugin);
        }
        $suffix = $this->classSuffixes[strtolower($type)];
        $subSpace = $this->mapType($type);
        if ($suffix && strpos($class, $suffix) === false) {
            $class .= $suffix;
        }
        $prefix = $this->_getPrefix();
        if (in_array(strtolower($type), ['controller', 'cell']) && $prefix) {
            $subSpace .= '\\' . str_replace('/', '\\', $prefix);
        }

        return $namespace . '\\' . $subSpace . '\\' . $class;
    }

    /**
     * Gets the subspace path for a test.
     *
     * @param string $type The Type of object you are generating tests for eg. controller.
     * @return string Path of the subspace.
     */
    public function getSubspacePath($type)
    {
        $subspace = $this->mapType($type);

        return str_replace('\\', DS, $subspace);
    }

    /**
     * Map the types that TestTask uses to concrete types that App::className can use.
     *
     * @param string $type The type of thing having a test generated.
     * @return string
     * @throws \Cake\Core\Exception\Exception When invalid object types are requested.
     */
    public function mapType($type)
    {
        $type = ucfirst($type);
        if (empty($this->classTypes[$type])) {
            throw new Exception('Invalid object type.');
        }

        return $this->classTypes[$type];
    }

    /**
     * Get methods declared in the class given.
     * No parent methods will be returned
     *
     * @param string $className Name of class to look at.
     * @return array Array of method names.
     */
    public function getTestableMethods($className)
    {
        $class = new ReflectionClass($className);
        $out = [];
        foreach ($class->getMethods() as $method) {
            if ($method->getDeclaringClass()->getName() !== $className) {
                continue;
            }
            if (!$method->isPublic()) {
                continue;
            }
            $out[] = $method->getName();
        }

        return $out;
    }

    /**
     * Generate the list of fixtures that will be required to run this test based on
     * loaded models.
     *
     * @param object $subject The object you want to generate fixtures for.
     * @return array Array of fixtures to be included in the test.
     */
    public function generateFixtureList($subject)
    {
        $this->_fixtures = [];
        if ($subject instanceof Table) {
            $this->_processModel($subject);
        } elseif ($subject instanceof Controller) {
            $this->_processController($subject);
        }

        return array_values($this->_fixtures);
    }

    /**
     * Process a model, pull out model name + associations converted to fixture names.
     *
     * @param \Cake\ORM\Table $subject A Model class to scan for associations and pull fixtures off of.
     * @return void
     */
    protected function _processModel($subject)
    {
        if (!$subject instanceof Table) {
            return;
        }
        $this->_addFixture($subject->getAlias());
        foreach ($subject->associations()->keys() as $alias) {
            $assoc = $subject->association($alias);
            $target = $assoc->getTarget();
            $name = $target->getAlias();
            $subjectClass = get_class($subject);

            if ($subjectClass !== 'Cake\ORM\Table' && $subjectClass === get_class($target)) {
                continue;
            }
            if (!isset($this->_fixtures[$name])) {
                $this->_addFixture($target->getAlias());
            }
        }
    }

    /**
     * Process all the models attached to a controller
     * and generate a fixture list.
     *
     * @param \Cake\Controller\Controller $subject A controller to pull model names off of.
     * @return void
     */
    protected function _processController($subject)
    {
        $models = [$subject->modelClass];
        foreach ($models as $model) {
            list(, $model) = pluginSplit($model);
            $this->_processModel($subject->{$model});
        }
    }

    /**
     * Add class name to the fixture list.
     * Sets the app. or plugin.plugin_name. prefix.
     *
     * @param string $name Name of the Model class that a fixture might be required for.
     * @return void
     */
    protected function _addFixture($name)
    {
        if ($this->plugin) {
            $prefix = 'plugin.' . Inflector::underscore($this->plugin) . '.';
        } else {
            $prefix = 'app.';
        }
        $fixture = $prefix . $this->_fixtureName($name);
        $this->_fixtures[$name] = $fixture;
    }

    /**
     * Is a mock class required for this type of test?
     * Controllers require a mock class.
     *
     * @param string $type The type of object tests are being generated for eg. controller.
     * @return bool
     */
    public function hasMockClass($type)
    {
        $type = strtolower($type);

        return $type === 'controller';
    }

    /**
     * Generate a constructor code snippet for the type and class name
     *
     * @param string $type The Type of object you are generating tests for eg. controller
     * @param string $fullClassName The full classname of the class the test is being generated for.
     * @return array Constructor snippets for the thing you are building.
     */
    public function generateConstructor($type, $fullClassName)
    {
        list(, $className) = namespaceSplit($fullClassName);
        $type = strtolower($type);
        $pre = $construct = $post = '';
        if ($type === 'table') {
            $tableName = str_replace('Table', '', $className);
            $pre = "\$config = TableRegistry::exists('{$tableName}') ? [] : ['className' => {$className}::class];";
            $construct = "TableRegistry::get('{$tableName}', \$config);";
        }
        if ($type === 'behavior' || $type === 'entity' || $type === 'form') {
            $construct = "new {$className}();";
        }
        if ($type === 'helper') {
            $pre = "\$view = new View();";
            $construct = "new {$className}(\$view);";
        }
        if ($type === 'component') {
            $pre = "\$registry = new ComponentRegistry();";
            $construct = "new {$className}(\$registry);";
        }
        if ($type === 'shell') {
            $pre = "\$this->io = \$this->getMockBuilder('Cake\Console\ConsoleIo')->getMock();";
            $construct = "new {$className}(\$this->io);";
        }
        if ($type === 'task') {
            $pre = "\$this->io = \$this->getMockBuilder('Cake\Console\ConsoleIo')->getMock();\n";
            $construct = "\$this->getMockBuilder('{$fullClassName}')\n";
            $construct .= "            ->setConstructorArgs([\$this->io])\n";
            $construct .= "            ->getMock();";
        }
        if ($type === 'cell') {
            $pre = "\$this->request = \$this->getMockBuilder('Cake\Http\ServerRequest')->getMock();\n";
            $pre .= "        \$this->response = \$this->getMockBuilder('Cake\Http\Response')->getMock();";
            $construct = "new {$className}(\$this->request, \$this->response);";
        }
        if ($type === 'shell_helper') {
            $pre = "\$this->stub = new ConsoleOutput();\n";
            $pre .= "        \$this->io = new ConsoleIo(\$this->stub);";
            $construct = "new {$className}(\$this->io);";
        }

        return [$pre, $construct, $post];
    }

    /**
     * Generate property info for the type and class name
     *
     * The generated property info consists of a set of arrays that hold the following keys:
     *
     * - `description` (the property description)
     * - `type` (the property docblock type)
     * - `name` (the property name)
     * - `value` (optional - the properties initial value)
     *
     * @param string $type The Type of object you are generating tests for eg. controller
     * @param string $subject The name of the test subject.
     * @param string $fullClassName The Classname of the class the test is being generated for.
     * @return array An array containing property info
     */
    public function generateProperties($type, $subject, $fullClassName)
    {
        $type = strtolower($type);

        $properties = [];
        switch (strtolower($type)) {
            case 'cell':
                $properties[] = [
                    'description' => 'Request mock',
                    'type' => '\Cake\Http\ServerRequest|\PHPUnit_Framework_MockObject_MockObject',
                    'name' => 'request'
                ];
                $properties[] = [
                    'description' => 'Response mock',
                    'type' => '\Cake\Http\Response|\PHPUnit_Framework_MockObject_MockObject',
                    'name' => 'response'
                ];
                break;

            case 'shell':
            case 'task':
                $properties[] = [
                    'description' => 'ConsoleIo mock',
                    'type' => '\Cake\Console\ConsoleIo|\PHPUnit_Framework_MockObject_MockObject',
                    'name' => 'io'
                ];
                break;

            case 'shell_helper':
                $properties[] = [
                    'description' => 'ConsoleOutput stub',
                    'type' => '\Cake\TestSuite\Stub\ConsoleOutput',
                    'name' => 'stub'
                ];
                $properties[] = [
                    'description' => 'ConsoleIo mock',
                    'type' => '\Cake\Console\ConsoleIo',
                    'name' => 'io'
                ];
                break;
        }

        if ($type !== 'controller') {
            $properties[] = [
                'description' => 'Test subject',
                'type' => '\\' . $fullClassName,
                'name' => $subject
            ];
        }

        return $properties;
    }

    /**
     * Generate the uses() calls for a type & class name
     *
     * @param string $type The Type of object you are generating tests for eg. controller
     * @param string $fullClassName The Classname of the class the test is being generated for.
     * @return array An array containing used classes
     */
    public function generateUses($type, $fullClassName)
    {
        $uses = [];
        $type = strtolower($type);
        if ($type === 'component') {
            $uses[] = 'Cake\Controller\ComponentRegistry';
        }
        if ($type === 'table') {
            $uses[] = 'Cake\ORM\TableRegistry';
        }
        if ($type === 'helper') {
            $uses[] = 'Cake\View\View';
        }
        if ($type === 'shell_helper') {
            $uses[] = 'Cake\TestSuite\Stub\ConsoleOutput';
            $uses[] = 'Cake\Console\ConsoleIo';
        }
        $uses[] = $fullClassName;

        return $uses;
    }

    /**
     * Get the file path.
     *
     * @return string
     */
    public function getPath()
    {
        $dir = 'TestCase/';
        $path = defined('TESTS') ? TESTS . $dir : ROOT . DS . 'tests' . DS . $dir;
        if (isset($this->plugin)) {
            $path = $this->_pluginPath($this->plugin) . 'tests/' . $dir;
        }

        return $path;
    }

    /**
     * Make the filename for the test case. resolve the suffixes for controllers
     * and get the plugin path if needed.
     *
     * @param string $type The Type of object you are generating tests for eg. controller
     * @param string $className The fully qualified classname of the class the test is being generated for.
     * @return string filename the test should be created on.
     */
    public function testCaseFileName($type, $className)
    {
        $path = $this->getPath();
        $namespace = Configure::read('App.namespace');
        if ($this->plugin) {
            $namespace = $this->plugin;
        }

        $classTail = substr($className, strlen($namespace) + 1);
        $path = $path . $classTail . 'Test.php';

        return str_replace(['/', '\\'], DS, $path);
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();

        $types = array_keys($this->classTypes);
        $types = array_merge($types, array_map('strtolower', $types));

        $parser->setDescription(
            'Bake test case skeletons for classes.'
        )->addArgument('type', [
            'help' => 'Type of class to bake, can be any of the following:' .
                ' controller, model, helper, component or behavior.',
            'choices' => $types,
        ])->addArgument('name', [
            'help' => 'An existing class to bake tests for.'
        ])->addOption('fixtures', [
            'help' => 'A comma separated list of fixture names you want to include.'
        ])->addOption('no-fixture', [
            'boolean' => true,
            'default' => false,
            'help' => 'Select if you want to bake without fixture.'
        ])->addOption('prefix', [
            'default' => false,
            'help' => 'Use when baking tests for prefixed controllers.'
        ])->addOption('all', [
            'boolean' => true,
            'help' => 'Bake all classes of the given type'
        ]);

        return $parser;
    }
}
