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

use Cake\Database\Connection;
use Cake\Database\Driver\Postgres;
use PDO;
use Phinx\Db\Adapter\AdapterInterface;
use Phinx\Db\Table as PhinxTable;
use Phinx\Db\Table\Column;
use Phinx\Db\Table\ForeignKey;
use Phinx\Db\Table\Index;
use Phinx\Migration\MigrationInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Decorates an AdapterInterface in order to proxy some method to the actual
 * connection object.
 */
class CakeAdapter implements AdapterInterface
{

    /**
     * Decorated adapter
     *
     * @var \Phinx\Db\Adapter\AdapterInterface
     */
    protected $adapter;

    /**
     * Database connection
     *
     * @var \Cake\Database\Connection
     */
    protected $connection;

    /**
     * Constructor
     *
     * @param \Phinx\Db\Adapter\AdapterInterface $adapter The original adapter to decorate.
     * @param \Cake\Database\Connection $connection The connection to actually use.
     */
    public function __construct(AdapterInterface $adapter, Connection $connection)
    {
        $this->adapter = $adapter;
        $this->connection = $connection;
        $pdo = $adapter->getConnection();

        if ($pdo->getAttribute(PDO::ATTR_ERRMODE) !== PDO::ERRMODE_EXCEPTION) {
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        $connection->cacheMetadata(false);

        if ($connection->driver() instanceof Postgres) {
            $config = $connection->config();
            $schema = empty($config['schema']) ? 'public' : $config['schema'];
            $pdo->exec('SET search_path TO ' . $schema);
        }
        $connection->driver()->connection($pdo);
    }

    /**
     * Gets the database PDO connection object.
     *
     * @return \PDO
     */
    public function getConnection()
    {
        return $this->adapter->getConnection();
    }

    /**
     * Gets the database PDO connection object.
     *
     * @param \PDO $connection Connection
     * @return AdapterInterface
     */
    public function setConnection($connection)
    {
        return $this->adapter->setConnection($connection);
    }

    /**
     * Gets the CakePHP Connection object.
     *
     * @return \Cake\Database\Connection
     */
    public function getCakeConnection()
    {
        return $this->connection;
    }

    /**
     * Get all migrated version numbers.
     *
     * @return array
     */
    public function getVersions()
    {
        return $this->adapter->getVersions();
    }

    /**
     * Get all migration log entries, indexed by version number.
     *
     * @return array
     */
    public function getVersionLog()
    {
        return $this->adapter->getVersionLog();
    }

    /**
     * Set adapter configuration options.
     *
     * @param array $options Options array.
     * @return $this
     */
    public function setOptions(array $options)
    {
        return $this->adapter->setOptions($options);
    }

    /**
     * Get all adapter options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->adapter->getOptions();
    }

    /**
     * Check if an option has been set.
     *
     * @param string $name Option name.
     * @return bool
     */
    public function hasOption($name)
    {
        return $this->adapter->hasOption($name);
    }

    /**
     * Get a single adapter option, or null if the option does not exist.
     *
     * @param string $name Option name.
     * @return mixed
     */
    public function getOption($name)
    {
        return $this->adapter->getOption($name);
    }

    /**
     * Sets the console output.
     *
     * @param OutputInterface $output Output
     * @return $this
     */
    public function setOutput(OutputInterface $output)
    {
        return $this->adapter->setOutput($output);
    }

    /**
     * Gets the console output.
     *
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->adapter->getOutput();
    }

    /**
     * Sets the command start time
     *
     * @param int $time Start time.
     * @return $this
     */
    public function setCommandStartTime($time)
    {
        return $this->adapter->setCommandStartTime($time);
    }

    /**
     * Gets the command start time
     *
     * @return int
     */
    public function getCommandStartTime()
    {
        return $this->adapter->getCommandStartTime();
    }

    /**
     * Start timing a command.
     *
     * @return void
     */
    public function startCommandTimer()
    {
        $this->adapter->startCommandTimer();
    }

    /**
     * Stop timing the current command and write the elapsed time to the
     * output.
     *
     * @return void
     */
    public function endCommandTimer()
    {
        $this->adapter->endCommandTimer();
    }

    /**
     * Write a Phinx command to the output.
     *
     * @param string $command Command Name
     * @param array  $args    Command Args
     * @return void
     */
    public function writeCommand($command, $args = [])
    {
        $this->adapter->writeCommand($command, $args);
    }

    /**
     * Records a migration being run.
     *
     * @param MigrationInterface $migration Migration
     * @param string $direction Direction
     * @param int $startTime Start Time
     * @param int $endTime End Time
     * @return $this
     */
    public function migrated(MigrationInterface $migration, $direction, $startTime, $endTime)
    {
        return $this->adapter->migrated($migration, $direction, $startTime, $endTime);
    }

    /**
     * Does the schema table exist?
     *
     * @deprecated use hasTable instead.
     * @return bool
     */
    public function hasSchemaTable()
    {
        return $this->adapter->hasSchemaTable();
    }

    /**
     * Creates the schema table.
     *
     * @return void
     */
    public function createSchemaTable()
    {
        $this->adapter->createSchemaTable();
    }

    /**
     * Returns the adapter type.
     *
     * @return string
     */
    public function getAdapterType()
    {
        return $this->adapter->getAdapterType();
    }

    /**
     * Initializes the database connection.
     *
     * @throws \RuntimeException When the requested database driver is not installed.
     * @return void
     */
    public function connect()
    {
        $this->adapter->connect();
    }

    /**
     * Closes the database connection.
     *
     * @return void
     */
    public function disconnect()
    {
        $this->adapter->disconnect();
    }

    /**
     * Does the adapter support transactions?
     *
     * @return bool
     */
    public function hasTransactions()
    {
        return $this->adapter->hasTransactions();
    }

    /**
     * Begin a transaction.
     *
     * @return void
     */
    public function beginTransaction()
    {
        $this->connection->begin();
    }

    /**
     * Commit a transaction.
     *
     * @return void
     */
    public function commitTransaction()
    {
        $this->connection->commit();
    }

    /**
     * Rollback a transaction.
     *
     * @return void
     */
    public function rollbackTransaction()
    {
        $this->connection->rollback();
    }

    /**
     * Executes a SQL statement and returns the number of affected rows.
     *
     * @param string $sql SQL
     * @return int
     */
    public function execute($sql)
    {
        return $this->adapter->execute($sql);
    }

    /**
     * Executes a SQL statement and returns the result as an array.
     *
     * @param string $sql SQL
     * @return array
     */
    public function query($sql)
    {
        return $this->adapter->query($sql);
    }

    /**
     * Executes a query and returns only one row as an array.
     *
     * @param string $sql SQL
     * @return array
     */
    public function fetchRow($sql)
    {
        return $this->adapter->fetchRow($sql);
    }

    /**
     * Executes a query and returns an array of rows.
     *
     * @param string $sql SQL
     * @return array
     */
    public function fetchAll($sql)
    {
        return $this->adapter->fetchAll($sql);
    }

    /**
     * Inserts data into a table.
     *
     * @param \Phinx\Db\Table $table where to insert data
     * @param array $row Row array.
     * @return void
     */
    public function insert(PhinxTable $table, $row)
    {
        $this->adapter->insert($table, $row);
    }

    /**
     * Quotes a table name for use in a query.
     *
     * @param string $tableName Table Name
     * @return string
     */
    public function quoteTableName($tableName)
    {
        return $this->adapter->quoteTableName($tableName);
    }

    /**
     * Quotes a column name for use in a query.
     *
     * @param string $columnName Table Name
     * @return string
     */
    public function quoteColumnName($columnName)
    {
        return $this->adapter->quoteColumnName($columnName);
    }

    /**
     * Checks to see if a table exists.
     *
     * @param string $tableName Table Name
     * @return bool
     */
    public function hasTable($tableName)
    {
        return $this->adapter->hasTable($tableName);
    }

    /**
     * Creates the specified database table.
     *
     * @param \Phinx\Db\Table $table Table
     * @return void
     */
    public function createTable(PhinxTable $table)
    {
        $this->adapter->createTable($table);
    }

    /**
     * Renames the specified database table.
     *
     * @param string $tableName Table Name
     * @param string $newName   New Name
     * @return void
     */
    public function renameTable($tableName, $newName)
    {
        $this->adapter->renameTable($tableName, $newName);
    }

    /**
     * Drops the specified database table.
     *
     * @param string $tableName Table Name
     * @return void
     */
    public function dropTable($tableName)
    {
        $this->adapter->dropTable($tableName);
    }

    /**
     * Truncates the specified table
     *
     * @param string $tableName Table name.
     * @return void
     */
    public function truncateTable($tableName)
    {
        $this->adapter->truncateTable($tableName);
    }

    /**
     * Returns table columns
     *
     * @param string $tableName Table Name
     * @return Column[]
     */
    public function getColumns($tableName)
    {
        return $this->adapter->getColumns($tableName);
    }

    /**
     * Checks to see if a column exists.
     *
     * @param string $tableName  Table Name
     * @param string $columnName Column Name
     * @return bool
     */
    public function hasColumn($tableName, $columnName)
    {
        return $this->adapter->hasColumn($tableName, $columnName);
    }

    /**
     * Adds the specified column to a database table.
     *
     * @param \Phinx\Db\Table  $table  Table
     * @param \Phinx\Db\Table\Column $column Column
     * @return void
     */
    public function addColumn(PhinxTable $table, Column $column)
    {
        $this->adapter->addColumn($table, $column);
    }

    /**
     * Renames the specified column.
     *
     * @param string $tableName Table Name
     * @param string $columnName Column Name
     * @param string $newColumnName New Column Name
     * @return void
     */
    public function renameColumn($tableName, $columnName, $newColumnName)
    {
        $this->adapter->renameColumn($tableName, $columnName, $newColumnName);
    }

    /**
     * Change a table column type.
     *
     * @param string $tableName  Table Name
     * @param string $columnName Column Name
     * @param \Phinx\Db\Table\Column $newColumn  New Column
     * @return \Phinx\Db\Table
     */
    public function changeColumn($tableName, $columnName, Column $newColumn)
    {
        return $this->adapter->changeColumn($tableName, $columnName, $newColumn);
    }

    /**
     * Drops the specified column.
     *
     * @param string $tableName Table Name
     * @param string $columnName Column Name
     * @return void
     */
    public function dropColumn($tableName, $columnName)
    {
        $this->adapter->dropColumn($tableName, $columnName);
    }

    /**
     * Checks to see if an index exists.
     *
     * @param string $tableName Table Name
     * @param mixed  $columns   Column(s)
     * @return bool
     */
    public function hasIndex($tableName, $columns)
    {
        return $this->adapter->hasIndex($tableName, $columns);
    }

    /**
     * Checks to see if an index specified by name exists.
     *
     * @param string $tableName Table name.
     * @param string $indexName Index name.
     * @return bool
     */
    public function hasIndexByName($tableName, $indexName)
    {
        return $this->adapter->hasIndexByName($tableName, $indexName);
    }

    /**
     * Adds the specified index to a database table.
     *
     * @param \Phinx\Db\Table $table Table
     * @param Index $index Index
     * @return void
     */
    public function addIndex(PhinxTable $table, Index $index)
    {
        $this->adapter->addIndex($table, $index);
    }

    /**
     * Drops the specified index from a database table.
     *
     * @param string $tableName Table name.
     * @param mixed  $columns Column(s)
     * @return void
     */
    public function dropIndex($tableName, $columns)
    {
        $this->adapter->dropIndex($tableName, $columns);
    }

    /**
     * Drops the index specified by name from a database table.
     *
     * @param string $tableName Table name.
     * @param string $indexName Index name.
     * @return void
     */
    public function dropIndexByName($tableName, $indexName)
    {
        $this->adapter->dropIndexByName($tableName, $indexName);
    }

    /**
     * Checks to see if a foreign key exists.
     *
     * @param string $tableName Table name.
     * @param string[] $columns Column(s)
     * @param string|null $constraint Constraint name
     * @return bool
     */
    public function hasForeignKey($tableName, $columns, $constraint = null)
    {
        return $this->adapter->hasForeignKey($tableName, $columns, $constraint);
    }

    /**
     * Adds the specified foreign key to a database table.
     *
     * @param \Phinx\Db\Table $table Table object.
     * @param \Phinx\Db\Table\ForeignKey $foreignKey Foreign key object.
     * @return void
     */
    public function addForeignKey(PhinxTable $table, ForeignKey $foreignKey)
    {
        $this->adapter->addForeignKey($table, $foreignKey);
    }

    /**
     * Drops the specified foreign key from a database table.
     * If the adapter property is an instance of the \Phinx\Db\Adapter\SQLiteAdapter,
     * a specific method will be called. The original one from Phinx contains a bug
     * that can drop a table in certain conditions.
     *
     * @param string $tableName Table name.
     * @param string[] $columns Column(s)
     * @param string|null $constraint Constraint name
     * @return void
     */
    public function dropForeignKey($tableName, $columns, $constraint = null)
    {
        $this->adapter->dropForeignKey($tableName, $columns, $constraint);
    }

    /**
     * Returns an array of the supported Phinx column types.
     *
     * @return array
     */
    public function getColumnTypes()
    {
        return $this->adapter->getColumnTypes();
    }

    /**
     * Checks that the given column is of a supported type.
     *
     * @param \Phinx\Db\Table\Column $column Column object.
     * @return bool
     */
    public function isValidColumnType(Column $column)
    {
        return $this->adapter->isValidColumnType($column);
    }

    /**
     * Converts the Phinx logical type to the adapter's SQL type.
     *
     * @param string $type Type name.
     * @param int|null $limit Limit.
     * @return string
     */
    public function getSqlType($type, $limit = null)
    {
        return $this->adapter->getSqlType($type, $limit);
    }

    /**
     * Creates a new database.
     *
     * @param string $name Database Name
     * @param array $options Options
     * @return void
     */
    public function createDatabase($name, $options = [])
    {
        $this->adapter->createDatabase($name, $options);
    }

    /**
     * Checks to see if a database exists.
     *
     * @param string $name Database Name
     * @return bool
     */
    public function hasDatabase($name)
    {
        return $this->adapter->hasDatabase($name);
    }

    /**
     * Drops the specified database.
     *
     * @param string $name Database Name
     * @return void
     */
    public function dropDatabase($name)
    {
        $this->adapter->dropDatabase($name);
    }

    /**
     * Cast a value to a boolean appropriate for the adapter.
     *
     * @param mixed $value The value to be cast
     *
     * @return mixed
     */
    public function castToBool($value)
    {
        return $this->adapter->castToBool($value);
    }

    /**
     * Sets the console input.
     *
     * @param InputInterface $input Input
     * @return $this
     */
    public function setInput(InputInterface $input)
    {
        $this->adapter->setInput($input);

        return $this;
    }

    /**
     * Gets the console input.
     *
     * @return InputInterface
     */
    public function getInput()
    {
        return $this->adapter->getInput();
    }

    /**
     * Toggle a migration breakpoint.
     *
     * @param MigrationInterface $migration Migration instance.
     *
     * @return $this
     */
    public function toggleBreakpoint(MigrationInterface $migration)
    {
        $this->adapter->toggleBreakpoint($migration);

        return $this;
    }

    /**
     * Reset all migration breakpoints.
     *
     * @return int The number of breakpoints reset
     */
    public function resetAllBreakpoints()
    {
        return $this->adapter->resetAllBreakpoints();
    }
}
