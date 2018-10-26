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

/**
 * Task for creating cells.
 *
 * @property \Bake\Shell\Task\BakeTemplateTask $BakeTemplate
 * @property \Bake\Shell\Task\TestTask $Test
 */
class CellTask extends SimpleBakeTask
{
    /**
     * Task name used in path generation.
     *
     * @var string
     */
    public $pathFragment = 'View/Cell/';

    /**
     * {@inheritDoc}
     */
    public function name()
    {
        return 'cell';
    }

    /**
     * {@inheritDoc}
     */
    public function fileName($name)
    {
        return $name . 'Cell.php';
    }

    /**
     * {@inheritDoc}
     */
    public function template()
    {
        return 'View/cell';
    }

    /**
     * Get template data.
     *
     * @return array
     */
    public function templateData()
    {
        $prefix = $this->_getPrefix();
        if ($prefix) {
            $prefix = '\\' . str_replace('/', '\\', $prefix);
        }

        $namespace = Configure::read('App.namespace');
        if ($this->plugin) {
            $namespace = $this->_pluginNamespace($this->plugin);
        }

        return compact('namespace', 'prefix');
    }

    /**
     * Bake the Cell class and template file.
     *
     * @param string $name The name of the cell to make.
     * @return string
     */
    public function bake($name)
    {
        $this->bakeTemplate($name);

        return parent::bake($name);
    }

    /**
     * Bake an empty file for a cell.
     *
     * @param string $name The name of the cell a template is needed for.
     * @return void
     */
    public function bakeTemplate($name)
    {
        $restore = $this->pathFragment;

        $this->pathFragment = 'Template/Cell/';
        $path = $this->getPath();
        $path .= implode(DS, [$name, 'display.ctp']);

        $this->pathFragment = $restore;

        $this->createFile($path, '');
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser
        ->addOption('prefix', [
            'help' => 'The namespace prefix to use.'
        ]);

        return $parser;
    }
}
