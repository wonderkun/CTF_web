<?php
/**
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Migrations;

use Cake\Core\App;
use Cake\Core\Plugin;
use Cake\Database\Schema\Collection;
use Cake\Datasource\ConnectionManager;
use Cake\Filesystem\Folder;
use Cake\ORM\TableRegistry;
use ReflectionClass;

trait TableFinderTrait
{

    /**
     * Tables to skip
     *
     * @var array
     */
    public $skipTables = ['phinxlog'];

    /**
     * Regex of Table name to skip
     *
     * @var string
     */
    public $skipTablesRegex = '_phinxlog';

    /**
     * Gets a list of table to baked based on the Collection instance passed and the options passed to
     * the shell call.
     *
     * @param \Cake\Database\Schema\Collection $collection Instance of the collection of a specific database
     * connection.
     * @param array $options Array of options passed to a shell call.
     * @return array
     */
    protected function getTablesToBake(Collection $collection, $options = [])
    {
        $options = array_merge(['require-table' => false, 'plugin' => null], $options);
        $tables = $collection->listTables();

        if (empty($tables)) {
            return $tables;
        }

        if ($options['require-table'] === true || $options['plugin']) {
            $tableNamesInModel = $this->getTableNames($options['plugin']);

            if (empty($tableNamesInModel)) {
                return [];
            }

            foreach ($tableNamesInModel as $num => $table) {
                if (strpos($table, '.') !== false) {
                    $splitted = array_reverse(explode('.', $table, 2));

                    $config = ConnectionManager::config($this->connection);
                    $key = isset($config['schema']) ? 'schema' : 'database';
                    if ($config[$key] === $splitted[1]) {
                        $table = $splitted[0];
                    }
                }

                if (!in_array($table, $tables)) {
                    unset($tableNamesInModel[$num]);
                }
            }
            $tables = $tableNamesInModel;
        } else {
            foreach ($tables as $num => $table) {
                if ((in_array($table, $this->skipTables)) || (strpos($table, $this->skipTablesRegex) !== false)) {
                    unset($tables[$num]);
                    continue;
                }
            }
        }

        return $tables;
    }

    /**
     * Gets list Tables Names
     *
     * @param string|null $pluginName Plugin name if exists.
     * @return array
     */
    protected function getTableNames($pluginName = null)
    {
        if ($pluginName !== null && !Plugin::loaded($pluginName)) {
            return [];
        }
        $list = [];
        $tables = $this->findTables($pluginName);

        if (empty($tables)) {
            return [];
        }

        foreach ($tables as $num => $table) {
            $list = array_merge($list, $this->fetchTableName($table, $pluginName));
        }

        return array_unique($list);
    }

    /**
     * Find Table Class
     *
     * @param string $pluginName Plugin name if exists.
     * @return array
     */
    protected function findTables($pluginName = null)
    {
        $path = 'Model' . DS . 'Table' . DS;
        if ($pluginName) {
            $path = Plugin::path($pluginName) . 'src' . DS . $path;
        } else {
            $path = APP . $path;
        }

        if (!is_dir($path)) {
            return [];
        }

        $tableDir = new Folder($path);
        $tableDir = $tableDir->find('.*\.php');

        return $tableDir;
    }

    /**
     * fetch TableName From Table Object
     *
     * @param string $className Name of Table Class.
     * @param string|null $pluginName Plugin name if exists.
     * @return array
     */
    protected function fetchTableName($className, $pluginName = null)
    {
        $tables = [];
        $className = str_replace('Table.php', '', $className);
        if ($pluginName !== null) {
            $className = $pluginName . '.' . $className;
        }

        $namespacedClassName = App::className($className, 'Model/Table', 'Table');

        if (!class_exists($namespacedClassName)) {
            return $tables;
        }

        $reflection = new ReflectionClass($namespacedClassName);
        if (!$reflection->isInstantiable()) {
            return $tables;
        }

        $table = TableRegistry::get($className);
        foreach ($table->associations()->keys() as $key) {
            if ($table->associations()->get($key)->type() === 'belongsToMany') {
                $tables[] = $table->associations()->get($key)->junction()->table();
            }
        }
        $tableName = $table->table();
        $splitted = array_reverse(explode('.', $tableName, 2));
        if (isset($splitted[1])) {
            $config = ConnectionManager::config($this->connection);
            $key = isset($config['schema']) ? 'schema' : 'database';
            if ($config[$key] === $splitted[1]) {
                $tableName = $splitted[0];
            }
        }
        $tables[] = $tableName;

        return $tables;
    }
}
