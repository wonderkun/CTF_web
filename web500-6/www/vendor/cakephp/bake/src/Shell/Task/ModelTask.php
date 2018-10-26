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
use Cake\Database\Schema\TableSchema;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;

/**
 * Task class for generating model files.
 *
 * @property \Bake\Shell\Task\FixtureTask $Fixture
 * @property \Bake\Shell\Task\BakeTemplateTask $BakeTemplate
 * @property \Bake\Shell\Task\TestTask $Test
 */
class ModelTask extends BakeTask
{
    /**
     * path to Model directory
     *
     * @var string
     */
    public $pathFragment = 'Model/';

    /**
     * tasks
     *
     * @var array
     */
    public $tasks = [
        'Bake.Fixture',
        'Bake.BakeTemplate',
        'Bake.Test'
    ];

    /**
     * Tables to skip when running all()
     *
     * @var array
     */
    public $skipTables = ['i18n', 'cake_sessions', 'phinxlog', 'users_phinxlog'];

    /**
     * Holds tables found on connection.
     *
     * @var array
     */
    protected $_tables = [];

    /**
     * Holds the model names
     *
     * @var array
     */
    protected $_modelNames = [];

    /**
     * Holds validation method map.
     *
     * @var array
     */
    protected $_validations = [];

    /**
     * Execution method always used for tasks
     *
     * @param string|null $name The name of the table to bake.
     * @return void
     */
    public function main($name = null)
    {
        parent::main();
        $name = $this->_getName($name);

        if (empty($name)) {
            $this->out('Choose a model to bake from the following:');
            foreach ($this->listUnskipped() as $table) {
                $this->out('- ' . $this->_camelize($table));
            }

            return;
        }

        $this->bake($this->_camelize($name));
    }

    /**
     * Generate code for the given model name.
     *
     * @param string $name The model name to generate.
     * @return void
     */
    public function bake($name)
    {
        $table = $this->getTable($name);
        $tableObject = $this->getTableObject($name, $table);
        $data = $this->getTableContext($tableObject, $table, $name);
        $this->bakeTable($tableObject, $data);
        $this->bakeEntity($tableObject, $data);
        $this->bakeFixture($tableObject->getAlias(), $tableObject->getTable());
        $this->bakeTest($tableObject->getAlias());
    }

    /**
     * Get table context for baking a given table.
     *
     * @param \Cake\ORM\Table $tableObject The model name to generate.
     * @param string $table The table name for the model being baked.
     * @param string $name The model name to generate.
     * @return array
     */
    public function getTableContext($tableObject, $table, $name)
    {
        $associations = $this->getAssociations($tableObject);
        $this->applyAssociations($tableObject, $associations);
        $associationInfo = $this->getAssociationInfo($tableObject);

        $primaryKey = $this->getPrimaryKey($tableObject);
        $displayField = $this->getDisplayField($tableObject);
        $propertySchema = $this->getEntityPropertySchema($tableObject);
        $fields = $this->getFields($tableObject);
        $validation = $this->getValidation($tableObject, $associations);
        $rulesChecker = $this->getRules($tableObject, $associations);
        $behaviors = $this->getBehaviors($tableObject);
        $connection = $this->connection;
        $hidden = $this->getHiddenFields($tableObject);

        return compact(
            'associations',
            'associationInfo',
            'primaryKey',
            'displayField',
            'table',
            'propertySchema',
            'fields',
            'validation',
            'rulesChecker',
            'behaviors',
            'connection',
            'hidden'
        );
    }

    /**
     * Bake all models at once.
     *
     * @return void
     */
    public function all()
    {
        $tables = $this->listUnskipped();
        foreach ($tables as $table) {
            TableRegistry::clear();
            $this->main($table);
        }
    }

    /**
     * Get a model object for a class name.
     *
     * @param string $className Name of class you want model to be.
     * @param string $table Table name
     * @return \Cake\ORM\Table Table instance
     */
    public function getTableObject($className, $table)
    {
        $plugin = $this->param('plugin');
        if (!empty($plugin)) {
            $className = $plugin . '.' . $className;
        }

        if (TableRegistry::exists($className)) {
            return TableRegistry::get($className);
        }

        return TableRegistry::get($className, [
            'name' => $className,
            'table' => $this->tablePrefix . $table,
            'connection' => ConnectionManager::get($this->connection)
        ]);
    }

