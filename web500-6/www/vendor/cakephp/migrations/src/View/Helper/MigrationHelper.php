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
namespace Migrations\View\Helper;

use Cake\Database\Schema\Table;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Cake\View\Helper;
use Cake\View\View;

/**
 * Migration Helper class for output of field data in migration files.
 *
 * MigrationHelper encloses all methods needed while working with HTML pages.
 */
class MigrationHelper extends Helper
{

    /**
     * Schemas list for tables analyzed during migration baking
     *
     * @var array
     */
    protected $schemas = [];

    /**
     * Stores the ``$this->table()`` statements issued while baking.
     * It helps prevent duplicate calls in case of complex conditions
     *
     * @var array
     */
    public $tableStatements = [];

    public $returnedData = [];

    /**
     * Constructor
     *
     * ### Settings
     *
     * - `collection` \Cake\Database\Schema\Collection
     *
     * @param \Cake\View\View $View The View this helper is being attached to.
     * @param array $config Configuration settings for the helper.
     */
    public function __construct(View $View, array $config = [])
    {
        parent::__construct($View, $config);
    }

    /**
     * Returns the method to be used for the Table::save()
     *
     * @param string $action Name of action to take against the table
     * @return string
     */
    public function tableMethod($action)
    {
        if ($action === 'drop_table') {
            return 'drop';
        }

        if ($action === 'create_table') {
            return 'create';
        }

        return 'update';
    }

    /**
     * Returns the method to be used for the index manipulation
     *
     * @param string $action Name of action to take against the table
     * @return string
     */
    public function indexMethod($action)
    {
        if ($action === 'drop_field') {
            return 'removeIndex';
        }

        return 'addIndex';
    }

    /**
     * Returns the method to be used for the column manipulation
     *
     * @param string $action Name of action to take against the table
     * @return string
     */
    public function columnMethod($action)
    {
        if ($action === 'drop_field') {
            return 'removeColumn';
        }

        return 'addColumn';
    }

    /**
     * Returns the Cake\Database\Schema\Table for $table
     *
     * @param string $table Name of the table to get the Schema for
     * @return \Cake\Database\Schema\Table
     */
    protected function schema($table)
    {
        if (isset($this->schemas[$table])) {
            return $this->schemas[$table];
        }

        if ($table instanceof Table) {
            return $this->schemas[$table->name()] = $table;
        }

        $collection = $this->config('collection');
        $schema = $collection->describe($table);
        $this->schemas[$table] = $schema;

        return $schema;
    }

    /**
     * Returns an array of column data for a given table
     *
     * @param string $table Name of the table to retrieve columns for
     * @return array
     */
    public function columns($table)
    {
        $tableSchema = $table;
        if (!($table instanceof Table)) {
            $tableSchema = $this->schema($table);
        }
        $columns = [];
        $tablePrimaryKeys = $tableSchema->primaryKey();
        foreach ($tableSchema->columns() as $column) {
            if (in_array($column, $tablePrimaryKeys)) {
                continue;
            }
            $columns[$column] = $this->column($tableSchema, $column);
        }

        return $columns;
    }

    /**
     * Returns an array of indexes for a given table
     *
     * @param string $table Name of the table to retrieve indexes for
     * @return array
     */
    public function indexes($table)
    {
        $tableSchema = $table;
        if (!($table instanceof Table)) {
            $tableSchema = $this->schema($table);
        }

        $tableIndexes = $tableSchema->indexes();
        $indexes = [];
        if (!empty($tableIndexes)) {
            foreach ($tableIndexes as $name) {
                $indexes[$name] = $tableSchema->index($name);
            }
        }

        return $indexes;
    }

