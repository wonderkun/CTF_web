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
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Migrations\Shell\Task;

use Bake\Shell\Task\SimpleBakeTask;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Plugin;
use Cake\Utility\Inflector;
use Phinx\Util\Util;

/**
 * Task class for generating migration snapshot files.
 */
abstract class SimpleMigrationTask extends SimpleBakeTask
{
    /**
     * path to Migration directory
     *
     * @var string
     */
    public $pathFragment = 'config/Migrations/';

    /**
     * {@inheritDoc}
     */
    public function name()
    {
        return 'migration';
    }

    /**
     * {@inheritDoc}
     */
    public function fileName($name)
    {
        $name = $this->getMigrationName($name);

        return Util::getCurrentTimestamp() . '_' . Inflector::camelize($name) . '.php';
    }

    /**
     * {@inheritDoc}
     */
    public function getPath()
    {
        $path = ROOT . DS . $this->pathFragment;
        if (isset($this->plugin)) {
            $path = $this->_pluginPath($this->plugin) . $this->pathFragment;
        }

        return str_replace('/', DS, $path);
    }

    /**
     * {@inheritDoc}
     */
    public function bake($name)
    {
        $migrationWithSameName = glob($this->getPath() . '*_' . $name . '.php');
        if (!empty($migrationWithSameName)) {
            $force = $this->param('force');
            if (!$force) {
                $this->abort(
                    sprintf(
                        'A migration with the name `%s` already exists. Please use a different name.',
                        $name
                    )
                );
            }

            $this->info(sprintf('A migration with the name `%s` already exists, it will be deleted.', $name));
            foreach ($migrationWithSameName as $migration) {
                $this->info(sprintf('Deleting migration file `%s`...', $migration));
                if (unlink($migration)) {
                    $this->success(sprintf('Deleted `%s`', $migration));
                } else {
                    $this->err(sprintf('An error occurred while deleting `%s`', $migration));
                }
            }
        }

        $this->params['no-test'] = true;

        return parent::bake($name);
    }

    /**
     * Returns a class name for the migration class
     *
     * If the name is invalid, the task will exit
     *
     * @param string|null $name Name for the generated migration
     * @return string|null Name of the migration file or null if empty
     */
    protected function getMigrationName($name = null)
    {
        if (empty($name)) {
            $this->abort('Choose a migration name to bake in CamelCase format');
        }

        $name = $this->_getName($name);
        $name = Inflector::camelize($name);

        if (!preg_match('/^[A-Z]{1}[a-zA-Z0-9]+$/', $name)) {
            $this->abort('The className is not correct. The className can only contain "A-Z" and "0-9".');
        }

        return $name;
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $name = ($this->plugin ? $this->plugin . '.' : '') . $this->name;
        $parser = new ConsoleOptionParser($name);

        $bakeThemes = [];
        foreach (Plugin::loaded() as $plugin) {
            $path = Plugin::classPath($plugin);
            if (is_dir($path . 'Template' . DS . 'Bake')) {
                $bakeThemes[] = $plugin;
            }
        }

        $parser->description(
            'Bake migration class.'
        )
        ->addOption('plugin', [
            'short' => 'p',
            'help' => 'Plugin to bake into.'
        ])
        ->addOption('force', [
            'short' => 'f',
            'boolean' => true,
            'help' => 'Force overwriting existing file if a migration already exists with the same name.'
        ])
        ->addOption('connection', [
            'short' => 'c',
            'default' => 'default',
            'help' => 'The datasource connection to get data from.'
        ])
        ->addOption('theme', [
            'short' => 't',
            'help' => 'The theme to use when baking code.',
            'choices' => $bakeThemes
        ]);

        return $parser;
    }
}