    /**
     * Get the array of associations to generate.
     *
     * @param \Cake\ORM\Table $table The table to get associations for.
     * @return array
     */
    public function getAssociations(Table $table)
    {
        if (!empty($this->params['no-associations'])) {
            return [];
        }
        $this->out('One moment while associations are detected.');

        $this->listAll();

        $associations = [
            'belongsTo' => [],
            'hasMany' => [],
            'belongsToMany' => []
        ];

        $primary = $table->getPrimaryKey();
        $associations = $this->findBelongsTo($table, $associations);

        if (is_array($primary) && count($primary) > 1) {
            $this->err(
                '<warning>Bake cannot generate associations for composite primary keys at this time</warning>.'
            );

            return $associations;
        }

        $associations = $this->findHasMany($table, $associations);
        $associations = $this->findBelongsToMany($table, $associations);

        return $associations;
    }

    /**
     * Sync the in memory table object.
     *
     * Composer's class cache prevents us from loading the
     * newly generated class. Applying associations if we have a
     * generic table object means fields will be detected correctly.
     *
     * @param \Cake\ORM\Table $model The table to apply associations to.
     * @param array $associations The associations to append.
     * @return void
     */
    public function applyAssociations($model, $associations)
    {
        if (get_class($model) !== 'Cake\ORM\Table') {
            return;
        }
        foreach ($associations as $type => $assocs) {
            foreach ($assocs as $assoc) {
                $alias = $assoc['alias'];
                unset($assoc['alias']);
                $model->{$type}($alias, $assoc);
            }
        }
    }

    /**
     * Collects meta information for associations.
     *
     * The information returned is in the format of map, where the key is the
     * association alias:
     *
     * ```
     * [
     *     'associationAlias' => [
     *         'targetFqn' => '...'
     *     ],
     *     // ...
     * ]
     * ```
     *
     * @param \Cake\ORM\Table $table The table from which to collect association information.
     * @return array A map of association information.
     */
    public function getAssociationInfo(Table $table)
    {
        $info = [];

        $appNamespace = Configure::read('App.namespace');

        foreach ($table->associations() as $association) {
            /* @var $association \Cake\ORM\Association */

            $tableClass = get_class($association->getTarget());
            if ($tableClass === 'Cake\ORM\Table') {
                $namespace = $appNamespace;

                $className = $association->className();
                if ($className !== null) {
                    list($plugin, $className) = pluginSplit($className);
                    if ($plugin !== null) {
                        $namespace = $plugin;
                    }
                } else {
                    $className = $association->getTarget()->getAlias();
                }

                $namespace = str_replace('/', '\\', trim($namespace, '\\'));
                $tableClass = $namespace . '\Model\Table\\' . $className . 'Table';
            }

            $info[$association->getName()] = [
                'targetFqn' => '\\' . $tableClass
            ];
        }

        return $info;
    }

    /**
     * Find belongsTo relations and add them to the associations list.
     *
     * @param \Cake\ORM\Table $model Database\Table instance of table being generated.
     * @param array $associations Array of in progress associations
     * @return array Associations with belongsTo added in.
     */
    public function findBelongsTo($model, array $associations)
    {
        $schema = $model->getSchema();
        foreach ($schema->columns() as $fieldName) {
            if (!preg_match('/^.+_id$/', $fieldName) || ([$fieldName] === $schema->primaryKey())) {
                continue;
            }

            if ($fieldName === 'parent_id') {
                $className = ($this->plugin) ? $this->plugin . '.' . $model->getAlias() : $model->getAlias();
                $assoc = [
                    'alias' => 'Parent' . $model->getAlias(),
                    'className' => $className,
                    'foreignKey' => $fieldName
                ];
            } else {
                $tmpModelName = $this->_modelNameFromKey($fieldName);
                if (!in_array(Inflector::tableize($tmpModelName), $this->_tables)) {
                    $found = $this->findTableReferencedBy($schema, $fieldName);
                    if ($found) {
                        $tmpModelName = Inflector::camelize($found);
                    }
                }
                $assoc = [
                    'alias' => $tmpModelName,
                    'foreignKey' => $fieldName
                ];
                if ($schema->getColumn($fieldName)['null'] === false) {
                    $assoc['joinType'] = 'INNER';
                }
            }

            if ($this->plugin && empty($assoc['className'])) {
                $assoc['className'] = $this->plugin . '.' . $assoc['alias'];
            }
            $associations['belongsTo'][] = $assoc;
        }

        return $associations;
    }