    /**
     * Returns an array of constraints for a given table
     *
     * @param string $table Name of the table to retrieve constraints for
     * @return array
     */
    public function constraints($table)
    {
        $tableSchema = $table;
        if (!($table instanceof Table)) {
            $tableSchema = $this->schema($table);
        }

        $constraints = [];
        $tableConstraints = $tableSchema->constraints();
        if (empty($tableConstraints)) {
            return $constraints;
        }

        if ($tableConstraints[0] === 'primary') {
            unset($tableConstraints[0]);
        }
        if (!empty($tableConstraints)) {
            foreach ($tableConstraints as $name) {
                $constraint = $tableSchema->constraint($name);
                if (isset($constraint['update'])) {
                    $constraint['update'] = $this->formatConstraintAction($constraint['update']);
                    $constraint['delete'] = $this->formatConstraintAction($constraint['delete']);
                }
                $constraints[$name] = $constraint;
            }
        }

        return $constraints;
    }

    /**
     * Format a constraint action if it is not already in the format expected by Phinx
     *
     * @param string $constraint Constraint action name
     * @return string Constraint action name altered if needed.
     */
    public function formatConstraintAction($constraint)
    {
        if (defined('\Phinx\Db\Table\ForeignKey::' . $constraint)) {
            return $constraint;
        }

        return strtoupper(Inflector::underscore($constraint));
    }

    /**
     * Returns the primary key data for a given table
     *
     * @param string $table Name of the table ot retrieve primary key for
     * @return array
     */
    public function primaryKeys($table)
    {
        $tableSchema = $table;
        if (!($table instanceof Table)) {
            $tableSchema = $this->schema($table);
        }
        $primaryKeys = [];
        $tablePrimaryKeys = $tableSchema->primaryKey();
        foreach ($tableSchema->columns() as $column) {
            if (in_array($column, $tablePrimaryKeys)) {
                $primaryKeys[] = ['name' => $column, 'info' => $this->column($tableSchema, $column)];
            }
        }

        return $primaryKeys;
    }

