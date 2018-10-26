<?php
namespace Bake\View\Helper;

use Bake\Utility\Model\AssociationFilter;
use Cake\Core\Configure;
use Cake\Core\ConventionsTrait;
use Cake\Utility\Inflector;
use Cake\View\Helper;

/**
 * Bake helper
 */
class BakeHelper extends Helper
{
    use ConventionsTrait;

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];

    /**
     * AssociationFilter utility
     *
     * @var AssociationFilter
     */
    protected $_associationFilter = null;

    /**
     * Used for generating formatted properties such as component and helper arrays
     *
     * @param string $name the name of the property
     * @param array $value the array of values
     * @param array $options extra options to be passed to the element
     * @return string
     */
    public function arrayProperty($name, array $value = [], array $options = [])
    {
        if (!$value) {
            return '';
        }

        foreach ($value as &$val) {
            $val = Inflector::camelize($val);
        }
        $options += [
            'name' => $name,
            'value' => $value
        ];

        return $this->_View->element('array_property', $options);
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
            'indent' => 2,
            'tab' => '    ',
            'trailingComma' => false,
            'quotes' => true
        ];

        if (!$list) {
            return '';
        }

        foreach ($list as $k => &$v) {
            if ($options['quotes']) {
                $v = "'$v'";
            }
            if (!is_numeric($k)) {
                $nestedOptions = $options;
                if ($nestedOptions['indent']) {
                    $nestedOptions['indent'] += 1;
                }
                if (is_array($v)) {
                    $v = sprintf(
                        "'%s' => [%s]",
                        $k,
                        $this->stringifyList($v, $nestedOptions)
                    );
                } else {
                    $v = "'$k' => $v";
                }
            } elseif (is_array($v)) {
                $nestedOptions = $options;
                if ($nestedOptions['indent']) {
                    $nestedOptions['indent'] += 1;
                }
                $v = sprintf(
                    "[%s]",
                    $this->stringifyList($v, $nestedOptions)
                );
            }
        }

        $start = $end = '';
        $join = ', ';
        if ($options['indent']) {
            $join = ',';
            $start = "\n" . str_repeat($options['tab'], $options['indent']);
            $join .= $start;
            $end = "\n" . str_repeat($options['tab'], $options['indent'] - 1);
        }

        if ($options['trailingComma']) {
            $end = "," . $end;
        }

        return $start . implode($join, $list) . $end;
    }

    /**
     * Extract the aliases for associations, filters hasMany associations already extracted as
     * belongsToMany
     *
     * @param \Cake\ORM\Table $table object to find associations on
     * @param string $assoc association to extract
     * @return array
     */
    public function aliasExtractor($table, $assoc)
    {
        $extractor = function ($val) {
            return $val->getTarget()->getAlias();
        };
        $aliases = array_map($extractor, $table->associations()->type($assoc));
        if ($assoc === 'HasMany') {
            return $this->_filterHasManyAssociationsAliases($table, $aliases);
        }

        return $aliases;
    }

    /**
     * Returns details about the given class.
     *
     * The returned array holds the following keys:
     *
     * - `fqn` (the fully qualified name)
     * - `namespace` (the full namespace without leading separator)
     * - `class` (the class name)
     * - `plugin` (either the name of the plugin, or `null`)
     * - `name` (the name of the component without suffix)
     * - `fullName` (the full name of the class, including possible vendor and plugin name)
     *
     * @param string $class Class name
     * @param string $type Class type/sub-namespace
     * @param string $suffix Class name suffix
     * @return array Class info
     */
    public function classInfo($class, $type, $suffix)
    {
        list($plugin, $name) = \pluginSplit($class);

        $base = Configure::read('App.namespace');
        if ($plugin !== null) {
            $base = $plugin;
        }
        $base = str_replace('/', '\\', trim($base, '\\'));
        $sub = '\\' . str_replace('/', '\\', trim($type, '\\'));
        $qn = $sub . '\\' . $name . $suffix;

        if (class_exists('\Cake' . $qn)) {
            $base = 'Cake';
        }

        return [
            'fqn' => '\\' . $base . $qn,
            'namespace' => $base . $sub,
            'plugin' => $plugin,
            'class' => $name . $suffix,
            'name' => $name,
            'fullName' => $class
        ];
    }

    /**
     * Return list of fields to generate controls for.
     *
     * @param array $fields Fields list.
     * @param \Cake\Datasource\SchemaInterface $schema Schema instance.
     * @param \Cake\ORM\Table|null $modelObject Model object.
     * @param array $takeFields Take fields.
     * @param array $filterTypes Filter field types.
     * @return \Cake\Collection\CollectionInterface
     */
    public function filterFields($fields, $schema, $modelObject = null, $takeFields = [], $filterTypes = ['binary'])
    {
        $fields = collection($fields)
            ->filter(function ($field) use ($schema, $filterTypes) {
                return !in_array($schema->columnType($field), $filterTypes);
            });

        if (isset($modelObject) && $modelObject->hasBehavior('Tree')) {
            $fields = $fields->reject(function ($field) {
                return $field === 'lft' || $field === 'rght';
            });
        }

        if (!empty($takeFields)) {
            $fields = $fields->take($takeFields);
        }

        return $fields->toArray();
    }

    /**
     * Get fields data for view template.
     *
     * @param array $fields Fields list.
     * @param \Cake\Datasource\SchemaInterface $schema Schema instance.
     * @param array $associations Associations data.
     * @return array
     */
    public function getViewFieldsData($fields, $schema, $associations)
    {
        $immediateAssociations = $associations['BelongsTo'];
        $associationFields = collection($fields)
            ->map(function ($field) use ($immediateAssociations) {
                foreach ($immediateAssociations as $alias => $details) {
                    if ($field === $details['foreignKey']) {
                        return [$field => $details];
                    }
                }
            })
            ->filter()
            ->reduce(function ($fields, $value) {
                return $fields + $value;
            }, []);

        $groupedFields = collection($fields)
            ->filter(function ($field) use ($schema) {
                return $schema->columnType($field) !== 'binary';
            })
            ->groupBy(function ($field) use ($schema, $associationFields) {
                $type = $schema->columnType($field);
                if (isset($associationFields[$field])) {
                    return 'string';
                }
                if (in_array($type, [
                    'decimal',
                    'biginteger',
                    'integer',
                    'float',
                    'smallinteger',
                    'tinyinteger',
                ])) {
                    return 'number';
                }
                if (in_array($type, ['date', 'time', 'datetime', 'timestamp'])) {
                    return 'date';
                }

                return in_array($type, ['text', 'boolean']) ? $type : 'string';
            })
            ->toArray();

        $groupedFields += [
            'number' => [],
            'string' => [],
            'boolean' => [],
            'date' => [],
            'text' => [],
        ];

        return compact('associationFields', 'groupedFields');
    }

    /**
     * Get column data from schema.
     *
     * @param string $field Field name.
     * @param \Cake\Database\Schema\TableSchema $schema Schema.
     * @return array
     */
    public function columnData($field, $schema)
    {
        return $schema->column($field);
    }

    /**
     * Get alias of associated table.
     *
     * @param \Cake\ORM\Table $modelObj Model object.
     * @param string $assoc Association name.
     * @return string
     */
    public function getAssociatedTableAlias($modelObj, $assoc)
    {
        $association = $modelObj->association($assoc);

        return $association->getTarget()->getAlias();
    }

    /**
     * Get validation methods data.
     *
     * @param string $field Field name.
     * @param array $rules Validation rules list.
     * @return array
     */
    public function getValidationMethods($field, $rules)
    {
        $validationMethods = [];

        foreach ($rules as $ruleName => $rule) {
            if ($rule['rule'] && !isset($rule['provider']) && !isset($rule['args'])) {
                $validationMethods[] = sprintf("->%s('%s')", $rule['rule'], $field);
            } elseif ($rule['rule'] && !isset($rule['provider'])) {
                $formatTemplate = "->%s('%s')";
                if (!empty($rule['args'])) {
                    $formatTemplate = "->%s('%s', %s)";
                }
                $validationMethods[] = sprintf(
                    $formatTemplate,
                    $rule['rule'],
                    $field,
                    $this->stringifyList(
                        $rule['args'],
                        ['indent' => false, 'quotes' => false]
                    )
                );
            } elseif ($rule['rule'] && isset($rule['provider'])) {
                $validationMethods[] = sprintf(
                    "->add('%s', '%s', ['rule' => '%s', 'provider' => '%s'])",
                    $field,
                    $ruleName,
                    $rule['rule'],
                    $rule['provider']
                );
            }

            if (isset($rule['allowEmpty'])) {
                if (is_string($rule['allowEmpty'])) {
                    $validationMethods[] = sprintf(
                        "->allowEmpty('%s', '%s')",
                        $field,
                        $rule['allowEmpty']
                    );
                } elseif ($rule['allowEmpty']) {
                    $validationMethods[] = sprintf(
                        "->allowEmpty('%s')",
                        $field
                    );
                } else {
                    $validationMethods[] = sprintf(
                        "->requirePresence('%s', 'create')",
                        $field
                    );
                    $validationMethods[] = sprintf(
                        "->notEmpty('%s')",
                        $field
                    );
                }
            }
        }

        return $validationMethods;
    }

    /**
     * Get field accessibility data.
     *
     * @param mixed $fields Fields list.
     * @param mixed $primaryKey Primary key.
     * @return array
     */
    public function getFieldAccessibility($fields = null, $primaryKey = null)
    {
        $accessible = [];

        if (!isset($fields) || $fields !== false) {
            if (!empty($fields)) {
                foreach ($fields as $field) {
                    $accessible[$field] = 'true';
                }
            } elseif (!empty($primaryKey)) {
                $accessible['*'] = 'true';
                foreach ($primaryKey as $field) {
                    $accessible[$field] = 'false';
                }
            }
        }

        return $accessible;
    }

    /**
     * Wrap string arguments with quotes
     *
     * @param array $args array of arguments
     * @return array
     */
    public function escapeArguments($args)
    {
        return array_map(function ($v) {
            if (is_string($v)) {
                $v = strtr($v, ["'" => "\'"]);
                $v = "'$v'";
            }

            return $v;
        }, $args);
    }

    /**
     * To be mocked elsewhere...
     *
     * @param \Cake\ORM\Table $table Table
     * @param array $aliases array of aliases
     * @return array
     */
    protected function _filterHasManyAssociationsAliases($table, $aliases)
    {
        if (is_null($this->_associationFilter)) {
            $this->_associationFilter = new AssociationFilter();
        }

        return $this->_associationFilter->filterHasManyAssociationsAliases($table, $aliases);
    }
}