    /**
     * find the table, if any, actually referenced by the passed key field.
     * Search tables in db for keyField; if found search key constraints
     * for the table to which it refers.
     *
     * @param \Cake\Database\Schema\TableSchema $schema The table schema to find a constraint for.
     * @param string $keyField The field to check for a constraint.
     * @return string|null Either the referenced table or null if the field has no constraints.
     */
    public function findTableReferencedBy($schema, $keyField)
    {
        if (!$schema->getColumn($keyField)) {
            return null;
        }

        foreach ($schema->constraints() as $constraint) {
            $constraintInfo = $schema->getConstraint($constraint);
            if (!in_array($keyField, $constraintInfo['columns'])) {
                continue;
            }

            if (!isset($constraintInfo['references'])) {
                continue;
            }
            $length = mb_strlen($this->tablePrefix);
            if ($length > 0 && mb_substr($constraintInfo['references'][0], 0, $length) === $this->tablePrefix) {
                return mb_substr($constraintInfo['references'][0], $length);
            }

            return $constraintInfo['references'][0];
        }

        return null;
    }

    /**
     * Find the hasMany relations and add them to associations list
     *
     * @param \Cake\ORM\Table $model Model instance being generated
     * @param array $associations Array of in progress associations
     * @return array Associations with hasMany added in.
     */
    public function findHasMany($model, array $associations)
    {
        $schema = $model->getSchema();
        $primaryKey = (array)$schema->primaryKey();
        $tableName = $schema->name();
        $foreignKey = $this->_modelKey($tableName);

        $tables = $this->listAll();
        foreach ($tables as $otherTableName) {
            $otherModel = $this->getTableObject($this->_camelize($otherTableName), $otherTableName);
            $otherSchema = $otherModel->getSchema();

            $pregTableName = preg_quote($tableName, '/');
            $pregPattern = "/^{$pregTableName}_|_{$pregTableName}$/";
            if (preg_match($pregPattern, $otherTableName) === 1) {
                $possibleHABTMTargetTable = preg_replace($pregPattern, '', $otherTableName);
                if (in_array($possibleHABTMTargetTable, $tables)) {
                    continue;
                }
            }

            foreach ($otherSchema->columns() as $fieldName) {
                $assoc = false;
                if (!in_array($fieldName, $primaryKey) && $fieldName === $foreignKey) {
                    $assoc = [
                        'alias' => $otherModel->getAlias(),
                        'foreignKey' => $fieldName
                    ];
                } elseif ($otherTableName === $tableName && $fieldName === 'parent_id') {
                    $className = ($this->plugin) ? $this->plugin . '.' . $model->getAlias() : $model->getAlias();
                    $assoc = [
                        'alias' => 'Child' . $model->getAlias(),
                        'className' => $className,
                        'foreignKey' => $fieldName
                    ];
                }
                if ($assoc && $this->plugin && empty($assoc['className'])) {
                    $assoc['className'] = $this->plugin . '.' . $assoc['alias'];
                }
                if ($assoc) {
                    $associations['hasMany'][] = $assoc;
                }
            }
        }

        return $associations;
    }

