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

use Cake\Core\Plugin;
use Cake\Datasource\ConnectionManager;
use Migrations\Util\UtilTrait;
use Phinx\Config\Config;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Contains a set of methods designed as overrides for
 * the methods in phinx that are responsible for reading the project configuration.
 * This is needed so that we can use the application configuration instead of having
 * a configuration yaml file.
 */
trait ConfigurationTrait
{

    use UtilTrait;

    /**
     * The configuration object that phinx uses for connecting to the database
     *
     * @var \Phinx\Config\Config
     */
    protected $configuration;

    /**
     * Connection name to be used for this request
     *
     * @var string
     */
    protected $connection;

    /**
     * The console input instance
     *
     * @var \Symfony\Component\Console\Input\Input
     */
    protected $input;

    /**
     * Overrides the original method from phinx in order to return a tailored
     * Config object containing the connection details for the database.
     *
     * @param bool $forceRefresh Refresh config.
     * @return \Phinx\Config\Config
     */
    public function getConfig($forceRefresh = false)
    {
        if ($this->configuration && $forceRefresh === false) {
            return $this->configuration;
        }

        $migrationsPath = $this->getOperationsPath($this->input);
        $seedsPath = $this->getOperationsPath($this->input, 'Seeds');
        $plugin = $this->getPlugin($this->input);

        if (!is_dir($migrationsPath)) {
            mkdir($migrationsPath, 0777, true);
        }

        if (!is_dir($seedsPath)) {
            mkdir($seedsPath, 0777, true);
        }

        $phinxTable = $this->getPhinxTable($plugin);

        $connection = $this->getConnectionName($this->input);

        $connectionConfig = ConnectionManager::config($connection);
        $adapterName = $this->getAdapterName($connectionConfig['driver']);

        $templatePath = Plugin::path('Migrations') . 'src' . DS . 'Template' . DS;
        $config = [
            'paths' => [
                'migrations' => $migrationsPath,
                'seeds' => $seedsPath,
            ],
            'templates' => [
                'file' => $templatePath . 'Phinx' . DS . 'create.php.template'
            ],
            'migration_base_class' => 'Migrations\AbstractMigration',
            'environments' => [
                'default_migration_table' => $phinxTable,
                'default_database' => 'default',
                'default' => [
                    'adapter' => $adapterName,
                    'host' => isset($connectionConfig['host']) ? $connectionConfig['host'] : null,
                    'user' => isset($connectionConfig['username']) ? $connectionConfig['username'] : null,
                    'pass' => isset($connectionConfig['password']) ? $connectionConfig['password'] : null,
                    'port' => isset($connectionConfig['port']) ? $connectionConfig['port'] : null,
                    'name' => $connectionConfig['database'],
                    'charset' => isset($connectionConfig['encoding']) ? $connectionConfig['encoding'] : null,
                    'unix_socket' => isset($connectionConfig['unix_socket']) ? $connectionConfig['unix_socket'] : null,
                ]
            ]
        ];

        if ($adapterName === 'pgsql') {
            if (!empty($connectionConfig['schema'])) {
                $config['environments']['default']['schema'] = $connectionConfig['schema'];
            }
        }

        if ($adapterName === 'mysql') {
            if (!empty($connectionConfig['ssl_key']) && !empty($connectionConfig['ssl_cert'])) {
                $config['environments']['default']['mysql_attr_ssl_key'] = $connectionConfig['ssl_key'];
                $config['environments']['default']['mysql_attr_ssl_cert'] = $connectionConfig['ssl_cert'];
            }

            if (!empty($connectionConfig['ssl_ca'])) {
                $config['environments']['default']['mysql_attr_ssl_ca'] = $connectionConfig['ssl_ca'];
            }
        }

        return $this->configuration = new Config($config);
    }

    /**
     * Returns the correct driver name to use in phinx based on the driver class
     * that was configured for the configuration.
     *
     * @param string $driver The driver name as configured for the CakePHP app.
     * @return string Name of the adapter.
     * @throws \InvalidArgumentException when it was not possible to infer the information
     * out of the provided database configuration
     */
    public function getAdapterName($driver)
    {
        switch ($driver) {
            case 'Cake\Database\Driver\Mysql':
            case is_subclass_of($driver, 'Cake\Database\Driver\Mysql'):
                return 'mysql';
            case 'Cake\Database\Driver\Postgres':
            case is_subclass_of($driver, 'Cake\Database\Driver\Postgres'):
                return 'pgsql';
            case 'Cake\Database\Driver\Sqlite':
            case is_subclass_of($driver, 'Cake\Database\Driver\Sqlite'):
                return 'sqlite';
            case 'Cake\Database\Driver\Sqlserver':
            case is_subclass_of($driver, 'Cake\Database\Driver\Sqlserver'):
                return 'sqlsrv';
        }

        throw new \InvalidArgumentException('Could not infer database type from driver');
    }

    /**
     * Returns the connection name that should be used for the migrations.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input the input object
     * @return string
     */
    protected function getConnectionName(InputInterface $input)
    {
        $connection = 'default';
        if ($input->getOption('connection')) {
            $connection = $input->getOption('connection');
        }

        return $connection;
    }
}
