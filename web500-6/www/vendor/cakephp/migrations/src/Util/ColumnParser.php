<?php
namespace Migrations\Util;

use Cake\Collection\Collection;
use Cake\Utility\Hash;
use ReflectionClass;

/**
 * Utility class used to parse arguments passed to a ``bake migration`` class
 */
class ColumnParser
{

    /**
     * Regex used to parse the column definition passed through the shell
     *
     * @var string
     */
    protected $regexpParseColumn = '/^(\w*)(?::(\w*\??\[?\d*\]?))?(?::(\w*))?(?::(\w*))?/';

    /**
     * Regex used to parse the field type and length
     *
     * @var string
     */
    protected $regexpParseField = '/(\w+\??)\[(\d+)\]/';

    /**
     * Parses a list of arguments into an array of fields
     *
     * @param array $arguments A list of arguments being parsed
     * @return array
     */
    public function parseFields($arguments)
    {
        $fields = [];
        $arguments = $this->validArguments($arguments);
        foreach ($arguments as $field) {
            preg_match($this->regexpParseColumn, $field, $matches);
            $field = $matches[1];
            $type = Hash::get($matches, 2);
            $indexType = Hash::get($matches, 3);

            $typeIsPk = in_array($type, ['primary', 'primary_key']);
            $isPrimaryKey = false;
            if ($typeIsPk || in_array($indexType, ['primary', 'primary_key'])) {
                $isPrimaryKey = true;

                if ($typeIsPk) {
                    $type = 'primary';
                }
            }

            $nullable = (bool)preg_match('/\w+\?(\[\d+\])?/', $type);
            $type = $nullable ? str_replace('?', '', $type) : $type;

            list($type, $length) = $this->getTypeAndLength($field, $type);
            $fields[$field] = [
                'columnType' => $type,
                'options' => [
                    'null' => $nullable,
                    'default' => null,
                ]
            ];

            if ($length !== null) {
                $fields[$field]['options']['limit'] = $length;
            }

            if ($isPrimaryKey === true && $type === 'integer') {
                $fields[$field]['options']['autoIncrement'] = true;
            }
        }

        return $fields;
    }

    /**
     * Parses a list of arguments into an array of indexes
     *
     * @param array $arguments A list of arguments being parsed
     * @return array
     */
    public function parseIndexes($arguments)
    {
        $indexes = [];
        $arguments = $this->validArguments($arguments);
        foreach ($arguments as $field) {
            preg_match($this->regexpParseColumn, $field, $matches);
            $field = $matches[1];
            $type = Hash::get($matches, 2);
            $indexType = Hash::get($matches, 3);
            $indexName = Hash::get($matches, 4);

            if (in_array($type, ['primary', 'primary_key']) ||
                in_array($indexType, ['primary', 'primary_key']) ||
                $indexType === null) {
                continue;
            }

            $indexUnique = false;
            if ($indexType === 'unique') {
                $indexUnique = true;
            }

            $indexName = $this->getIndexName($field, $indexType, $indexName, $indexUnique);

            if (empty($indexes[$indexName])) {
                $indexes[$indexName] = [
                    'columns' => [],
                    'options' => [
                        'unique' => $indexUnique,
                        'name' => $indexName,
                    ],
                ];
            }

            $indexes[$indexName]['columns'][] = $field;
        }

        return $indexes;
    }

    /**
     * Parses a list of arguments into an array of fields composing the primary key
     * of the table
     *
     * @param array $arguments A list of arguments being parsed
     * @return array
     */
    public function parsePrimaryKey($arguments)
    {
        $primaryKey = [];
        $arguments = $this->validArguments($arguments);
        foreach ($arguments as $field) {
            preg_match($this->regexpParseColumn, $field, $matches);
            $field = $matches[1];
            $type = Hash::get($matches, 2);
            $indexType = Hash::get($matches, 3);

            if (in_array($type, ['primary', 'primary_key']) || in_array($indexType, ['primary', 'primary_key'])) {
                $primaryKey[] = $field;
            }
        }

        return $primaryKey;
    }

    /**
     * Returns a list of only valid arguments
     *
     * @param array $arguments A list of arguments
     * @return array
     */
    public function validArguments($arguments)
    {
        $collection = new Collection($arguments);

        return $collection->filter(function ($value, $field) {
            return preg_match($this->regexpParseColumn, $field);
        })->toArray();
    }

    /**
     * Get the type and length of a field based on the field and the type passed
     *
     * @param string $field Name of field
     * @param string $type User-specified type
     * @return array First value is the field type, second value is the field length. If no length
     * can be extracted, null is returned for the second value
     */
    public function getTypeAndLength($field, $type)
    {
        if (preg_match($this->regexpParseField, $type, $matches)) {
            return [$matches[1], $matches[2]];
        }

        $fieldType = $this->getType($field, $type);
        $length = $this->getLength($fieldType);

        return [$fieldType, $length];
    }

    /**
     * Retrieves a type that should be used for a specific field
     *
     * @param string $field Name of field
     * @param string $type User-specified type
     * @return string
     */
    public function getType($field, $type)
    {
        $reflector = new ReflectionClass('Phinx\Db\Adapter\AdapterInterface');
        $collection = new Collection($reflector->getConstants());

        $validTypes = $collection->filter(function ($value, $constant) {
            return substr($constant, 0, strlen('PHINX_TYPE_')) === 'PHINX_TYPE_';
        })->toArray();

        $fieldType = $type;
        if ($type === null || !in_array($type, $validTypes)) {
            if ($type === 'primary') {
                $fieldType = 'integer';
            } elseif ($field === 'id') {
                $fieldType = 'integer';
            } elseif (in_array($field, ['created', 'modified', 'updated']) || substr($field, -3) === '_at') {
                $fieldType = 'datetime';
            } else {
                $fieldType = 'string';
            }
        }

        return $fieldType;
    }

    /**
     * Returns the default length to be used for a given fie
     *
     * @param string $type User-specified type
     * @return int
     */
    public function getLength($type)
    {
        $length = null;
        if ($type === 'string') {
            $length = 255;
        } elseif ($type === 'integer') {
            $length = 11;
        } elseif ($type === 'biginteger') {
            $length = 20;
        }

        return $length;
    }

    /**
     * Returns the default length to be used for a given fie
     *
     * @param string $field Name of field
     * @param string $indexType Type of index
     * @param string $indexName Name of index
     * @param bool $indexUnique Whether this is a unique index or not
     * @return string
     */
    public function getIndexName($field, $indexType, $indexName, $indexUnique)
    {
        if (empty($indexName)) {
            $indexName = strtoupper('BY_' . $field);
            if ($indexType === 'primary') {
                $indexName = 'PRIMARY';
            } elseif ($indexUnique) {
                $indexName = strtoupper('UNIQUE_' . $field);
            }
        }

        return $indexName;
    }
}