    /**
     * Find the BelongsToMany relations and add them to associations list
     *
     * @param \Cake\ORM\Table $model Model instance being generated
     * @param array $associations Array of in-progress associations
     * @return array Associations with belongsToMany added in.
     */
    public function findBelongsToMany($model, array $associations)
    {
        $schema = $model->getSchema();
        $tableName = $schema->name();
        $foreignKey = $this->_modelKey($tableName);

        $tables = $this->listAll();
        foreach ($tables as $otherTableName) {
            $assocTable = null;
            $offset = strpos($otherTableName, $tableName . '_');
            $otherOffset = strpos($otherTableName, '_' . $tableName);

            if ($offset !== false) {
                $assocTable = substr($otherTableName, strlen($tableName . '_'));
            } elseif ($otherOffset !== false) {
                $assocTable = substr($otherTableName, 0, $otherOffset);
            }
            if ($assocTable && in_array($assocTable, $tables)) {
                $habtmName = $this->_camelize($assocTable);
                $assoc = [
                    'alias' => $habtmName,
                    'foreignKey' => $foreignKey,
                    'targetForeignKey' => $this->_modelKey($habtmName),
                    'joinTable' => $otherTableName
                ];
                if ($assoc && $this->plugin) {
                    $assoc['className'] = $this->plugin . '.' . $assoc['alias'];
                }
                $associations['belongsToMany'][] = $assoc;
            }
        }

        return $associations;
    }

    /**
     * Get the display field from the model or parameters
     *
     * @param \Cake\ORM\Table $model The model to introspect.
     * @return string
     */
    public function getDisplayField($model)
    {
        if (!empty($this->params['display-field'])) {
            return $this->params['display-field'];
        }

        return $model->getDisplayField();
    }

    /**
     * Get the primary key field from the model or parameters
     *
     * @param \Cake\ORM\Table $model The model to introspect.
     * @return array The columns in the primary key
     */
    public function getPrimaryKey($model)
    {
        if (!empty($this->params['primary-key'])) {
            $fields = explode(',', $this->params['primary-key']);

            return array_values(array_filter(array_map('trim', $fields)));
        }

        return (array)$model->getPrimaryKey();
    }

    /**
     * Returns an entity property "schema".
     *
     * The schema is an associative array, using the property names
     * as keys, and information about the property as the value.
     *
     * The value part consists of at least two keys:
     *
     * - `kind`: The kind of property, either `column`, which indicates
     * that the property stems from a database column, or `association`,
     * which identifies a property that is generated for an associated
     * table.
     * - `type`: The type of the property value. For the `column` kind
     * this is the database type associated with the column, and for the
     * `association` type it's the FQN of the entity class for the
     * associated table.
     *
     * For `association` properties an additional key will be available
     *
     * - `association`: Holds an instance of the corresponding association
     * class.
     *
     * @param \Cake\ORM\Table $model The model to introspect.
     * @return array The property schema
     */
    public function getEntityPropertySchema(Table $model)
    {
        $properties = [];

        $schema = $model->getSchema();
        foreach ($schema->columns() as $column) {
            $properties[$column] = [
                'kind' => 'column',
                'type' => $schema->getColumnType($column)
            ];
        }

        foreach ($model->associations() as $association) {
            $entityClass = '\\' . ltrim($association->getTarget()->getEntityClass(), '\\');

            if ($entityClass === '\Cake\ORM\Entity') {
                $namespace = Configure::read('App.namespace');

                list($plugin, ) = pluginSplit($association->getTarget()->getRegistryAlias());
                if ($plugin !== null) {
                    $namespace = $plugin;
                }
                $namespace = str_replace('/', '\\', trim($namespace, '\\'));

                $entityClass = $this->_entityName($association->getTarget()->getAlias());
                $entityClass = '\\' . $namespace . '\Model\Entity\\' . $entityClass;
            }

            $properties[$association->getProperty()] = [
                'kind' => 'association',
                'association' => $association,
                'type' => $entityClass
            ];
        }

        return $properties;
    }