    /**
     * Returns whether the $tables list given as arguments contains primary keys
     * unsigned.
     *
     * @param array $tables List of tables to check
     * @return bool
     */
    public function hasUnsignedPrimaryKey($tables)
    {
        foreach ($tables as $table) {
            $tableSchema = $table;
            if (!($table instanceof Table)) {
                $tableSchema = $this->schema($table);
            }
            $tablePrimaryKeys = $tableSchema->primaryKey();

            foreach ($tablePrimaryKeys as $primaryKey) {
                $column = $tableSchema->column($primaryKey);
                if (isset($column['unsigned']) && $column['unsigned'] === true) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Returns the primary key columns name for a given table
     *
     * @param string $table Name of the table ot retrieve primary key for
     * @return array
     */
    public function primaryKeysColumnsList($table)
    {
        $primaryKeys = $this->primaryKeys($table);
        $primaryKeysColumns = Hash::extract($primaryKeys, '{n}.name');
        sort($primaryKeysColumns);

        return $primaryKeysColumns;
    }

    /**
     * Returns an array of column data for a single column
     *
     * @param \Cake\Database\Schema\Table $tableSchema Name of the table to retrieve columns for
     * @param string $column A column to retrieve data for
     * @return array
     */
    public function column($tableSchema, $column)
    {
        return [
            'columnType' => $tableSchema->columnType($column),
            'options' => $this->attributes($tableSchema, $column),
        ];
    }

    /**
     * Compute the final array of options to display in a `addColumn` or `changeColumn` instruction.
     * The method also takes care of translating properties names between CakePHP database layer and phinx database
     * layer.
     *
     * @param array $options Array of options to compute the final list from.
     * @return array
     */
    public function getColumnOption($options)
    {
        $wantedOptions = array_flip([
            'length',
            'limit',
            'default',
            'signed',
            'null',
            'comment',
            'autoIncrement',
            'precision',
            'after'
        ]);
        $columnOptions = array_intersect_key($options, $wantedOptions);
        if (empty($columnOptions['comment'])) {
            unset($columnOptions['comment']);
        }
        if (empty($columnOptions['autoIncrement'])) {
            unset($columnOptions['autoIncrement']);
        }
        if (isset($columnOptions['signed']) && $columnOptions['signed'] === true) {
            unset($columnOptions['signed']);
        }
        if (empty($columnOptions['precision'])) {
            unset($columnOptions['precision']);
        } else {
            // due to Phinx using different naming for the precision and scale to CakePHP
            $columnOptions['scale'] = $columnOptions['precision'];

            if (isset($columnOptions['limit'])) {
                $columnOptions['precision'] = $columnOptions['limit'];
                unset($columnOptions['limit']);
            }
            if (isset($columnOptions['length'])) {
                $columnOptions['precision'] = $columnOptions['length'];
                unset($columnOptions['length']);
            }
        }

        return $columnOptions;
    }

    /**
     * Returns a string-like representation of a value
     *
     * @param string $value A value to represent as a string
     * @param bool $numbersAsString Set tu true to return as string.
     * @return mixed
     */
    public function value($value, $numbersAsString = false)
    {
        if ($value === null || $value === 'null' || $value === 'NULL') {
            return 'null';
        }

        if ($value === 'true' || $value === 'false') {
            return $value;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (!$numbersAsString && (is_numeric($value) || ctype_digit($value))) {
            return (float)$value;
        }

        return sprintf("'%s'", addslashes($value));
    }

    /**
     * Returns an array of attributes for a given table column
     *
     * @param string $table Name of the table to retrieve columns for
     * @param string $column A column to retrieve attributes for
     * @return array
     */
    public function attributes($table, $column)
    {
        $tableSchema = $table;
        if (!($table instanceof Table)) {
            $tableSchema = $this->schema($table);
        }
        $validOptions = [
            'length', 'limit',
            'default', 'null',
            'precision', 'scale',
            'after', 'update',
            'comment', 'unsigned',
            'signed', 'properties',
            'autoIncrement'
        ];

        $attributes = [];
        $options = $tableSchema->column($column);
        foreach ($options as $_option => $value) {
            $option = $_option;
            switch ($_option) {
                case 'length':
                    $option = 'limit';
                    break;
                case 'unsigned':
                    $option = 'signed';
                    $value = (bool)!$value;
                    break;
                case 'unique':
                    $value = (bool)$value;
                    break;
            }

            if (!in_array($option, $validOptions)) {
                continue;
            }

            $attributes[$option] = $value;
        }

        ksort($attributes);

        return $attributes;
    }

    /**
     * Returns an array converted into a formatted multiline string
     *
     * @param array $list array of items to be stringified
     * @param array $options options to use
     * @return string
     */
    public function stringifyList(array $list, array $options = [])
    {
        $options += [
            'indent' => 2
        ];

        if (!$list) {
            return '';
        }

        ksort($list);
        foreach ($list as $k => &$v) {
            if (is_array($v)) {
                $v = $this->stringifyList($v, [
                    'indent' => $options['indent'] + 1
                ]);
                $v = sprintf('[%s]', $v);
            } else {
                $v = $this->value($v, $k === 'default');
            }
            if (!is_numeric($k)) {
                $v = "'$k' => $v";
            }
        }

        $start = $end = '';
        $join = ', ';
        if ($options['indent']) {
            $join = ',';
            $start = "\n" . str_repeat("    ", $options['indent']);
            $join .= $start;
            $end = "\n" . str_repeat("    ", $options['indent'] - 1);
        }

        return $start . implode($join, $list) . ',' . $end;
    }

    /**
     * Returns a $this->table() statement only if it was not issued already
     *
     * @param string $table Table for which the statement is needed
     * @param bool $reset Reset previously set statement.
     * @return string
     */
    public function tableStatement($table, $reset = false)
    {
        if ($reset === true) {
            unset($this->tableStatements[$table]);
        }

        if (!isset($this->tableStatements[$table])) {
            $this->tableStatements[$table] = true;

            return '$this->table(\'' . $table . '\')';
        }

        return '';
    }
}
