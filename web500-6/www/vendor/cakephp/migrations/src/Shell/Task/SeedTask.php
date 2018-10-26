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
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Datasource\ConnectionManager;
use Cake\Utility\Inflector;

/**
 * Task class for generating seed files.
 *
 * @property \Bake\Shell\Task\BakeTemplateTask $BakeTemplate
 * @property \Bake\Shell\Task\TestTask $Test
 */
class SeedTask extends SimpleBakeTask
{
    /**
     * path to Migration directory
     *
     * @var string
     */
    public $pathFragment = 'config/Seeds/';

    /**
     * {@inheritDoc}
     */
    public function name()
    {
        return 'seed';
    }

    /**
     * {@inheritDoc}
     */
    public function fileName($name)
    {
        return Inflector::camelize($name) . 'Seed.php';
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
    public function template()
    {
        return 'Migrations.Seed/seed';
    }

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

        $table = Inflector::tableize($this->args[0]);
        if (!empty($this->params['table'])) {
            $table = $this->params['table'];
        }

        $records = false;
        if ($this->param('data')) {
            $limit = (int)$this->param('limit');

            $fields = $this->param('fields') ?: '*';
            if ($fields !== '*') {
                $fields = explode(',', $fields);
            }

            $connection = ConnectionManager::get($this->connection);

            $query = $connection->newQuery()
                ->from($table)
                ->select($fields);

            if ($limit) {
                $query->limit($limit);
            }

            $records = $connection->execute($query)->fetchAll('assoc');
            $records = $this->prettifyArray($records);
        }

        return [
            'className' => $this->BakeTemplate->viewVars['name'],
            'namespace' => $namespace,
            'records' => $records,
            'table' => $table,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function bake($name)
    {
        $this->params['no-test'] = true;

        return parent::bake($name);
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
            'Bake seed class.'
        )->addOption('plugin', [
            'short' => 'p',
            'help' => 'Plugin to bake into.'
        ])->addOption('force', [
            'short' => 'f',
            'boolean' => true,
            'help' => 'Force overwriting existing files without prompting.'
        ])->addOption('connection', [
            'short' => 'c',
            'default' => 'default',
            'help' => 'The datasource connection to get data from.'
        ])->addOption('table', [
            'help' => 'The database table to use.'
        ])->addOption('theme', [
            'short' => 't',
            'help' => 'The theme to use when baking code.',
            'choices' => $bakeThemes
        ])->addArgument('name', [
            'help' => 'Name of the seed to bake. Can use Plugin.name to bake plugin models.'
        ])->addOption('data', [
            'boolean' => true,
            'help' => 'Include data from the table to the seed'
        ])->addOption('fields', [
            'default' => '*',
            'help' => 'If including data, comma separated list of fields to select (all fields by default)',
        ])->addOption('limit', [
            'short' => 'l',
            'help' => 'If including data, max number of rows to select'
        ]);

        return $parser;
    }

    /**
     * Prettify var_export of an array output
     *
     * @param array $array              Array to prettify
     * @param int $tabCount             Initial tab count
     * @param string $indentCharacter   Desired indent for the code.
     * @return string
     */
    protected function prettifyArray($array, $tabCount = 3, $indentCharacter = "    ")
    {
        $content = var_export($array, true);

        $lines = explode("\n", $content);

        $inString = false;

        foreach ($lines as $k => &$line) {
            if ($k === 0) {
                // First row
                $line = '[';
                continue;
            }

            if ($k === count($lines) - 1) {
                // Last row
                $line = str_repeat($indentCharacter, --$tabCount) . ']';
                continue;
            }

            $line = ltrim($line);

            if (!$inString) {
                if ($line === '),') {
                    //Check for closing bracket
                    $line = '],';
                    $tabCount--;
                } elseif (preg_match("/^\d+\s\=\>\s$/", $line)) {
                    // Mark '0 =>' kind of lines to remove
                    $line = false;
                    continue;
                }

                //Insert tab count
                $line = str_repeat($indentCharacter, $tabCount) . $line;
            }

            $length = strlen($line);
            for ($j = 0; $j < $length; $j++) {
                if ($line[$j] === '\\') {
                    //skip character right after an escape \
                    $j++;
                } elseif ($line[$j] === '\'') {
                    //check string open/end
                    $inString = !$inString;
                }
            }

            //check for opening bracket
            if (!$inString && trim($line) === 'array (') {
                $line = str_replace('array (', '[', $line);
                $tabCount++;
            }
        }
        unset($line);

        // Remove marked lines
        $lines = array_filter($lines, function ($line) {
            return $line !== false;
        });

        return implode("\n", $lines);
    }
}