    /**
     * Evaluates the fields and no-fields options, and
     * returns if, and which fields should be made accessible.
     *
     * If no fields are specified and the `no-fields` parameter is
     * not set, then all non-primary key fields + association
     * fields will be set as accessible.
     *
     * @param \Cake\ORM\Table $table The table instance to get fields for.
     * @return array|bool|null Either an array of fields, `false` in
     *   case the no-fields option is used, or `null` if none of the
     *   field options is used.
     */
    public function getFields($table)
    {
        if (!empty($this->params['no-fields'])) {
            return false;
        }
        if (!empty($this->params['fields'])) {
            $fields = explode(',', $this->params['fields']);

            return array_values(array_filter(array_map('trim', $fields)));
        }
        $schema = $table->getSchema();
        $fields = $schema->columns();
        foreach ($table->associations() as $assoc) {
            $fields[] = $assoc->getProperty();
        }
        $primaryKey = $schema->primaryKey();

        return array_values(array_diff($fields, $primaryKey));
    }

    /**
     * Get the hidden fields from a model.
     *
     * Uses the hidden and no-hidden options.
     *
     * @param \Cake\ORM\Table $model The model to introspect.
     * @return array The columns to make accessible
     */
    public function getHiddenFields($model)
    {
        if (!empty($this->params['no-hidden'])) {
            return [];
        }
        if (!empty($this->params['hidden'])) {
            $fields = explode(',', $this->params['hidden']);

            return array_values(array_filter(array_map('trim', $fields)));
        }
        $schema = $model->getSchema();
        $columns = $schema->columns();
        $whitelist = ['token', 'password', 'passwd'];

        return array_values(array_intersect($columns, $whitelist));
    }

    /**
     * Generate default validation rules.
     *
     * @param \Cake\ORM\Table $model The model to introspect.
     * @param array $associations The associations list.
     * @return array The validation rules.
     */
    public function getValidation($model, $associations = [])
    {
        if (!empty($this->params['no-validation'])) {
            return [];
        }
        $schema = $model->getSchema();
        $fields = $schema->columns();
        if (empty($fields)) {
            return false;
        }

        $validate = [];
        $primaryKey = (array)$schema->primaryKey();
        $foreignKeys = [];
        if (isset($associations['belongsTo'])) {
            foreach ($associations['belongsTo'] as $assoc) {
                $foreignKeys[] = $assoc['foreignKey'];
            }
        }
        foreach ($fields as $fieldName) {
            if (in_array($fieldName, $foreignKeys)) {
                continue;
            }
            $field = $schema->getColumn($fieldName);
            $validation = $this->fieldValidation($schema, $fieldName, $field, $primaryKey);
            if (!empty($validation)) {
                $validate[$fieldName] = $validation;
            }
        }

        return $validate;
    }

    /**
     * Does individual field validation handling.
     *
     * @param \Cake\Database\Schema\TableSchema $schema The table schema for the current field.
     * @param string $fieldName Name of field to be validated.
     * @param array $metaData metadata for field
     * @param array $primaryKey The primary key field
     * @return array Array of validation for the field.
     */
    public function fieldValidation($schema, $fieldName, array $metaData, $primaryKey)
    {
        $ignoreFields = ['lft', 'rght', 'created', 'modified', 'updated'];
        if (in_array($fieldName, $ignoreFields)) {
            return [];
        }

        $rules = [];
        if ($fieldName === 'email') {
            $rules['email'] = [];
        } elseif ($metaData['type'] === 'uuid') {
            $rules['uuid'] = [];
        } elseif ($metaData['type'] === 'integer') {
            $rules['integer'] = [];
        } elseif ($metaData['type'] === 'float') {
            $rules['numeric'] = [];
        } elseif ($metaData['type'] === 'decimal') {
            $rules['decimal'] = [];
        } elseif ($metaData['type'] === 'boolean') {
            $rules['boolean'] = [];
        } elseif ($metaData['type'] === 'date') {
            $rules['date'] = [];
        } elseif ($metaData['type'] === 'time') {
            $rules['time'] = [];
        } elseif ($metaData['type'] === 'datetime') {
            $rules['dateTime'] = [];
        } elseif ($metaData['type'] === 'timestamp') {
            $rules['dateTime'] = [];
        } elseif ($metaData['type'] === 'inet') {
            $rules['ip'] = [];
        } elseif ($metaData['type'] === 'string' || $metaData['type'] === 'text') {
            $rules['scalar'] = [];
            if ($metaData['length'] > 0) {
                $rules['maxLength'] = [$metaData['length']];
            }
        }

        if (in_array($fieldName, (array)$primaryKey)) {
            $rules['allowEmpty'] = ["'create'"];
        } elseif ($metaData['null'] === true) {
            $rules['allowEmpty'] = [];
        } else {
            $rules['requirePresence'] = ["'create'"];
            $rules['notEmpty'] = [];
        }

        $validation = [];
        foreach ($rules as $rule => $args) {
            $validation[$rule] = [
                'rule' => $rule,
                'args' => $args
            ];
        }

        foreach ($schema->constraints() as $constraint) {
            $constraint = $schema->getConstraint($constraint);
            if (!in_array($fieldName, $constraint['columns']) || count($constraint['columns']) > 1) {
                continue;
            }

            $notDatetime = !in_array($metaData['type'], ['datetime', 'timestamp', 'date', 'time']);
            if ($constraint['type'] === TableSchema::CONSTRAINT_UNIQUE && $notDatetime) {
                $validation['unique'] = ['rule' => 'validateUnique', 'provider' => 'table'];
            }
        }

        return $validation;
    }

