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

use Cake\Core\Configure;
use Cake\Utility\Inflector;

/**
 * Base class for simple bake tasks code generator.
 *
 * @property \Bake\Shell\Task\BakeTemplateTask $BakeTemplate
 * @property \Bake\Shell\Task\TestTask $Test
 */
abstract class SimpleBakeTask extends BakeTask
{
    /**
     * Tasks to be loaded by this Task
     *
     * @var array
     */
    public $tasks = [
        'Bake.BakeTemplate',
        'Bake.Test'
    ];

    /**
     * Get the generated object's name.
     *
     * @return string
     */
    abstract public function name();

    /**
     * Get the generated object's filename without the leading path.
     *
     * @param string $name The name of the object being generated
     * @return string
     */
    abstract public function fileName($name);

    /**
     * Get the template name.
     *
     * @return string
     */
    abstract public function template();

    /**
     * Get template data.
     *
     * @return array
     */
    public function templateData()
    {
        $namespace = Configure::read('App.namespace');
        if ($this->plugin) {
            $namespace = $this->_pluginNamespace($this->plugin);
        }

        return ['namespace' => $namespace];
    }

    /**
     * Execute method
     *
     * @param string|null $name The name of the object to bake.
     * @return int|null
     */
    public function main($name = null)
    {
        parent::main();
        if (empty($name)) {
            return $this->error('You must provide a name to bake a ' . $this->name());
        }
        $name = $this->_getName($name);
        $name = Inflector::camelize($name);
        $this->bake($name);
        $this->bakeTest($name);
    }

    /**
     * Generate a class stub
     *
     * @param string $name The classname to generate.
     * @return string
     */
    public function bake($name)
    {
        $this->BakeTemplate->set('name', $name);
        $this->BakeTemplate->set($this->templateData());
        $contents = $this->BakeTemplate->generate($this->template());

        $filename = $this->getPath() . $this->fileName($name);
        $this->createFile($filename, $contents);
        $emptyFile = $this->getPath() . 'empty';
        $this->_deleteEmptyFile($emptyFile);

        return $contents;
    }

    /**
     * Generate a test case.
     *
     * @param string $className The class to bake a test for.
     * @return string|bool|null
     */
    public function bakeTest($className)
    {
        if (!empty($this->params['no-test'])) {
            return null;
        }
        $this->Test->plugin = $this->plugin;

        return $this->Test->bake($this->name(), $className);
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $name = $this->name();
        $parser->setDescription(
            sprintf('Bake a %s class file.', $name)
        )->addArgument('name', [
            'help' => sprintf(
                'Name of the %s to bake. Can use Plugin.name to bake %s files into plugins.',
                $name,
                $name
            )
        ])->addOption('no-test', [
            'boolean' => true,
            'help' => 'Do not generate a test skeleton.'
        ]);

        return $parser;
    }
}