    /**
     * Generate default rules checker.
     *
     * @param \Cake\ORM\Table $model The model to introspect.
     * @param array $associations The associations for the model.
     * @return array The rules to be applied.
     */
    public function getRules($model, array $associations)
    {
        if (!empty($this->params['no-rules'])) {
            return [];
        }
        $schema = $model->getSchema();
        $fields = $schema->columns();
        if (empty($fields)) {
            return [];
        }

        $rules = [];
        foreach ($fields as $fieldName) {
            if (in_array($fieldName, ['username', 'email', 'login'])) {
                $rules[$fieldName] = ['name' => 'isUnique'];
            }
        }
        foreach ($schema->constraints() as $name) {
            $constraint = $schema->getConstraint($name);
            if ($constraint['type'] !== TableSchema::CONSTRAINT_UNIQUE) {
                continue;
            }
            if (count($constraint['columns']) > 1) {
                continue;
            }
            $rules[$constraint['columns'][0]] = ['name' => 'isUnique'];
        }

        if (empty($associations['belongsTo'])) {
            return $rules;
        }

        foreach ($associations['belongsTo'] as $assoc) {
            $rules[$assoc['foreignKey']] = ['name' => 'existsIn', 'extra' => $assoc['alias']];
        }

        return $rules;
    }

    /**
     * Get behaviors
     *
     * @param \Cake\ORM\Table $model The model to generate behaviors for.
     * @return array Behaviors
     */
    public function getBehaviors($model)
    {
        $behaviors = [];
        $schema = $model->getSchema();
        $fields = $schema->columns();
        if (empty($fields)) {
            return [];
        }
        if (in_array('created', $fields) || in_array('modified', $fields)) {
            $behaviors['Timestamp'] = [];
        }

        if (in_array('lft', $fields) && $schema->getColumnType('lft') === 'integer' &&
            in_array('rght', $fields) && $schema->getColumnType('rght') === 'integer' &&
            in_array('parent_id', $fields)
        ) {
            $behaviors['Tree'] = [];
        }

        $counterCache = $this->getCounterCache($model);
        if (!empty($counterCache)) {
            $behaviors['CounterCache'] = $counterCache;
        }

        return $behaviors;
    }

    /**
     * Get CounterCaches
     *
     * @param \Cake\ORM\Table $model The table to get counter cache fields for.
     * @return array CounterCache configurations
     */
    public function getCounterCache($model)
    {
        $belongsTo = $this->findBelongsTo($model, ['belongsTo' => []]);
        $counterCache = [];
        foreach ($belongsTo['belongsTo'] as $otherTable) {
            $otherAlias = $otherTable['alias'];
            $otherModel = $this->getTableObject($this->_camelize($otherAlias), Inflector::underscore($otherAlias));

            try {
                $otherSchema = $otherModel->getSchema();
            } catch (\Cake\Database\Exception $e) {
                continue;
            }

            $otherFields = $otherSchema->columns();
            $alias = $model->getAlias();
            $field = Inflector::singularize(Inflector::underscore($alias)) . '_count';
            if (in_array($field, $otherFields, true)) {
                $counterCache[] = "'{$otherAlias}' => ['{$field}']";
            }
        }

        return $counterCache;
    }

    /**
     * Bake an entity class.
     *
     * @param \Cake\ORM\Table $model Model name or object
     * @param array $data An array to use to generate the Table
     * @return string|null
     */
    public function bakeEntity($model, array $data = [])
    {
        if (!empty($this->params['no-entity'])) {
            return null;
        }
        $name = $this->_entityName($model->getAlias());

        $namespace = Configure::read('App.namespace');
        $pluginPath = '';
        if ($this->plugin) {
            $namespace = $this->_pluginNamespace($this->plugin);
            $pluginPath = $this->plugin . '.';
        }

        $data += [
            'name' => $name,
            'namespace' => $namespace,
            'plugin' => $this->plugin,
            'pluginPath' => $pluginPath,
            'primaryKey' => []
        ];

        $this->BakeTemplate->set($data);
        $out = $this->BakeTemplate->generate('Model/entity');

        $path = $this->getPath();
        $filename = $path . 'Entity' . DS . $name . '.php';
        $this->out("\n" . sprintf('Baking entity class for %s...', $name), 1, Shell::QUIET);
        $this->createFile($filename, $out);
        $emptyFile = $path . 'Entity' . DS . 'empty';
        $this->_deleteEmptyFile($emptyFile);

        return $out;
    }

    /**
     * Bake a table class.
     *
     * @param \Cake\ORM\Table $model Model name or object
     * @param array $data An array to use to generate the Table
     * @return string|null
     */
    public function bakeTable($model, array $data = [])
    {
        if (!empty($this->params['no-table'])) {
            return null;
        }

        $namespace = Configure::read('App.namespace');
        $pluginPath = '';
        if ($this->plugin) {
            $namespace = $this->_pluginNamespace($this->plugin);
        }

        $name = $model->getAlias();
        $entity = $this->_entityName($model->getAlias());
        $data += [
            'plugin' => $this->plugin,
            'pluginPath' => $pluginPath,
            'namespace' => $namespace,
            'name' => $name,
            'entity' => $entity,
            'associations' => [],
            'primaryKey' => 'id',
            'displayField' => null,
            'table' => null,
            'validation' => [],
            'rulesChecker' => [],
            'behaviors' => [],
            'connection' => $this->connection,
        ];

        $this->BakeTemplate->set($data);
        $out = $this->BakeTemplate->generate('Model/table');

        $path = $this->getPath();
        $filename = $path . 'Table' . DS . $name . 'Table.php';
        $this->out("\n" . sprintf('Baking table class for %s...', $name), 1, Shell::QUIET);
        $this->createFile($filename, $out);

        // Work around composer caching that classes/files do not exist.
        // Check for the file as it might not exist in tests.
        if (file_exists($filename)) {
            require_once $filename;
        }
        TableRegistry::clear();

        $emptyFile = $path . 'Table' . DS . 'empty';
        $this->_deleteEmptyFile($emptyFile);

        return $out;
    }

    /**
     * Outputs the a list of possible models or controllers from database
     *
     * @return array
     */
    public function listAll()
    {
        if (!empty($this->_tables)) {
            return $this->_tables;
        }

        $this->_modelNames = [];
        $this->_tables = $this->_getAllTables();
        foreach ($this->_tables as $table) {
            $this->_modelNames[] = $this->_camelize($table);
        }

        return $this->_tables;
    }

    /**
     * Outputs the a list of unskipped models or controllers from database
     *
     * @return array
     */
    public function listUnskipped()
    {
        $this->listAll();

        return array_diff($this->_tables, $this->skipTables);
    }

    /**
     * Models never have routing prefixes applied.
     *
     * @return string
     */
    protected function _getPrefix()
    {
        return '';
    }

    /**
     * Get an Array of all the tables in the supplied connection
     * will halt the script if no tables are found.
     *
     * @return array Array of tables in the database.
     * @throws \InvalidArgumentException When connection class
     *   does not have a schemaCollection method.
     */
    protected function _getAllTables()
    {
        $db = ConnectionManager::get($this->connection);
        if (!method_exists($db, 'schemaCollection')) {
            $this->abort(
                'Connections need to implement schemaCollection() to be used with bake.'
            );
        }
        $schema = $db->schemaCollection();
        $tables = $schema->listTables();
        if (empty($tables)) {
            $this->abort('Your database does not have any tables.');
        }
        sort($tables);

        return $tables;
    }

    /**
     * Get the table name for the model being baked.
     *
     * Uses the `table` option if it is set.
     *
     * @param string $name Table name
     * @return string
     */
    public function getTable($name)
    {
        if (isset($this->params['table'])) {
            return $this->params['table'];
        }

        return Inflector::underscore($name);
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
            'Bake table and entity classes.'
        )->addArgument('name', [
            'help' => 'Name of the model to bake (without the Table suffix). ' .
                'You can use Plugin.name to bake plugin models.'
        ])->addSubcommand('all', [
            'help' => 'Bake all model files with associations and validation.'
        ])->addOption('table', [
            'help' => 'The table name to use if you have non-conventional table names.'
        ])->addOption('no-entity', [
            'boolean' => true,
            'help' => 'Disable generating an entity class.'
        ])->addOption('no-table', [
            'boolean' => true,
            'help' => 'Disable generating a table class.'
        ])->addOption('no-validation', [
            'boolean' => true,
            'help' => 'Disable generating validation rules.'
        ])->addOption('no-rules', [
            'boolean' => true,
            'help' => 'Disable generating a rules checker.'
        ])->addOption('no-associations', [
            'boolean' => true,
            'help' => 'Disable generating associations.'
        ])->addOption('no-fields', [
            'boolean' => true,
            'help' => 'Disable generating accessible fields in the entity.'
        ])->addOption('fields', [
            'help' => 'A comma separated list of fields to make accessible.'
        ])->addOption('no-hidden', [
            'boolean' => true,
            'help' => 'Disable generating hidden fields in the entity.'
        ])->addOption('hidden', [
            'help' => 'A comma separated list of fields to hide.'
        ])->addOption('primary-key', [
            'help' => 'The primary key if you would like to manually set one.' .
                ' Can be a comma separated list if you are using a composite primary key.'
        ])->addOption('display-field', [
            'help' => 'The displayField if you would like to choose one.'
        ])->addOption('no-test', [
            'boolean' => true,
            'help' => 'Do not generate a test case skeleton.'
        ])->addOption('no-fixture', [
            'boolean' => true,
            'help' => 'Do not generate a test fixture skeleton.'
        ])->setEpilog(
            'Omitting all arguments and options will list the table names you can generate models for'
        );

        return $parser;
    }

    /**
     * Interact with FixtureTask to automatically bake fixtures when baking models.
     *
     * @param string $className Name of class to bake fixture for
     * @param string|null $useTable Optional table name for fixture to use.
     * @return void
     * @see FixtureTask::bake
     */
    public function bakeFixture($className, $useTable = null)
    {
        if (!empty($this->params['no-fixture'])) {
            return;
        }
        $this->Fixture->connection = $this->connection;
        $this->Fixture->plugin = $this->plugin;
        $this->Fixture->interactive = $this->interactive;
        $this->Fixture->bake($className, $useTable);
    }

    /**
     * Assembles and writes a unit test file
     *
     * @param string $className Model class name
     * @return string|null
     */
    public function bakeTest($className)
    {
        if (!empty($this->params['no-test'])) {
            return null;
        }
        $this->Test->plugin = $this->plugin;
        $this->Test->interactive = $this->interactive;
        $this->Test->connection = $this->connection;

        return $this->Test->bake('Table', $className);
    }
}
